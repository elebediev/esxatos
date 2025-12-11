<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Store old Drupal URLs for 301 redirects (SEO preservation)
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_path', 512)->unique();
            $table->string('new_path', 512);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();

            $table->index('old_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};
