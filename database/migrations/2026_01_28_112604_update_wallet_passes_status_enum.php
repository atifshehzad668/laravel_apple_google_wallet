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
        // Update enum in wallet_passes table
        DB::statement("ALTER TABLE wallet_passes MODIFY COLUMN status ENUM('active', 'pending', 'expired', 'revoked') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_passes MODIFY COLUMN status ENUM('active', 'revoked', 'expired') DEFAULT 'active'");
    }
};
