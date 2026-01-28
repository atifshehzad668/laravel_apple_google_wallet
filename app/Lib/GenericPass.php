<?php

namespace App\Lib;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Client as GoogleClient;
use Google\Service\Walletobjects;
use Google\Service\Walletobjects\GenericObject;
use Google\Service\Walletobjects\GenericClass;
use Google\Service\Walletobjects\Barcode;
use Google\Service\Walletobjects\ImageModuleData;
use Google\Service\Walletobjects\LinksModuleData;
use Google\Service\Walletobjects\TextModuleData;
use Google\Service\Walletobjects\TranslatedString;
use Google\Service\Walletobjects\LocalizedString;
use Google\Service\Walletobjects\ImageUri;
use Google\Service\Walletobjects\Image;
use Google\Service\Walletobjects\Uri;
use Illuminate\Support\Facades\Log;

/** Class for creating and managing Generic passes in Google Wallet. */
class GenericPass
{
    /**
     * The Google API Client
     * https://github.com/google/google-api-php-client
     */
    public GoogleClient $client;

    /**
     * Path to service account key file from Google Cloud Console.
     */
    public string $keyFilePath;

    /**
     * Service account credentials for Google Wallet APIs.
     */
    public ServiceAccountCredentials $credentials;

    /**
     * Google Wallet service client.
     */
    public Walletobjects $service;

    public function __construct()
    {
        $this->keyFilePath = config('wallet.google.service_account_file');
        $this->auth();
    }

    /**
     * Create authenticated HTTP client using a service account file.
     */
    public function auth()
    {
        $this->credentials = new ServiceAccountCredentials(
            Walletobjects::WALLET_OBJECT_ISSUER,
            $this->keyFilePath
        );

        // Initialize Google Wallet API service
        $this->client = new GoogleClient();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes(Walletobjects::WALLET_OBJECT_ISSUER);
        $this->client->setAuthConfig($this->keyFilePath);

        $this->service = new Walletobjects($this->client);
    }

    /**
     * Create a class.
     *
     * @param string $issuerId The issuer ID being used for this request.
     * @param string $classSuffix Developer-defined unique ID for this pass class.
     * @return string The pass class ID: "{$issuerId}.{$classSuffix}"
     */
    public function createClass(string $issuerId, string $classSuffix)
    {
        try {
            $this->service->genericclass->get("{$issuerId}.{$classSuffix}");
            return "{$issuerId}.{$classSuffix}";
        } catch (\Google\Service\Exception $ex) {
            if (empty($ex->getErrors()) || $ex->getErrors()[0]['reason'] != 'classNotFound') {
                Log::error('Google Wallet Generic Class get failed: ' . $ex->getMessage());
                return "{$issuerId}.{$classSuffix}";
            }
        }

        $newClass = new GenericClass([
            'id' => "{$issuerId}.{$classSuffix}",
            'issuerName' => config('wallet.google.design.issuer_name', 'Premium Membership Club'),
            'reviewStatus' => 'UNDER_REVIEW'
        ]);

        $response = $this->service->genericclass->insert($newClass);
        return $response->id;
    }

    /**
     * Fetch all objects for a given class.
     */
    public function listObjects(string $issuerId, string $classSuffix)
    {
        try {
            $response = $this->service->genericobject->listGenericobject([
                'classId' => "{$issuerId}.{$classSuffix}"
            ]);
            return $response->getResources();
        } catch (\Google\Service\Exception $ex) {
            Log::error('Google Wallet Generic Object list failed: ' . $ex->getMessage());
            return [];
        }
    }

    /**
     * Get a class.
     */
    public function getClass(string $issuerId, string $classSuffix)
    {
        try {
            return $this->service->genericclass->get("{$issuerId}.{$classSuffix}");
        } catch (\Google\Service\Exception $ex) {
            Log::error('Google Wallet Generic Class get failed: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Create an object.
     */
    public function createObject(string $issuerId, string $classSuffix, string $objectSuffix, array $data = [])
    {
        $existingObject = null;
        try {
            $existingObject = $this->service->genericobject->get("{$issuerId}.{$objectSuffix}");
        } catch (\Google\Service\Exception $ex) {
            if (empty($ex->getErrors()) || $ex->getErrors()[0]['reason'] != 'resourceNotFound') {
                Log::error('Google Wallet Generic Object get failed: ' . $ex->getMessage());
            }
        }

        $objectData = [
            'id' => "{$issuerId}.{$objectSuffix}",
            'classId' => "{$issuerId}.{$classSuffix}",
            'state' => 'ACTIVE',
            'cardTitle' => new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => 'en-US',
                    'value' => config('wallet.google.design.card_title', 'Member Card')
                ])
            ]),
            'header' => new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => 'en-US',
                    'value' => $data['name'] ?? 'Member'
                ])
            ]),
            'hexBackgroundColor' => config('wallet.google.design.hex_background_color', '#0f172a'),
            'logo' => new Image([
                'sourceUri' => new ImageUri([
                    'uri' => config('wallet.google.design.logo_url')
                ]),
                'contentDescription' => new LocalizedString([
                    'defaultValue' => new TranslatedString([
                        'language' => 'en-US',
                        'value' => config('wallet.google.design.logo_description')
                    ])
                ])
            ]),
            'barcode' => new Barcode([
                'type' => 'QR_CODE',
                'value' => $data['barcode_value'] ?? 'QR code value'
            ]),
            'textModulesData' => [
                new TextModuleData([
                    'header' => 'Member ID',
                    'body' => $data['member_id'] ?? 'N/A',
                    'id' => 'MEMBER_ID'
                ]),
                new TextModuleData([
                    'header' => 'Email',
                    'body' => $data['email'] ?? 'N/A',
                    'id' => 'EMAIL'
                ]),
                new TextModuleData([
                    'header' => 'Joined Date',
                    'body' => $data['joined_date'] ?? 'N/A',
                    'id' => 'JOINED_DATE'
                ])
            ]
        ];

        // Add subheader if provided
        if (!empty($data['subheader'])) {
            $objectData['subheader'] = new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => 'en-US',
                    'value' => $data['subheader']
                ])
            ]);
        }

        // Add hero image if configured
        $heroImageUrl = $data['hero_image_url'] ?? config('wallet.google.design.hero_image_url');
        if (!empty($heroImageUrl)) {
            $objectData['heroImage'] = new Image([
                'sourceUri' => new ImageUri([
                    'uri' => $heroImageUrl
                ]),
                'contentDescription' => new LocalizedString([
                    'defaultValue' => new TranslatedString([
                        'language' => 'en-US',
                        'value' => $data['hero_image_description'] ?? config('wallet.google.design.hero_image_description', 'Hero Image')
                    ])
                ])
            ]);
        }

        // Add wide image if configured
        $wideImageUrl = $data['wide_image_url'] ?? config('wallet.google.design.wide_image_url');
        if (!empty($wideImageUrl)) {
            $objectData['wideLogoImage'] = new Image([
                'sourceUri' => new ImageUri([
                    'uri' => $wideImageUrl
                ]),
                'contentDescription' => new LocalizedString([
                    'defaultValue' => new TranslatedString([
                        'language' => 'en-US',
                        'value' => $data['wide_image_description'] ?? config('wallet.google.design.wide_image_description', 'Wide Banner')
                    ])
                ])
            ]);
        }

        // Add links module if website or support email is configured
        $websiteUrl = config('wallet.branding.website');
        $supportEmail = config('wallet.branding.support_email');
        
        if ($websiteUrl || $supportEmail) {
            $uris = [];
            
            if ($websiteUrl) {
                $uris[] = new Uri([
                    'uri' => $websiteUrl,
                    'description' => 'Visit Website',
                    'id' => 'WEBSITE'
                ]);
            }
            
            if ($supportEmail) {
                $uris[] = new Uri([
                    'uri' => 'mailto:' . $supportEmail,
                    'description' => 'Contact Support',
                    'id' => 'SUPPORT'
                ]);
            }
            
            if (!empty($uris)) {
                $objectData['linksModuleData'] = new LinksModuleData([
                    'uris' => $uris
                ]);
            }
        }

        $newObject = new GenericObject($objectData);

        if ($existingObject) {
            $response = $this->service->genericobject->update("{$issuerId}.{$objectSuffix}", $newObject);
        } else {
            $response = $this->service->genericobject->insert($newObject);
        }
        return $response;
    }

    /**
     * Expire an object.
     */
    public function expireObject(string $issuerId, string $objectSuffix)
    {
        try {
            $this->service->genericobject->get("{$issuerId}.{$objectSuffix}");
        } catch (\Google\Service\Exception $ex) {
            if (!empty($ex->getErrors()) && $ex->getErrors()[0]['reason'] == 'resourceNotFound') {
                return "{$issuerId}.{$objectSuffix}";
            } else {
                Log::error('Google Wallet Generic Object get failed for expiration: ' . $ex->getMessage());
                return "{$issuerId}.{$objectSuffix}";
            }
        }

        $patchBody = new GenericObject([
            'state' => 'EXPIRED'
        ]);

        $response = $this->service->genericobject->patch("{$issuerId}.{$objectSuffix}", $patchBody);
        return $response->id;
    }

    /**
     * Generate a signed JWT that creates a new pass class and object.
     */
    public function createJwtNewObjects(string $issuerId, string $classSuffix, string $objectSuffix, array $data = [])
    {
        $newClass = new GenericClass([
            'id' => "{$issuerId}.{$classSuffix}",
            'issuerName' => config('wallet.google.design.issuer_name', 'Premium Membership Club'),
        ]);

        $newObject = new GenericObject([
            'id' => "{$issuerId}.{$objectSuffix}",
            'classId' => "{$issuerId}.{$classSuffix}",
            'state' => 'ACTIVE',
            'cardTitle' => new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => 'en-US',
                    'value' => config('wallet.google.design.card_title', 'Member Card')
                ])
            ]),
            'header' => new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => 'en-US',
                    'value' => $data['name'] ?? 'Member'
                ])
            ]),
            'hexBackgroundColor' => config('wallet.google.design.hex_background_color', '#0f172a'),
            'logo' => new Image([
                'sourceUri' => new ImageUri([
                    'uri' => config('wallet.google.design.logo_url')
                ]),
                'contentDescription' => new LocalizedString([
                    'defaultValue' => new TranslatedString([
                        'language' => 'en-US',
                        'value' => config('wallet.google.design.logo_description')
                    ])
                ])
            ]),
            'barcode' => new Barcode([
                'type' => 'QR_CODE',
                'value' => $data['barcode_value'] ?? 'QR code value'
            ]),
            'textModulesData' => [
                new TextModuleData([
                    'header' => 'Member ID',
                    'body' => $data['member_id'] ?? 'N/A',
                    'id' => 'MEMBER_ID'
                ]),
                new TextModuleData([
                    'header' => 'Email',
                    'body' => $data['email'] ?? 'N/A',
                    'id' => 'EMAIL'
                ]),
                new TextModuleData([
                    'header' => 'Joined Date',
                    'body' => $data['joined_date'] ?? 'N/A',
                    'id' => 'JOINED_DATE'
                ])
            ]
        ]);

        $serviceAccount = json_decode(file_get_contents($this->keyFilePath), true);

        $claims = [
            'iss' => $serviceAccount['client_email'],
            'aud' => 'google',
            'origins' => [],
            'typ' => 'savetowallet',
            'payload' => [
                'genericClasses' => [$newClass],
                'genericObjects' => [$newObject]
            ]
        ];

        $token = JWT::encode(
            $claims,
            $serviceAccount['private_key'],
            'RS256'
        );

        return "https://pay.google.com/gp/v/save/{$token}";
    }
}