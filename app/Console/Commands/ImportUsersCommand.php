<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportUsersCommand extends Command
{
    protected $signature = 'import:users {--limit= : Limit number of users to import}';
    protected $description = 'Import users from Drupal database';

    public function handle(): int
    {
        $this->info('Starting users import from Drupal...');

        // Pre-load profile data for all users
        $profileData = $this->loadProfileData();
        $this->info("Loaded profile data for " . count($profileData) . " users");

        $query = DB::connection('drupal')
            ->table('users')
            ->where('uid', '>', 0) // Skip anonymous user
            ->where('status', 1)   // Only active users
            ->orderBy('uid');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = (clone $query)->count();
        $this->info("Found {$total} users to import");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $imported = 0;
        $skipped = 0;

        $query->chunk(500, function ($drupalUsers) use (&$imported, &$skipped, $bar, $profileData) {
            foreach ($drupalUsers as $drupalUser) {
                // Skip if already imported
                if (User::where('drupal_uid', $drupalUser->uid)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Skip if email already exists
                if ($drupalUser->mail && User::where('email', $drupalUser->mail)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Skip if name already exists
                if (User::where('name', $drupalUser->name)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Get profile data for this user
                $profile = $profileData[$drupalUser->uid] ?? null;

                try {
                    User::create([
                        'drupal_uid' => $drupalUser->uid,
                        'name' => $drupalUser->name,
                        'first_name' => $profile['real_name'] ?? null,
                        'country' => $profile['country'] ?? null,
                        'city' => $profile['city'] ?? null,
                        'email' => $drupalUser->mail ?: "user_{$drupalUser->uid}@esxatos.com",
                        'password' => bcrypt(bin2hex(random_bytes(16))), // Random password
                        'drupal_password_hash' => $drupalUser->pass,
                        'is_active' => true,
                        'timezone' => $drupalUser->timezone,
                        'language' => $drupalUser->language ?: 'ru',
                        'last_login_at' => $drupalUser->login > 0
                            ? \Carbon\Carbon::createFromTimestamp($drupalUser->login)
                            : null,
                        'created_at' => \Carbon\Carbon::createFromTimestamp($drupalUser->created),
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $this->error("\nFailed to import user {$drupalUser->name}: " . $e->getMessage());
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Import completed!");
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");

        return Command::SUCCESS;
    }

    /**
     * Load profile data (real name, country, city) for all users from Drupal
     */
    private function loadProfileData(): array
    {
        $profiles = DB::connection('drupal')
            ->table('profile as p')
            ->leftJoin('field_data_field_username as fn', function ($join) {
                $join->on('fn.entity_id', '=', 'p.pid')
                    ->where('fn.entity_type', '=', 'profile2');
            })
            ->leftJoin('field_data_field_usercountry as fc', function ($join) {
                $join->on('fc.entity_id', '=', 'p.pid')
                    ->where('fc.entity_type', '=', 'profile2');
            })
            ->leftJoin('field_data_field_usrtown as ft', function ($join) {
                $join->on('ft.entity_id', '=', 'p.pid')
                    ->where('ft.entity_type', '=', 'profile2');
            })
            ->select([
                'p.uid',
                'fn.field_username_value as real_name',
                'fc.field_usercountry_value as country',
                'ft.field_usrtown_value as city',
            ])
            ->get();

        $data = [];
        foreach ($profiles as $profile) {
            $data[$profile->uid] = [
                'real_name' => $profile->real_name,
                'country' => $profile->country,
                'city' => $profile->city,
            ];
        }

        return $data;
    }
}
