<?php

namespace App\Examples;

/**
 * Migration Guide: EventTicketPass to GenericPass
 * 
 * This file demonstrates how to migrate from using EventTicketPass
 * to the new GenericPass implementation for member passes.
 */

class MigrationGuide
{
    /**
     * OLD WAY: Using EventTicketPass
     * ================================
     * 
     * The EventTicketPass was designed for event tickets and includes
     * event-specific fields like seat, row, section, and gate.
     */
    public function oldWayEventTicketPass()
    {
        // Old implementation using EventTicketPass
        $eventTicketPass = new \App\Lib\EventTicketPass();
        
        $issuerId = config('wallet.google.issuer_id');
        $classSuffix = 'event_ticket_class';
        $objectSuffix = 'member_123';
        
        // Create class
        $eventTicketPass->createClass($issuerId, $classSuffix);
        
        // Create object with event-specific data
        $passData = [
            'name' => 'John Doe',
            'ticket_number' => 'TICKET-123',
            'barcode_value' => 'MEMBER-123',
            'barcode_type' => 'QR_CODE',
            
            // Event-specific fields (not needed for membership)
            'seat' => '42',
            'row' => 'G3',
            'section' => '5',
            'gate' => 'A',
            
            'hero_image_url' => 'https://example.com/hero.jpg',
            'text_module_header' => 'Member Information',
            'text_module_body' => 'VIP Member',
        ];
        
        $passObject = $eventTicketPass->createObject(
            $issuerId, 
            $classSuffix, 
            $objectSuffix, 
            $passData
        );
        
        // Generate JWT for existing object
        $passUrl = $eventTicketPass->createJwtExistingObjects(
            $issuerId, 
            $classSuffix, 
            $objectSuffix
        );
        
        return $passUrl;
    }
    
    /**
     * NEW WAY: Using GenericPass via GenericPassService
     * =================================================
     * 
     * The GenericPass is more flexible and designed for membership cards.
     * GenericPassService provides a high-level interface with database integration.
     */
    public function newWayGenericPassService($memberId)
    {
        // New implementation using GenericPassService
        $genericPassService = new \App\Services\GenericPassService();
        
        // Simple one-line call handles everything:
        // - Fetches member data
        // - Creates class if needed
        // - Creates pass object
        // - Generates JWT
        // - Saves to database
        $passData = $genericPassService->generatePass($memberId);
        
        return $passData['pass_url'];
    }
    
    /**
     * COMPARISON: Direct API Usage
     * ============================
     */
    public function directComparisonExample($member)
    {
        // ==========================================
        // OLD WAY: EventTicketPass (Manual)
        // ==========================================
        $eventTicketPass = new \App\Lib\EventTicketPass();
        $issuerId = config('wallet.google.issuer_id');
        
        // Manual class creation
        $eventTicketPass->createClass($issuerId, 'event_class');
        
        // Manual object creation with hard-coded structure
        $passObject = $eventTicketPass->createObject(
            $issuerId,
            'event_class',
            $member->unique_member_id,
            [
                'name' => $member->full_name,
                'ticket_number' => $member->unique_member_id,
                'barcode_value' => $member->unique_member_id,
                'seat' => '42',      // Not needed for membership
                'row' => 'G3',       // Not needed for membership
                'section' => '5',    // Not needed for membership
                'gate' => 'A',       // Not needed for membership
            ]
        );
        
        // Manual JWT generation
        $passUrl = $eventTicketPass->createJwtExistingObjects(
            $issuerId,
            'event_class',
            $member->unique_member_id
        );
        
        // Manual database save
        \App\Models\WalletPass::updateOrCreate(
            ['member_id' => $member->id],
            [
                'google_object_id' => "{$issuerId}.{$member->unique_member_id}",
                'google_pass_url' => $passUrl,
                'barcode_data' => $member->unique_member_id,
                'status' => 'active',
            ]
        );
        
        // ==========================================
        // NEW WAY: GenericPassService (Automated)
        // ==========================================
        $genericPassService = new \App\Services\GenericPassService();
        
        // Everything handled automatically
        $passData = $genericPassService->generatePass($member->id);
        
        // Returns structured data:
        // [
        //     'object_id' => '3388000000022345850.MEMBER_123',
        //     'pass_url' => 'https://pay.google.com/gp/v/save/...',
        //     'member_id' => 123,
        //     'member_name' => 'John Doe'
        // ]
        
        return $passData;
    }
    
    /**
     * KEY DIFFERENCES
     */
    public function keyDifferences()
    {
        return [
            'design' => [
                'EventTicketPass' => 'Event-focused with seat information',
                'GenericPass' => 'Membership-focused with customizable fields'
            ],
            
            'api_type' => [
                'EventTicketPass' => 'eventticketobject API',
                'GenericPass' => 'genericobject API'
            ],
            
            'fields' => [
                'EventTicketPass' => [
                    'ticketHolderName',
                    'ticketNumber',
                    'seatInfo' => ['seat', 'row', 'section', 'gate'],
                    'eventName'
                ],
                'GenericPass' => [
                    'cardTitle',
                    'header',
                    'subheader',
                    'textModulesData' => ['Member ID', 'Email', 'Mobile', 'Member Since'],
                    'linksModuleData',
                    'hexBackgroundColor'
                ]
            ],
            
            'customization' => [
                'EventTicketPass' => 'Limited customization',
                'GenericPass' => 'Highly customizable (colors, logos, fields)'
            ],
            
            'integration' => [
                'EventTicketPass' => 'Manual database handling required',
                'GenericPass' => 'Automatic via GenericPassService'
            ],
            
            'member_data' => [
                'EventTicketPass' => 'Must manually prepare data array',
                'GenericPass' => 'Automatically extracts from Member model'
            ],
        ];
    }
    
    /**
     * MIGRATION STEPS
     */
    public function migrationSteps()
    {
        return [
            '1. Update References' => 'Replace EventTicketPass references with GenericPassService',
            '2. Update Routes' => 'Use new generic pass routes',
            '3. Update Controllers' => 'Use GenericPassService instead of EventTicketPass',
            '4. Update Email Templates' => 'Update pass URLs to use generic pass service',
            '5. Regenerate Existing Passes' => 'Run migration script to regenerate all member passes',
            '6. Test Integration' => 'Verify passes work correctly in Google Wallet app',
            '7. Clean Up' => 'Remove old EventTicketPass code if no longer needed',
        ];
    }
    
    /**
     * EXAMPLE: Replacing in Controller
     */
    public function controllerMigrationExample()
    {
        /*
        // ==========================================
        // BEFORE (EventTicketPass)
        // ==========================================
        use App\Lib\EventTicketPass;
        
        public function generatePass($memberId)
        {
            $member = Member::findOrFail($memberId);
            $eventPass = new EventTicketPass();
            
            $issuerId = config('wallet.google.issuer_id');
            $classSuffix = 'event_class';
            $objectSuffix = $member->unique_member_id;
            
            $eventPass->createClass($issuerId, $classSuffix);
            
            $passData = [
                'name' => $member->full_name,
                'ticket_number' => $member->unique_member_id,
                'barcode_value' => $member->unique_member_id,
                // ... more manual configuration
            ];
            
            $eventPass->createObject($issuerId, $classSuffix, $objectSuffix, $passData);
            $passUrl = $eventPass->createJwtExistingObjects($issuerId, $classSuffix, $objectSuffix);
            
            // Manual database save
            WalletPass::updateOrCreate(...);
            
            return response()->json(['pass_url' => $passUrl]);
        }
        
        // ==========================================
        // AFTER (GenericPassService)
        // ==========================================
        use App\Services\GenericPassService;
        
        public function generatePass($memberId)
        {
            $genericPassService = app(GenericPassService::class);
            $passData = $genericPassService->generatePass($memberId);
            
            return response()->json($passData);
        }
        */
    }
    
    /**
     * EXAMPLE: Email Template Migration
     */
    public function emailMigrationExample()
    {
        /*
        <!-- ========================================== -->
        <!-- BEFORE (EventTicketPass) -->
        <!-- ========================================== -->
        @if($eventTicketUrl)
            <a href="{{ $eventTicketUrl }}">Add Event Ticket to Wallet</a>
        @endif
        
        <!-- ========================================== -->
        <!-- AFTER (GenericPass) -->
        <!-- ========================================== -->
        @if($googleWalletUrl)
            <a href="{{ $googleWalletUrl }}" 
               style="display: inline-block; background: #4285f4; color: white; 
                      padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Add to Google Wallet
            </a>
        @endif
        
        <!-- Or using route helper -->
        <a href="{{ route('google.wallet.generic.download', ['memberId' => $member->id]) }}">
            Add to Google Wallet
        </a>
        */
    }
}
