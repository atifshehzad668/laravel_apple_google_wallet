<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Client as GoogleClient;
use Google\Service\Walletobjects;
use Google\Service\Walletobjects\EventSeat;
use Google\Service\Walletobjects\LatLongPoint;
use Google\Service\Walletobjects\Barcode;
use Google\Service\Walletobjects\ImageModuleData;
use Google\Service\Walletobjects\LinksModuleData;
use Google\Service\Walletobjects\TextModuleData;
use Google\Service\Walletobjects\ImageUri;
use Google\Service\Walletobjects\Image;
use Google\Service\Walletobjects\EventTicketObject;
use Google\Service\Walletobjects\Message;
use Google\Service\Walletobjects\AddMessageRequest;
use Google\Service\Walletobjects\Uri;
use Google\Service\Walletobjects\TranslatedString;
use Google\Service\Walletobjects\LocalizedString;
use Google\Service\Walletobjects\EventTicketClass;
use Exception;

class GoogleEventTicketService
{
    public GoogleClient $client;
    public string $keyFilePath;
    public ServiceAccountCredentials $credentials;
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
        if (!file_exists($this->keyFilePath)) {
            return; // Handle this gracefully in methods
        }

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
     * Generate a signed JWT that creates a new pass class and object.
     */
    public function createJwtEventTicket(string $issuerId, string $classSuffix, string $objectSuffix, array $memberDetails = [])
    {
        if (!file_exists($this->keyFilePath)) {
            throw new Exception("Google Service Account key file not found at: {$this->keyFilePath}");
        }

        // See link below for more information on required properties
        // https://developers.google.com/wallet/tickets/events/rest/v1/eventticketclass
        $newClass = new EventTicketClass([
            'id' => "{$issuerId}.{$classSuffix}",
            'issuerName' => config('wallet.branding.organization_name', 'Your Organization'),
            'reviewStatus' => 'UNDER_REVIEW',
            'eventName' => new LocalizedString([
                'defaultValue' => new TranslatedString([
                    'language' => 'en-US',
                    'value' => 'Membership Event'
                ])
            ])
        ]);

        // See link below for more information on required properties
        // https://developers.google.com/wallet/tickets/events/rest/v1/eventticketobject
        $newObject = new EventTicketObject([
            'id' => "{$issuerId}.{$objectSuffix}",
            'classId' => "{$issuerId}.{$classSuffix}",
            'state' => 'ACTIVE',
            'barcode' => new Barcode([
                'type' => 'QR_CODE',
                'value' => $memberDetails['unique_member_id'] ?? 'QR_CODE_VALUE'
            ]),
            'ticketHolderName' => $memberDetails['name'] ?? 'Member Name',
            'ticketNumber' => $memberDetails['unique_member_id'] ?? 'TICKET_NUMBER'
        ]);

        // The service account credentials are used to sign the JWT
        $serviceAccount = json_decode(file_get_contents($this->keyFilePath), true);

        // Create the JWT as an array of key/value pairs
        $claims = [
            'iss' => $serviceAccount['client_email'],
            'aud' => 'google',
            'origins' => [],
            'typ' => 'savetowallet',
            'payload' => [
                'eventTicketClasses' => [$newClass],
                'eventTicketObjects' => [$newObject]
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
