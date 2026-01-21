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
        Schema::table('wallet_passes', function (Blueprint $table) {
            $table->boolean('is_google_added')->default(false)->after('google_pass_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            $table->dropColumn('is_google_added');
        });
    }
};
