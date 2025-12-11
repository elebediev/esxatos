<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('drupal_tid')->nullable()->unique()->comment('Original Drupal taxonomy term ID');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('weight')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('drupal_tid');
            $table->index('parent_id');
            $table->index('weight');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
