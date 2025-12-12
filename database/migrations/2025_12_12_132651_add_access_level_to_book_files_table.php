<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('book_files', function (Blueprint $table) {
            // Access levels: public (all), authenticated (logged in), club, aide, admin
            $table->string('access_level', 20)->default('public')->after('sort_order');
            $table->index('access_level');
        });
    }

    public function down(): void
    {
        Schema::table('book_files', function (Blueprint $table) {
            $table->dropIndex(['access_level']);
            $table->dropColumn('access_level');
        });
    }
};
