<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\BookFile;
use App\Models\Category;
use App\Models\UrlRedirect;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportBooksCommand extends Command
{
    protected $signature = 'import:books
                            {--limit= : Limit number of books to import}
                            {--offset=0 : Offset to start from}';
    protected $description = 'Import books from Drupal database';

    private array $categoryMap = [];
    private array $userMap = [];

    public function handle(): int
    {
        $this->info('Starting books import from Drupal...');

        // Pre-load category mapping (drupal_tid => laravel_id)
        $this->categoryMap = Category::pluck('id', 'drupal_tid')->toArray();
        $this->userMap = User::pluck('id', 'drupal_uid')->toArray();

        $query = DB::connection('drupal')
            ->table('node as n')
            ->where('n.type', 'booksmodules')
            ->where('n.status', 1)
            ->orderBy('n.nid');

        $total = (clone $query)->count();
        $offset = (int) $this->option('offset');

        if ($offset > 0) {
            $query->offset($offset);
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
            $total = min((int) $limit, $total - $offset);
        }

        $this->info("Found {$total} books to import (offset: {$offset})");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $imported = 0;
        $skipped = 0;

        $query->chunk(100, function ($nodes) use (&$imported, &$skipped, $bar) {
            foreach ($nodes as $node) {
                // Skip if already imported
                if (Book::where('drupal_nid', $node->nid)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                try {
                    $book = $this->importBook($node);
                    if ($book) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $this->error("\nFailed to import book nid={$node->nid}: " . $e->getMessage());
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

    private function importBook(object $node): ?Book
    {
        // Get body content
        $body = DB::connection('drupal')
            ->table('field_data_body')
            ->where('entity_id', $node->nid)
            ->where('bundle', 'booksmodules')
            ->first();

        $description = $body->body_value ?? '';
        $descriptionPlain = strip_tags(html_entity_decode($description));

        // Get URL alias
        $alias = DB::connection('drupal')
            ->table('url_alias')
            ->where('source', "node/{$node->nid}")
            ->first();

        $slug = $alias ? $this->extractSlug($alias->alias) : Str::slug($node->title);
        $slug = $this->generateUniqueSlug($slug, $node->nid);

        // Get cover image
        $cover = DB::connection('drupal')
            ->table('field_data_field_wrapbm as f')
            ->join('file_managed as fm', 'f.field_wrapbm_fid', '=', 'fm.fid')
            ->where('f.entity_id', $node->nid)
            ->first();

        $coverImage = null;
        $coverAlt = null;
        if ($cover) {
            // Convert Drupal URI (public://path) to relative path
            $coverImage = str_replace('public://', '', $cover->uri);
            $coverAlt = $cover->field_wrapbm_alt ?? $node->title;
        }

        // Get view count
        $counter = DB::connection('drupal')
            ->table('node_counter')
            ->where('nid', $node->nid)
            ->first();

        // Create book
        $book = Book::create([
            'drupal_nid' => $node->nid,
            'title' => $node->title,
            'slug' => $slug,
            'description' => $description,
            'description_plain' => Str::limit($descriptionPlain, 60000),
            'cover_image' => $coverImage,
            'cover_alt' => $coverAlt,
            'user_id' => $this->userMap[$node->uid] ?? null,
            'is_published' => true,
            'views_count' => $counter->totalcount ?? 0,
            'published_at' => \Carbon\Carbon::createFromTimestamp($node->created),
            'created_at' => \Carbon\Carbon::createFromTimestamp($node->created),
            'updated_at' => \Carbon\Carbon::createFromTimestamp($node->changed ?? $node->created),
        ]);

        // Import categories
        $this->importBookCategories($book, $node->nid);

        // Import download files/links
        $this->importBookFiles($book, $node->nid);

        // Create URL redirect from old path
        if ($alias) {
            $this->createRedirect($alias->alias, "books/{$slug}");
        }
        // Also redirect from node/nid
        $this->createRedirect("node/{$node->nid}", "books/{$slug}");

        return $book;
    }

    private function importBookCategories(Book $book, int $nid): void
    {
        $tids = DB::connection('drupal')
            ->table('taxonomy_index')
            ->where('nid', $nid)
            ->pluck('tid')
            ->toArray();

        $categoryIds = [];
        foreach ($tids as $tid) {
            if (isset($this->categoryMap[$tid])) {
                $categoryIds[] = $this->categoryMap[$tid];
            }
        }

        if (!empty($categoryIds)) {
            $book->categories()->sync($categoryIds);
        }
    }

    private function importBookFiles(Book $book, int $nid): void
    {
        $files = DB::connection('drupal')
            ->table('field_data_field_doc')
            ->where('entity_id', $nid)
            ->orderBy('delta')
            ->get();

        foreach ($files as $index => $file) {
            if (empty($file->field_doc_url)) {
                continue;
            }

            BookFile::create([
                'book_id' => $book->id,
                'title' => $file->field_doc_title ?: "File " . ($index + 1),
                'url' => $file->field_doc_url,
                'file_type' => $this->detectFileType($file->field_doc_url),
                'sort_order' => $index,
            ]);
        }
    }

    private function extractSlug(string $alias): string
    {
        // Remove 'files/' prefix if present
        $slug = preg_replace('/^files\//', '', $alias);
        return $slug;
    }

    private function generateUniqueSlug(string $slug, int $nid): string
    {
        if (empty($slug)) {
            $slug = 'book-' . $nid;
        }

        $originalSlug = $slug;
        $counter = 1;

        while (Book::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function createRedirect(string $oldPath, string $newPath): void
    {
        $oldPath = ltrim($oldPath, '/');

        if (UrlRedirect::where('old_path', $oldPath)->exists()) {
            return;
        }

        UrlRedirect::create([
            'old_path' => $oldPath,
            'new_path' => $newPath,
            'status_code' => 301,
        ]);
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
