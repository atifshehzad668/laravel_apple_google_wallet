<?php

namespace App\Services;

use App\Models\Member;
use App\Models\WalletPass;
use App\Lib\GenericPass;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing Generic Google Wallet passes for members.
 * This service integrates GenericPass library with member data.
 */
class GenericPassService
{
    private GenericPass $genericPass;
    private array $config;
    private array $brandingConfig;
    private string $serviceAccountFile;

    public function __construct()
    {
        $this->genericPass = new GenericPass();
        $this->config = config('wallet.google');
        $this->brandingConfig = config('wallet.branding');
        $this->serviceAccountFile = storage_path('app/google/apple-accounts.json');
    }

    /**
     * Generate Google Wallet generic pass for a member.
     *
     * @param int $memberId Member ID
     * @return array Pass data with URL and object ID
     * @throws Exception
     */
    public function generatePass(int $memberId): array
    {
        try {
            // Get member data
            $member = Member::findOrFail($memberId);

            // Get issuer and class IDs
            $issuerId = $this->config['issuer_id'];
            $classSuffix = $this->config['class_id'];
            
            // Create class if it doesn't exist
            $this->genericPass->createClass($issuerId, $classSuffix);

            // Generate unique object ID for this member
            $objectSuffix = $this->generateObjectSuffix($member->unique_member_id);

            // Prepare member data for the pass
            $passData = $this->prepareMemberData($member);

            // Create the pass object
            $passObject = $this->genericPass->createObject(
                $issuerId,
                $classSuffix,
                $objectSuffix,
                $passData
            );

            // Generate JWT for "Add to Google Wallet" URL
            $passUrl = $this->createJwtForExistingObject($issuerId, $classSuffix, $objectSuffix);

            // Save to database
            $objectId = "{$issuerId}.{$objectSuffix}";
            $this->savePassToDatabase($memberId, $objectId, $passUrl);

            Log::info("Generic pass created successfully for member: {$memberId}", [
                'object_id' => $objectId,
                'member_name' => $member->full_name
            ]);

            return [
                'object_id' => $objectId,
                'pass_url' => $passUrl,
                'member_id' => $memberId,
                'member_name' => $member->full_name,
            ];
        } catch (Exception $e) {
            Log::error("Failed to generate generic pass for member {$memberId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepare member data for the pass.
     *
     * @param Member $member
     * @return array
     */
    private function prepareMemberData(Member $member): array
    {
        return [
            'name' => $member->full_name,
            'subheader' => 'Member #' . $member->unique_member_id,
            'member_id' => $member->unique_member_id,
            'email' => $member->email,
            'mobile' => $member->mobile,
            'joined_date' => $member->created_at->format('F j, Y'),
            'barcode_value' => $member->unique_member_id,
        ];
    }

    /**
     * Generate object suffix from member ID.
     * Ensures the suffix is valid for Google Wallet (alphanumeric, underscores, hyphens).
     *
     * @param string $memberId
     * @return string
     */
    private function generateObjectSuffix(string $memberId): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $memberId);
    }

    /**
     * Create JWT for existing pass object.
     * This generates the "Add to Google Wallet" URL.
     *
     * @param string $issuerId
     * @param string $classSuffix
     * @param string $objectSuffix
     * @return string
     * @throws Exception
     */
    private function createJwtForExistingObject(string $issuerId, string $classSuffix, string $objectSuffix): string
    {
        if (!file_exists($this->serviceAccountFile)) {
            throw new Exception('Google Wallet service account file not found at: ' . $this->serviceAccountFile);
        }

        $serviceAccount = json_decode(file_get_contents($this->serviceAccountFile), true);

        if (!$serviceAccount || !isset($serviceAccount['client_email']) || !isset($serviceAccount['private_key'])) {
            throw new Exception('Invalid service account file format.');
        }

        // Create JWT payload for existing generic object
        $objectsToAdd = [
            'genericObjects' => [
                [
                    'id' => "{$issuerId}.{$objectSuffix}",
                    'classId' => "{$issuerId}.{$classSuffix}"
                ]
            ]
        ];

        $claims = [
            'iss' => $serviceAccount['client_email'],
            'aud' => 'google',
            'origins' => [],
            'typ' => 'savetowallet',
            'payload' => $objectsToAdd,
            'iat' => time(),
        ];

        $token = JWT::encode(
            $claims,
            $serviceAccount['private_key'],
            'RS256'
        );

        return "https://pay.google.com/gp/v/save/{$token}";
    }

    /**
     * Save pass information to database.
     *
     * @param int $memberId
     * @param string $objectId
     * @param string $passUrl
     * @return void
     */
    private function savePassToDatabase(int $memberId, string $objectId, string $passUrl): void
    {
        $member = Member::find($memberId);
        
        WalletPass::updateOrCreate(
            ['member_id' => $memberId],
            [
                'google_object_id' => $objectId,
                'google_pass_url' => $passUrl,
                'barcode_data' => $member->unique_member_id,
                'status' => 'active',
            ]
        );
    }

    /**
     * Get pass URL for a member.
     *
     * @param int $memberId
     * @return string|null
     */
    public function getPassUrl(int $memberId): ?string
    {
        $walletPass = WalletPass::where('member_id', $memberId)
            ->where('status', 'active')
            ->first();

        return $walletPass ? $walletPass->google_pass_url : null;
    }

    /**
     * Regenerate pass for a member.
     * This creates a new pass object and updates the database.
     *
     * @param int $memberId
     * @return array
     * @throws Exception
     */
    public function regeneratePass(int $memberId): array
    {
        // Expire the old pass if it exists
        $this->revokePass($memberId);

        // Generate a new pass
        return $this->generatePass($memberId);
    }

    /**
     * Revoke (expire) a pass for a member.
     *
     * @param int $memberId
     * @return bool
     */
    public function revokePass(int $memberId): bool
    {
        try {
            $walletPass = WalletPass::where('member_id', $memberId)->first();

            if (!$walletPass || !$walletPass->google_object_id) {
                return false;
            }

            // Extract issuer ID and object suffix from object ID
            list($issuerId, $objectSuffix) = explode('.', $walletPass->google_object_id, 2);

            // Expire the pass in Google Wallet
            $this->genericPass->expireObject($issuerId, $objectSuffix);

            // Update database status
            WalletPass::where('member_id', $memberId)
                ->update(['status' => 'revoked']);

            Log::info("Generic pass revoked for member: {$memberId}");

            return true;
        } catch (Exception $e) {
            Log::error("Failed to revoke generic pass for member {$memberId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update pass information for a member.
     * Since Google Wallet doesn't support direct updates, we need to send a message
     * or regenerate the pass.
     *
     * @param int $memberId
     * @param array $updatedData
     * @return array
     * @throws Exception
     */
    public function updatePass(int $memberId, array $updatedData = []): array
    {
        // For generic passes, the best approach is to regenerate
        return $this->regeneratePass($memberId);
    }

    /**
     * Get pass object from Google Wallet.
     *
     * @param int $memberId
     * @return object|null
     */
    public function getPassObject(int $memberId): ?object
    {
        try {
            $walletPass = WalletPass::where('member_id', $memberId)->first();

            if (!$walletPass || !$walletPass->google_object_id) {
                return null;
            }

            list($issuerId, $objectSuffix) = explode('.', $walletPass->google_object_id, 2);

            return $this->genericPass->service->genericobject->get("{$issuerId}.{$objectSuffix}");
        } catch (Exception $e) {
            Log::error("Failed to get pass object for member {$memberId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a member has an active pass.
     *
     * @param int $memberId
     * @return bool
     */
    public function hasActivePass(int $memberId): bool
    {
        return WalletPass::where('member_id', $memberId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Get all active passes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActivePasses()
    {
        return WalletPass::where('status', 'active')
            ->with('member')
            ->get();
    }
}
