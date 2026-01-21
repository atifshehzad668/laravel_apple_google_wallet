<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletPass extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'email',
        'apple_serial_number',
        'apple_pass_path',
        'google_object_id',
        'google_class_id',
        'google_pass_url',
        'google_state',
        'ticket_holder_name',
        'ticket_number',
        'barcode_type',
        'barcode_value',
        'seat',
        'row',
        'section',
        'gate',
        'event_name',
        'event_start_date',
        'event_end_date',
        'event_doors_open',
        'latitude',
        'longitude',
        'logo_url',
        'hero_image_url',
        'homepage_url',
        'main_image_url',
        'text_module_header',
        'text_module_body',
        'barcode_data',
        'status',
    ];

    /**
     * Get the member that owns the wallet pass.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Check if the pass has an Apple Wallet pass.
     */
    public function hasApplePass(): bool
    {
        return !empty($this->apple_serial_number);
    }

    /**
     * Check if the pass has a Google Wallet pass.
     */
    public function hasGooglePass(): bool
    {
        return !empty($this->google_object_id);
    }

    /**
     * Get the Apple Wallet pass download URL.
     */
    public function getApplePassUrlAttribute(): ?string
    {
        if ($this->hasApplePass()) {
            return route('pass.download', ['id' => $this->member_id]);
        }
        return null;
    }
}
