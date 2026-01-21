<?php

namespace App\Services;

use App\Models\Member;
use App\Models\WalletPass;
use Exception;
use Firebase\JWT\JWT;

class GoogleWalletService
{
    private array $config;
    private array $brandingConfig;

    public function __construct()
    {
        $this->config = config('wallet.google');
        $this->brandingConfig = config('wallet.branding');
    }

    /**
     * Generate Google Wallet pass for a member.
     *
     * @param int $memberId Member ID
     * @return array Pass data with URL and object ID
     * @throws Exception
     */
    public function generatePass(int $memberId): array
    {
        // Get member data
        $member = Member::findOrFail($memberId);

        // Generate unique object ID
        $objectId = $this->generateObjectId($member->unique_member_id);

        // Create pass object
        $passObject = $this->createPassObject($member, $objectId);

        // Generate JWT
        $jwt = $this->generateJWT($passObject);

        // Create "Add to Google Wallet" URL
        $passUrl = 'https://pay.google.com/gp/v/save/' . $jwt;

        // Save to database
        $this->savePassToDatabase($memberId, $objectId, $passUrl);

        return [
            'object_id' => $objectId,
            'pass_url' => $passUrl,
            'member_id' => $memberId,
        ];
    }

    /**
     * Create Google Wallet pass object.
     */
    private function createPassObject(Member $member, string $objectId): array
    {
        $fullName = $member->full_name;
        $design = $this->config['design'];

        $passObject = [
            'id' => $objectId,
            'classId' => $this->getClassId(),
            'state' => 'ACTIVE',

            // Card title and header
            'cardTitle' => [
                'defaultValue' => [
                    'language' => 'en-US',
                    'value' => $design['card_title'],
                ],
            ],

            'header' => [
                'defaultValue' => [
                    'language' => 'en-US',
                    'value' => $fullName,
                ],
            ],

            // Subheader (Member ID)
            'subheader' => [
                'defaultValue' => [
                    'language' => 'en-US',
                    'value' => 'ID: ' . $member->unique_member_id,
                ],
            ],

            // Barcode (QR code)
            'barcode' => [
                'type' => $this->config['barcode_type'],
                'value' => $member->unique_member_id,
                'alternateText' => $member->unique_member_id,
            ],

            // Text modules (additional info)
            'textModulesData' => [
                [
                    'header' => 'Email',
                    'body' => $member->email,
                    'id' => 'email',
                ],
                [
                    'header' => 'Mobile',
                    'body' => $member->mobile,
                    'id' => 'mobile',
                ],
                [
                    'header' => 'Member Since',
                    'body' => $member->created_at->format('F j, Y'),
                    'id' => 'member_since',
                ],
            ],

            // Links
            'linksModuleData' => [
                'uris' => [
                    [
                        'uri' => $this->brandingConfig['website'],
                        'description' => 'Visit Website',
                        'id' => 'website',
                    ],
                    [
                        'uri' => 'mailto:' . $this->brandingConfig['support_email'],
                        'description' => 'Contact Support',
                        'id' => 'support',
                    ],
                ],
            ],
        ];

        // Add hero image if configured
        if (!empty($design['hero_image_url'])) {
            $passObject['heroImage'] = [
                'sourceUri' => [
                    'uri' => $design['hero_image_url'],
                ],
                'contentDescription' => [
                    'defaultValue' => [
                        'language' => 'en-US',
                        'value' => $design['hero_image_description'] ?? 'Hero Image',
                    ],
                ],
            ];
        }

        return $passObject;
    }

    /**
     * Get or create class ID.
     */
    private function getClassId(): string
    {
        $issuerId = $this->config['issuer_id'];
        $classId = $this->config['class_id'];

        return "{$issuerId}.{$classId}";
    }

    /**
     * Generate unique object ID.
     */
    private function generateObjectId(string $memberId): string
    {
        $issuerId = $this->config['issuer_id'];
        $uniqueId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $memberId);

        return "{$issuerId}.{$uniqueId}";
    }

    /**
     * Generate JWT for Google Wallet.
     */
    private function generateJWT(array $passObject): string
    {
        // Load service account credentials
        if (!file_exists($this->config['service_account_file'])) {
            throw new Exception('Google Wallet service account file not found. Please configure Google Cloud credentials.');
        }

        $serviceAccount = json_decode(file_get_contents($this->config['service_account_file']), true);

        if (!$serviceAccount) {
            throw new Exception('Invalid service account file.');
        }

        // Create JWT payload
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'origins' => [],
            'payload' => [
                'genericObjects' => [$passObject],
            ],
        ];

        // Sign with private key
        $jwt = JWT::encode($payload, $serviceAccount['private_key'], 'RS256');

        return $jwt;
    }

    /**
     * Save pass information to database.
     */
    private function savePassToDatabase(int $memberId, string $objectId, string $passUrl): void
    {
        WalletPass::updateOrCreate(
            ['member_id' => $memberId],
            [
                'google_object_id' => $objectId,
                'google_pass_url' => $passUrl,
                'barcode_data' => Member::find($memberId)->unique_member_id,
                'status' => 'active',
            ]
        );
    }

    /**
     * Get pass URL for member.
     */
    public function getPassUrl(int $memberId): ?string
    {
        $walletPass = WalletPass::where('member_id', $memberId)
            ->where('status', 'active')
            ->first();

        return $walletPass ? $walletPass->google_pass_url : null;
    }

    /**
     * Revoke a pass.
     */
    public function revokePass(int $memberId): bool
    {
        return WalletPass::where('member_id', $memberId)
            ->update(['status' => 'revoked']) > 0;
    }
}
