<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRatingsCommand extends Command
{
    protected $signature = 'import:ratings';
    protected $description = 'Import ratings from Drupal votingapi';

    public function handle(): int
    {
        $this->info('Starting ratings import from Drupal...');

        $userMap = User::pluck('id', 'drupal_uid')->toArray();
        $bookMap = Book::pluck('id', 'drupal_nid')->toArray();

        $query = DB::connection('drupal')
            ->table('votingapi_vote')
            ->where('entity_type', 'node')
            ->where('tag', 'vote')
            ->orderBy('vote_id');

        $total = (clone $query)->count();
        $this->info("Found {$total} ratings to import");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $imported = 0;
        $skipped = 0;

        $query->chunk(1000, function ($votes) use (&$imported, &$skipped, $bar, $userMap, $bookMap) {
            foreach ($votes as $vote) {
                if (Rating::where('drupal_vote_id', $vote->vote_id)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $bookId = $bookMap[$vote->entity_id] ?? null;
                if (!$bookId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $userId = $userMap[$vote->uid] ?? null;

                // Skip if user already rated this book (keep first rating)
                if ($userId && Rating::where('book_id', $bookId)->where('user_id', $userId)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                try {
                    Rating::create([
                        'drupal_vote_id' => $vote->vote_id,
                        'book_id' => $bookId,
                        'user_id' => $userId,
                        'value' => (int) $vote->value, // Drupal stores as percent (0-100)
                        'ip_address' => $vote->vote_source,
                        'created_at' => \Carbon\Carbon::createFromTimestamp($vote->timestamp),
                        'updated_at' => \Carbon\Carbon::createFromTimestamp($vote->timestamp),
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // Update book rating stats
        $this->info('Updating book rating statistics...');
        Book::whereHas('ratings')->each(function ($book) {
            $book->update([
                'rating_average' => $book->ratings()->avg('value') ?? 0,
                'rating_count' => $book->ratings()->count(),
            ]);
        });

        $this->newLine();
        $this->info("Import completed!");
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
