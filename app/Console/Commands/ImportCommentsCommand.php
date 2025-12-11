<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCommentsCommand extends Command
{
    protected $signature = 'import:comments {--limit= : Limit number of comments}';
    protected $description = 'Import comments from Drupal database';

    private array $userMap = [];
    private array $bookMap = [];
    private array $commentMap = [];

    public function handle(): int
    {
        $this->info('Starting comments import from Drupal...');

        // Pre-load mappings
        $this->userMap = User::pluck('id', 'drupal_uid')->toArray();
        $this->bookMap = Book::pluck('id', 'drupal_nid')->toArray();

        $query = DB::connection('drupal')
            ->table('comment as c')
            ->leftJoin('field_data_comment_body as cb', 'c.cid', '=', 'cb.entity_id')
            ->join('node as n', 'c.nid', '=', 'n.nid')
            ->where('c.status', 1)
            ->where('n.type', 'booksmodules')
            ->select('c.*', 'cb.comment_body_value as body')
            ->orderBy('c.cid');

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = (clone $query)->count();
        $this->info("Found {$total} comments to import");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $imported = 0;
        $skipped = 0;

        // First pass: import all comments
        $query->chunk(500, function ($comments) use (&$imported, &$skipped, $bar) {
            foreach ($comments as $comment) {
                if (Comment::where('drupal_cid', $comment->cid)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $bookId = $this->bookMap[$comment->nid] ?? null;
                if (!$bookId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                try {
                    $newComment = Comment::create([
                        'drupal_cid' => $comment->cid,
                        'book_id' => $bookId,
                        'user_id' => $this->userMap[$comment->uid] ?? null,
                        'parent_id' => null, // Will be updated in second pass
                        'author_name' => $comment->name,
                        'author_email' => $comment->mail,
                        'subject' => $comment->subject,
                        'body' => $comment->body ?? '',
                        'is_approved' => true,
                        'created_at' => \Carbon\Carbon::createFromTimestamp($comment->created),
                        'updated_at' => \Carbon\Carbon::createFromTimestamp($comment->changed),
                    ]);

                    $this->commentMap[$comment->cid] = [
                        'id' => $newComment->id,
                        'pid' => $comment->pid,
                    ];

                    $imported++;
                } catch (\Exception $e) {
                    $this->error("\nFailed to import comment {$comment->cid}: " . $e->getMessage());
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // Second pass: update parent relationships
        $this->info('Updating parent relationships...');
        $updatedParents = 0;

        foreach ($this->commentMap as $cid => $data) {
            if ($data['pid'] > 0 && isset($this->commentMap[$data['pid']])) {
                Comment::where('drupal_cid', $cid)
                    ->update(['parent_id' => $this->commentMap[$data['pid']]['id']]);
                $updatedParents++;
            }
        }

        $this->newLine();
        $this->info("Import completed!");
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");
        $this->info("Parent relations updated: {$updatedParents}");

        return Command::SUCCESS;
    }
}
