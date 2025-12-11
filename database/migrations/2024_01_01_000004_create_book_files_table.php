<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('url', 2048);
            $table->string('file_type', 50)->nullable()->comment('pdf, epub, doc, etc.');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('downloads_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('book_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_files');
    }
};
