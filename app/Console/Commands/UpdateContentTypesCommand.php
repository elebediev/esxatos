<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateContentTypesCommand extends Command
{
    protected $signature = 'books:update-content-types';
    protected $description = 'Update content_type for existing books based on Drupal categories';

    // Root category TIDs in Drupal
    private const DRUPAL_MODULES_ROOT_TID = 28;  // "МОДУЛИ BIBLEQUTE"
    private const DRUPAL_SOFTWARE_ROOT_TID = 56; // "ПРОГРАММЫ"
    private const DRUPAL_AUDIO_TID = 3534;       // "AUDIO" category

    private array $modulesTids = [];
    private array $softwareTids = [];
    private array $audioTids = [];

    public function handle(): int
    {
        $this->info('Updating content types for existing books...');

        // Build content type category maps
        $this->buildContentTypeMaps();

        $books = Book::whereNotNull('drupal_nid')->get();
        $total = $books->count();

        $this->info("Processing {$total} books...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $stats = [
            'book' => 0,
            'module' => 0,
            'software' => 0,
            'audio' => 0,
        ];

        foreach ($books as $book) {
            $contentType = $this->determineContentType($book->drupal_nid);

            if ($book->content_type !== $contentType) {
                $book->update(['content_type' => $contentType]);
            }

            $stats[$contentType]++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Content types updated:");
        $this->info("  Books: {$stats['book']}");
        $this->info("  Modules: {$stats['module']}");
        $this->info("  Software: {$stats['software']}");
        $this->info("  Audio: {$stats['audio']}");

        return Command::SUCCESS;
    }

    private function buildContentTypeMaps(): void
    {
        // Get all child TIDs for "МОДУЛИ BIBLEQUTE" (tid=28)
        $this->modulesTids = DB::connection('drupal')
            ->table('taxonomy_term_hierarchy')
            ->where('parent', self::DRUPAL_MODULES_ROOT_TID)
            ->pluck('tid')
            ->toArray();
        $this->modulesTids[] = self::DRUPAL_MODULES_ROOT_TID;

        // Get all child TIDs for "ПРОГРАММЫ" (tid=56)
        $this->softwareTids = DB::connection('drupal')
            ->table('taxonomy_term_hierarchy')
            ->where('parent', self::DRUPAL_SOFTWARE_ROOT_TID)
            ->pluck('tid')
            ->toArray();
        $this->softwareTids[] = self::DRUPAL_SOFTWARE_ROOT_TID;

        // Audio category (tid=3534)
        $this->audioTids = [self::DRUPAL_AUDIO_TID];

        $this->info("Content type mappings:");
        $this->info("  Modules TIDs: " . count($this->modulesTids));
        $this->info("  Software TIDs: " . count($this->softwareTids));
        $this->info("  Audio TIDs: " . count($this->audioTids));
    }

    private function determineContentType(int $nid): string
    {
        $tids = DB::connection('drupal')
            ->table('taxonomy_index')
            ->where('nid', $nid)
            ->pluck('tid')
            ->toArray();

        // Check for audio first (it's a subcategory inside books)
        foreach ($tids as $tid) {
            if (in_array($tid, $this->audioTids)) {
                return Book::TYPE_AUDIO;
            }
        }

        // Check for modules
        foreach ($tids as $tid) {
            if (in_array($tid, $this->modulesTids)) {
                return Book::TYPE_MODULE;
            }
        }

        // Check for software
        foreach ($tids as $tid) {
            if (in_array($tid, $this->softwareTids)) {
                return Book::TYPE_SOFTWARE;
            }
        }

        // Default to book
        return Book::TYPE_BOOK;
    }
}
