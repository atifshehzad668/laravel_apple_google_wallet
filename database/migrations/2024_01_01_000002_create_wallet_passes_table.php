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
        Schema::create('wallet_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('apple_serial_number', 100)->unique()->nullable()->comment('Apple PassKit serial number');
            $table->string('apple_pass_path', 255)->nullable()->comment('Path to .pkpass file');
            $table->string('google_object_id', 100)->unique()->nullable()->comment('Google Wallet object ID');
            $table->text('google_pass_url')->nullable()->comment('Google Wallet add URL');
            $table->string('barcode_data', 255)->nullable()->comment('QR code data (member ID)');
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('member_id');
            $table->index('apple_serial_number');
            $table->index('google_object_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_passes');
    }
};
