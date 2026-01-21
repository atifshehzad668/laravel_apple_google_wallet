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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->string('action', 100)->comment('e.g., member_created, pass_regenerated, member_deleted');
            $table->string('entity_type', 50)->nullable()->comment('e.g., member, pass, admin');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID of affected entity');
            $table->text('details')->nullable()->comment('JSON data with additional details');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index('admin_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
