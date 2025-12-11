<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCategoriesCommand extends Command
{
    protected $signature = 'import:categories';
    protected $description = 'Import categories from Drupal taxonomy';

    public function handle(): int
    {
        $this->info('Starting categories import from Drupal...');

        // Import from vocabulary "_files" (vid = 2) - Library categories
        $terms = DB::connection('drupal')
            ->table('taxonomy_term_data as t')
            ->join('taxonomy_vocabulary as v', 't.vid', '=', 'v.vid')
            ->leftJoin('taxonomy_term_hierarchy as h', 't.tid', '=', 'h.tid')
            ->where('v.machine_name', '_files')
            ->select('t.tid', 't.name', 't.description', 't.weight', 'h.parent')
            ->orderBy('h.parent')
            ->orderBy('t.weight')
            ->get();

        $this->info("Found {$terms->count()} categories to import");

        $bar = $this->output->createProgressBar($terms->count());
        $bar->start();

        $tidToId = []; // Map Drupal tid to Laravel id
        $imported = 0;

        // First pass: create all categories without parents
        foreach ($terms as $term) {
            if (Category::where('drupal_tid', $term->tid)->exists()) {
                $category = Category::where('drupal_tid', $term->tid)->first();
                $tidToId[$term->tid] = $category->id;
                $bar->advance();
                continue;
            }

            $slug = $this->generateUniqueSlug($term->name);

            $category = Category::create([
                'drupal_tid' => $term->tid,
                'name' => $term->name,
                'slug' => $slug,
                'description' => $term->description,
                'weight' => $term->weight,
                'is_active' => true,
            ]);

            $tidToId[$term->tid] = $category->id;
            $imported++;
            $bar->advance();
        }

        // Second pass: update parent relationships
        foreach ($terms as $term) {
            if ($term->parent && $term->parent > 0 && isset($tidToId[$term->parent])) {
                Category::where('drupal_tid', $term->tid)
                    ->update(['parent_id' => $tidToId[$term->parent]]);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import completed! Imported: {$imported}");

        return Command::SUCCESS;
    }

    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'category-' . uniqid();
        }

        $originalSlug = $slug;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
