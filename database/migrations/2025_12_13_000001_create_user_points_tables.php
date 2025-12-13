<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Point categories (e.g., "Donation", "Work")
        Schema::create('point_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('drupal_tid')->nullable()->unique()->comment('Original Drupal taxonomy term ID');
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User points balance per category
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('drupal_pid')->nullable()->unique()->comment('Original Drupal points ID');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('point_categories')->nullOnDelete();
            $table->integer('points')->default(0)->comment('Current points balance');
            $table->integer('max_points')->default(0)->comment('Maximum points ever reached');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'category_id']);
            $table->index('points');
            $table->index('last_updated_at');
        });

        // Transactions history (all point operations)
        Schema::create('user_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('drupal_txn_id')->nullable()->unique()->comment('Original Drupal transaction ID');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete()->comment('Admin who approved');
            $table->foreignId('category_id')->nullable()->constrained('point_categories')->nullOnDelete();
            $table->integer('points')->comment('Points amount (positive or negative)');
            $table->string('operation', 50)->default('admin')->comment('Operation type: admin, expiry, download, etc.');
            $table->text('description')->nullable();
            $table->string('reference', 128)->nullable()->comment('Module-specific reference');
            $table->string('status', 20)->default('approved')->comment('approved, pending, cancelled');
            $table->timestamp('expires_at')->nullable()->comment('When points expire');
            $table->boolean('is_expired')->default(false);
            $table->unsignedBigInteger('parent_transaction_id')->nullable()->comment('Link to parent transaction');
            $table->string('entity_type', 128)->nullable()->comment('Related entity type');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('Related entity ID');
            $table->timestamp('drupal_created_at')->nullable()->comment('Original creation time from Drupal');
            $table->timestamps();

            $table->index('user_id');
            $table->index('approver_id');
            $table->index('category_id');
            $table->index('operation');
            $table->index('status');
            $table->index('is_expired');
            $table->index(['status', 'is_expired', 'expires_at']);
            $table->index('created_at');

            $table->foreign('parent_transaction_id')
                ->references('id')
                ->on('user_point_transactions')
                ->nullOnDelete();
        });

        // Add total points to users table for quick access
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->after('language')->comment('Total points balance');
            $table->index('total_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['total_points']);
            $table->dropColumn('total_points');
        });

        Schema::dropIfExists('user_point_transactions');
        Schema::dropIfExists('user_points');
        Schema::dropIfExists('point_categories');
    }
};
