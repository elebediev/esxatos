<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('drupal_nid')->nullable()->unique()->comment('Original Drupal node ID');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('description_plain')->nullable()->comment('Plain text for search');
            $table->string('cover_image')->nullable()->comment('Path to cover image');
            $table->string('cover_alt')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('downloads_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('drupal_nid');
            $table->index('slug');
            $table->index('is_published');
            $table->index('views_count');
            $table->index('published_at');

            // Fulltext index for search
            $table->fullText(['title', 'description_plain'], 'books_fulltext');
        });

        // Pivot table for book categories (many-to-many)
        Schema::create('book_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->unique(['book_id', 'category_id']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('books');
    }
};
