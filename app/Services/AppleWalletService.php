<?php

namespace App\Services;

use App\Models\Member;
use App\Models\WalletPass;
use Exception;
use ZipArchive;

class AppleWalletService
{
    private array $config;
    private array $brandingConfig;

    public function __construct()
    {
        $this->config = config('wallet.apple');
        $this->brandingConfig = config('wallet.branding');
    }

    /**
     * Generate Apple Wallet pass for a member.
     *
     * @param int $memberId Member ID
     * @return array Pass data with file path and serial number
     * @throws Exception
     */
    public function generatePass(int $memberId): array
    {
        // Get member data
        $member = Member::findOrFail($memberId);

        // Generate unique serial number
        $serialNumber = $this->generateSerialNumber();

        // Create pass structure
        $passData = $this->createPassData($member, $serialNumber);

        // Create temporary directory for pass assembly
        $tempDir = $this->createTempDirectory();

        try {
            // Write pass.json
            file_put_contents(
                $tempDir . '/pass.json',
                json_encode($passData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            // Copy images from template
            $this->copyTemplateImages($tempDir);

            // Generate manifest
            $manifest = $this->generateManifest($tempDir);
            file_put_contents($tempDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

            // Sign manifest
            $this->signManifest($tempDir);

            // Create .pkpass file
            $pkpassPath = $this->createPkpass($tempDir, $member->unique_member_id);

            // Save to database
            $this->savePassToDatabase($memberId, $serialNumber, $pkpassPath);

            // Clean up temp directory
            $this->cleanupDirectory($tempDir);

            return [
                'serial_number' => $serialNumber,
                'file_path' => $pkpassPath,
                'member_id' => $memberId,
            ];
        } catch (Exception $e) {
            // Clean up on error
            $this->cleanupDirectory($tempDir);
            throw $e;
        }
    }

    /**
     * Create pass.json data structure.
     */
    private function createPassData(Member $member, string $serialNumber): array
    {
        $fullName = $member->full_name;
        $colors = $this->brandingConfig['colors'];

        return [
            'formatVersion' => 1,
            'passTypeIdentifier' => $this->config['pass_type_id'],
            'serialNumber' => $serialNumber,
            'teamIdentifier' => $this->config['team_id'],
            'organizationName' => $this->config['organization_name'],
            'description' => $this->config['design']['description'],

            // Visual appearance
            'backgroundColor' => $this->hexToRgb($colors['background']),
            'foregroundColor' => $this->hexToRgb($colors['text_light']),
            'labelColor' => $this->hexToRgb($colors['text_light']),
            'logoText' => $this->config['design']['logo_text'],

            // Barcode (QR code with member ID)
            'barcodes' => [
                [
                    'format' => $this->config['design']['barcode_format'],
                    'message' => $member->unique_member_id,
                    'messageEncoding' => $this->config['design']['barcode_encoding'],
                ],
            ],

            // Legacy barcode (for older iOS versions)
            'barcode' => [
                'format' => $this->config['design']['barcode_format'],
                'message' => $member->unique_member_id,
                'messageEncoding' => $this->config['design']['barcode_encoding'],
            ],

            // Generic pass structure (membership card)
            'generic' => [
                'primaryFields' => [
                    [
                        'key' => 'member-name',
                        'label' => 'MEMBER',
                        'value' => $fullName,
                    ],
                ],
                'secondaryFields' => [
                    [
                        'key' => 'member-id',
                        'label' => 'MEMBER ID',
                        'value' => $member->unique_member_id,
                    ],
                ],
                'auxiliaryFields' => [
                    [
                        'key' => 'status',
                        'label' => 'STATUS',
                        'value' => ucfirst($member->status),
                    ],
                ],
                'backFields' => [
                    [
                        'key' => 'email',
                        'label' => 'Email',
                        'value' => $member->email,
                    ],
                    [
                        'key' => 'mobile',
                        'label' => 'Mobile',
                        'value' => $member->mobile,
                    ],
                    [
                        'key' => 'member-since',
                        'label' => 'Member Since',
                        'value' => $member->created_at->format('F j, Y'),
                    ],
                    [
                        'key' => 'support',
                        'label' => 'Support',
                        'value' => $this->brandingConfig['support_email'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Convert hex color to RGB string.
     */
    private function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgb($r, $g, $b)";
    }

    /**
     * Generate unique serial number.
     */
    private function generateSerialNumber(): string
    {
        return uniqid('PASS-', true);
    }

    /**
     * Create temporary directory for pass assembly.
     */
    private function createTempDirectory(): string
    {
        $tempDir = sys_get_temp_dir() . '/pkpass_' . uniqid();
        if (!mkdir($tempDir, 0755, true)) {
            throw new Exception('Failed to create temporary directory.');
        }
        return $tempDir;
    }

    /**
     * Copy template images to pass directory.
     */
    private function copyTemplateImages(string $targetDir): void
    {
        $templateDir = $this->config['template_path'];

        // Required images
        $images = ['logo.png', 'logo@2x.png', 'icon.png', 'icon@2x.png'];

        foreach ($images as $image) {
            $sourcePath = $templateDir . $image;
            $targetPath = $targetDir . '/' . $image;

            if (file_exists($sourcePath)) {
                copy($sourcePath, $targetPath);
            }
        }

        // Optional background
        if (file_exists($templateDir . 'background.png')) {
            copy($templateDir . 'background.png', $targetDir . '/background.png');
        }
        if (file_exists($templateDir . 'background@2x.png')) {
            copy($templateDir . 'background@2x.png', $targetDir . '/background@2x.png');
        }
    }

    /**
     * Generate manifest.json with SHA1 hashes of all files.
     */
    private function generateManifest(string $passDir): array
    {
        $manifest = [];
        $files = scandir($passDir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $passDir . '/' . $file;
            if (is_file($filePath)) {
                $manifest[$file] = sha1_file($filePath);
            }
        }

        return $manifest;
    }

    /**
     * Sign manifest using certificates.
     */
    private function signManifest(string $passDir): void
    {
        $manifestPath = $passDir . '/manifest.json';
        $signaturePath = $passDir . '/signature';

        // Check if certificates exist
        if (!file_exists($this->config['certificate_path'])) {
            throw new Exception('Pass certificate not found. Please configure Apple Developer certificates.');
        }

        if (!file_exists($this->config['wwdr_certificate_path'])) {
            throw new Exception('WWDR certificate not found. Please download from Apple.');
        }

        // Read certificate
        $p12Content = file_get_contents($this->config['certificate_path']);
        $certs = [];

        if (!openssl_pkcs12_read($p12Content, $certs, $this->config['certificate_password'])) {
            throw new Exception('Failed to read pass certificate. Check password.');
        }

        // Sign manifest
        if (!openssl_pkcs7_sign(
            $manifestPath,
            $signaturePath,
            $certs['cert'],
            $certs['pkey'],
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
            $this->config['wwdr_certificate_path']
        )) {
            throw new Exception('Failed to sign manifest.');
        }

        // Convert signature to DER format
        $signatureContent = file_get_contents($signaturePath);
        $signatureStart = strpos($signatureContent, "\n\n");

        if ($signatureStart !== false) {
            $signatureContent = substr($signatureContent, $signatureStart + 2);
            $signatureEnd = strpos($signatureContent, "\n\n");

            if ($signatureEnd !== false) {
                $signatureContent = substr($signatureContent, 0, $signatureEnd);
            }

            file_put_contents($signaturePath, base64_decode($signatureContent));
        }
    }

    /**
     * Create .pkpass ZIP file.
     */
    private function createPkpass(string $passDir, string $memberId): string
    {
        // Ensure output directory exists
        $outputDir = $this->config['output_path'];
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $pkpassPath = $outputDir . $memberId . '.pkpass';

        // Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($pkpassPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Failed to create .pkpass file.');
        }

        // Add all files from pass directory
        $files = scandir($passDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $passDir . '/' . $file;
            if (is_file($filePath)) {
                $zip->addFile($filePath, $file);
            }
        }

        $zip->close();

        return $pkpassPath;
    }

    /**
     * Save pass information to database.
     */
    private function savePassToDatabase(int $memberId, string $serialNumber, string $pkpassPath): void
    {
        WalletPass::updateOrCreate(
            ['member_id' => $memberId],
            [
                'apple_serial_number' => $serialNumber,
                'apple_pass_path' => $pkpassPath,
                'barcode_data' => Member::find($memberId)->unique_member_id,
                'status' => 'active',
            ]
        );
    }

    /**
     * Clean up temporary directory.
     */
    private function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->cleanupDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * Get pass path for member.
     */
    public function getPassPath(int $memberId): ?string
    {
        $walletPass = WalletPass::where('member_id', $memberId)
            ->where('status', 'active')
            ->first();

        return $walletPass ? $walletPass->apple_pass_path : null;
    }
}
