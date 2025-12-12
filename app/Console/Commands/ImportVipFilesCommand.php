<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportVipFilesCommand extends Command
{
    protected $signature = 'import:vip-files
                            {--dry-run : Show what would be imported without making changes}';
    protected $description = 'Import VIP/club files from Drupal field_vip to book_files with club access level';

    public function handle(): int
    {
        $this->info('Starting VIP files import from Drupal...');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - no changes will be made');
        }

        // Get all VIP files with non-empty URLs
        $vipFiles = DB::connection('drupal')
            ->table('field_data_field_vip')
            ->where('field_vip_url', '!=', '')
            ->where('bundle', 'booksmodules')
            ->orderBy('entity_id')
            ->orderBy('delta')
            ->get();

        $this->info("Found {$vipFiles->count()} VIP files in Drupal");

        // Get mapping of drupal_nid to book_id
        $bookMap = Book::whereNotNull('drupal_nid')
            ->pluck('id', 'drupal_nid')
            ->toArray();

        $this->info("Found " . count($bookMap) . " books with Drupal NIDs");

        $imported = 0;
        $skipped = 0;
        $notFound = 0;

        $bar = $this->output->createProgressBar($vipFiles->count());
        $bar->start();

        foreach ($vipFiles as $file) {
            $bar->advance();

            $bookId = $bookMap[$file->entity_id] ?? null;

            if (!$bookId) {
                $notFound++;
                continue;
            }

            // Check if this exact URL already exists for this book
            $exists = BookFile::where('book_id', $bookId)
                ->where('url', $file->field_vip_url)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                // Get current max sort_order for this book
                $maxSortOrder = BookFile::where('book_id', $bookId)->max('sort_order') ?? -1;

                BookFile::create([
                    'book_id' => $bookId,
                    'title' => $file->field_vip_title ?: 'Club File',
                    'url' => $file->field_vip_url,
                    'file_type' => $this->detectFileType($file->field_vip_url),
                    'sort_order' => $maxSortOrder + 1,
                    'access_level' => BookFile::ACCESS_CLUB,
                ]);
            }

            $imported++;
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import completed!");
        $this->info("Imported: {$imported}");
        $this->info("Skipped (already exists): {$skipped}");
        $this->info("Not found (book not imported): {$notFound}");

        return Command::SUCCESS;
    }

    private function detectFileType(string $url): ?string
    {
        $url = strtolower($url);

        if (str_contains($url, 'drive.google.com')) {
            return 'google_drive';
        }
        if (str_contains($url, 'dropbox.com')) {
            return 'dropbox';
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        return match ($extension) {
            'pdf' => 'pdf',
            'epub' => 'epub',
            'mobi' => 'mobi',
            'doc', 'docx' => 'doc',
            'zip', 'rar', '7z' => 'archive',
            'mp3', 'wav', 'ogg' => 'audio',
            default => null,
        };
    }
}
