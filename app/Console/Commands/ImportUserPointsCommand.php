<?php

namespace App\Console\Commands;

use App\Models\PointCategory;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserPointTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportUserPointsCommand extends Command
{
    protected $signature = 'import:points {--limit= : Limit number of transactions to import}';
    protected $description = 'Import user points and transaction history from Drupal database';

    private array $categoryMap = [];
    private array $userMap = [];

    public function handle(): int
    {
        $this->info('Starting user points import from Drupal...');

        // Step 1: Import point categories
        $this->importCategories();

        // Step 2: Build user mapping
        $this->buildUserMap();

        // Step 3: Import transactions
        $this->importTransactions();

        // Step 4: Import current balances
        $this->importBalances();

        // Step 5: Update total points
        $this->updateTotalPoints();

        $this->newLine();
        $this->info('Points import completed!');

        return Command::SUCCESS;
    }

    private function importCategories(): void
    {
        $this->info('Importing point categories...');

        // Get categories from Drupal taxonomy
        $categories = DB::connection('drupal')
            ->table('taxonomy_term_data as t')
            ->join('taxonomy_vocabulary as v', 'v.vid', '=', 't.vid')
            ->where('v.machine_name', 'userpoints')
            ->orWhere('v.vid', 6) // Userpoints vocabulary ID
            ->select('t.tid', 't.name', 't.description')
            ->get();

        // Also add known categories that might not be in taxonomy
        $knownCategories = [
            ['tid' => 472, 'name' => 'Работа', 'slug' => 'work'],
            ['tid' => 476, 'name' => 'Пожертвование', 'slug' => 'donation'],
            ['tid' => 0, 'name' => 'Без категории', 'slug' => 'uncategorized'],
        ];

        foreach ($knownCategories as $cat) {
            $category = PointCategory::updateOrCreate(
                ['drupal_tid' => $cat['tid']],
                [
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'is_active' => true,
                ]
            );
            $this->categoryMap[$cat['tid']] = $category->id;
        }

        // Add any additional categories from taxonomy
        foreach ($categories as $cat) {
            if (!isset($this->categoryMap[$cat->tid])) {
                $slug = \Illuminate\Support\Str::slug($cat->name);
                $category = PointCategory::updateOrCreate(
                    ['drupal_tid' => $cat->tid],
                    [
                        'name' => $cat->name,
                        'slug' => $slug ?: 'category-' . $cat->tid,
                        'description' => $cat->description,
                        'is_active' => true,
                    ]
                );
                $this->categoryMap[$cat->tid] = $category->id;
            }
        }

        $this->info('Imported ' . count($this->categoryMap) . ' categories');
    }

    private function buildUserMap(): void
    {
        $this->info('Building user mapping...');

        $this->userMap = User::whereNotNull('drupal_uid')
            ->pluck('id', 'drupal_uid')
            ->toArray();

        $this->info('Found ' . count($this->userMap) . ' mapped users');
    }

    private function importTransactions(): void
    {
        $this->info('Importing transactions...');

        $query = DB::connection('drupal')
            ->table('userpoints_txn')
            ->orderBy('txn_id');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = (clone $query)->count();
        $this->info("Found {$total} transactions to import");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $imported = 0;
        $skipped = 0;

        // First, get all approver UIDs and map them
        $approverMap = User::whereNotNull('drupal_uid')
            ->pluck('id', 'drupal_uid')
            ->toArray();

        $query->chunk(500, function ($transactions) use (&$imported, &$skipped, $bar, $approverMap) {
            foreach ($transactions as $txn) {
                // Skip if already imported
                if (UserPointTransaction::where('drupal_txn_id', $txn->txn_id)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Skip if user not found
                if (!isset($this->userMap[$txn->uid])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                try {
                    // Map status
                    $status = match ($txn->status) {
                        0 => UserPointTransaction::STATUS_APPROVED,
                        1 => UserPointTransaction::STATUS_PENDING,
                        2 => UserPointTransaction::STATUS_APPROVED, // locked = approved
                        default => UserPointTransaction::STATUS_APPROVED,
                    };

                    // Map operation
                    $operation = match ($txn->operation) {
                        'админ', 'admin' => UserPointTransaction::OPERATION_ADMIN,
                        'expiry' => UserPointTransaction::OPERATION_EXPIRY,
                        default => UserPointTransaction::OPERATION_ADMIN,
                    };

                    // Don't trigger model events during import
                    $transaction = new UserPointTransaction();
                    $transaction->timestamps = false;
                    $transaction->fill([
                        'drupal_txn_id' => $txn->txn_id,
                        'user_id' => $this->userMap[$txn->uid],
                        'approver_id' => isset($approverMap[$txn->approver_uid]) ? $approverMap[$txn->approver_uid] : null,
                        'category_id' => $this->categoryMap[$txn->tid] ?? $this->categoryMap[0] ?? null,
                        'points' => $txn->points,
                        'operation' => $operation,
                        'description' => $txn->description,
                        'reference' => $txn->reference,
                        'status' => $status,
                        'expires_at' => $txn->expirydate > 0 ? Carbon::createFromTimestamp($txn->expirydate) : null,
                        'is_expired' => (bool) $txn->expired,
                        'entity_type' => $txn->entity_type,
                        'entity_id' => $txn->entity_id,
                        'drupal_created_at' => Carbon::createFromTimestamp($txn->time_stamp),
                        'created_at' => Carbon::createFromTimestamp($txn->time_stamp),
                        'updated_at' => $txn->changed > 0 ? Carbon::createFromTimestamp($txn->changed) : now(),
                    ]);
                    $transaction->save();

                    $imported++;
                } catch (\Exception $e) {
                    $this->error("\nFailed to import transaction {$txn->txn_id}: " . $e->getMessage());
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Transactions imported: {$imported}, skipped: {$skipped}");

        // Update parent transaction IDs
        $this->updateParentTransactions();
    }

    private function updateParentTransactions(): void
    {
        $this->info('Updating parent transaction references...');

        // Get all transactions with parent references
        $drupalTransactions = DB::connection('drupal')
            ->table('userpoints_txn')
            ->whereNotNull('parent_txn_id')
            ->where('parent_txn_id', '>', 0)
            ->select('txn_id', 'parent_txn_id')
            ->get();

        $updated = 0;
        foreach ($drupalTransactions as $txn) {
            $child = UserPointTransaction::where('drupal_txn_id', $txn->txn_id)->first();
            $parent = UserPointTransaction::where('drupal_txn_id', $txn->parent_txn_id)->first();

            if ($child && $parent) {
                $child->update(['parent_transaction_id' => $parent->id]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} parent references");
    }

    private function importBalances(): void
    {
        $this->info('Importing current balances...');

        $balances = DB::connection('drupal')
            ->table('userpoints')
            ->get();

        $bar = $this->output->createProgressBar($balances->count());
        $bar->start();

        $imported = 0;
        $skipped = 0;

        foreach ($balances as $balance) {
            if (!isset($this->userMap[$balance->uid])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                UserPoint::updateOrCreate(
                    [
                        'user_id' => $this->userMap[$balance->uid],
                        'category_id' => $this->categoryMap[$balance->tid] ?? $this->categoryMap[0] ?? null,
                    ],
                    [
                        'drupal_pid' => $balance->pid,
                        'points' => $balance->points,
                        'max_points' => $balance->max_points,
                        'last_updated_at' => $balance->last_update > 0
                            ? Carbon::createFromTimestamp($balance->last_update)
                            : null,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->error("\nFailed to import balance for user {$balance->uid}: " . $e->getMessage());
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Balances imported: {$imported}, skipped: {$skipped}");
    }

    private function updateTotalPoints(): void
    {
        $this->info('Updating total points for users...');

        DB::statement('
            UPDATE users
            SET total_points = COALESCE(
                (SELECT SUM(points) FROM user_points WHERE user_points.user_id = users.id),
                0
            )
        ');

        $usersWithPoints = User::where('total_points', '>', 0)->count();
        $this->info("Updated total points for {$usersWithPoints} users");
    }
}
