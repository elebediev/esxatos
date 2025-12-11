<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('drupal_vote_id')->nullable()->unique();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('value')->comment('Rating value 1-5 or 1-100');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'user_id']);
            $table->index('book_id');
            $table->index('user_id');
        });

        // Add average rating to books table
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('rating_average', 3, 2)->default(0)->after('downloads_count');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['rating_average', 'rating_count']);
        });
        Schema::dropIfExists('ratings');
    }
};
