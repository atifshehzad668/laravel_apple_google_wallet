<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'unique_member_id',
        'status',
    ];

    /**
     * Get the wallet pass for the member.
     */
    public function walletPass(): HasOne
    {
        return $this->hasOne(WalletPass::class);
    }

    /**
     * Get the full name of the member.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Generate a unique member ID.
     * Format: PREFIX-YEAR-XXXXXX (e.g., PMC-2024-001234)
     */
    public static function generateUniqueMemberId(): string
    {
        $prefix = config('wallet.branding.member_id_prefix', 'MEM');
        $year = date('Y');
        
        // Get the latest member ID for this year
        $lastMember = self::where('unique_member_id', 'like', "{$prefix}-{$year}-%")
            ->orderBy('unique_member_id', 'desc')
            ->first();
        
        if ($lastMember) {
            // Extract the sequence number and increment it
            $parts = explode('-', $lastMember->unique_member_id);
            $sequence = intval(end($parts)) + 1;
        } else {
            // Start from 1 for the first member of the year
            $sequence = 1;
        }
        
        // Format with leading zeros (6 digits)
        $sequenceStr = str_pad($sequence, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}-{$sequenceStr}";
    }

    /**
     * Format mobile number (remove spaces, keep + and digits).
     */
    public static function formatMobileNumber(string $mobile): string
    {
        // Remove all whitespace
        $mobile = preg_replace('/\s+/', '', $mobile);
        
        // Keep only + and digits
        $mobile = preg_replace('/[^\d+]/', '', $mobile);
        
        return $mobile;
    }

    /**
     * Check if email already exists.
     */
    public static function emailExists(string $email): bool
    {
        return self::where('email', $email)->exists();
    }
}
