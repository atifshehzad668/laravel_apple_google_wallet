<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\WalletPass;
use App\Services\MemberService;
use App\Services\AppleWalletService;
use App\Services\GoogleWalletService;
use App\Services\EmailNotificationService;
use App\Lib\EventTicketPass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class MemberController extends Controller
{
    protected MemberService $memberService;
    protected AppleWalletService $appleWalletService;
    protected GoogleWalletService $googleWalletService;
    protected EmailNotificationService $emailService;
    protected EventTicketPass $eventTicketPass;

    public function __construct(
        MemberService $memberService,
        AppleWalletService $appleWalletService,
        GoogleWalletService $googleWalletService,
        EmailNotificationService $emailService,
        EventTicketPass $eventTicketPass
    ) {
        $this->memberService = $memberService;
        $this->appleWalletService = $appleWalletService;
        $this->googleWalletService = $googleWalletService;
        $this->emailService = $emailService;
        $this->eventTicketPass = $eventTicketPass;
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('register');
    }

    /**
     * Handle member registration.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Create member
            $member = $this->memberService->createMember($request->all());

            // Generate Apple Wallet pass
            $applePass = null;
            $applePassUrl = null;
            try {
                $applePass = $this->appleWalletService->generatePass($member->id);
                $applePassUrl = route('pass.download', ['id' => $member->id]);
            } catch (Exception $e) {
                Log::error('Apple Wallet pass generation failed: ' . $e->getMessage());
            }

            // Generate Google Wallet pass (Event Ticket)
            $googlePassUrl = null;
            try {
                // Check if Google Wallet service is properly configured
                if ($this->eventTicketPass->service === null) {
                    Log::warning('Google Wallet service not initialized. Please configure service account credentials.');
                    // Set a placeholder message instead of null
                    $googlePassUrl = '#'; // Will be handled in the email template
                } else {
                    $issuerId = config('wallet.google.issuer_id');
                    $classSuffix = config('wallet.google.class_id');
                    $objectSuffix = 'obj_' . $member->unique_member_id;

                    // Create the Google Wallet object with member details
                    $googlePassResponse = $this->eventTicketPass->createObject($issuerId, $classSuffix, $objectSuffix, [
                        'name' => $member->full_name,
                        'email' => $member->email,
                        'ticket_number' => $member->unique_member_id,
                        'barcode_value' => $member->unique_member_id,
                        // You can add more mapping here if needed
                    ]);

                    if ($googlePassResponse) {
                        // Generate the "Save to Wallet" JWT URL
                        $googlePassUrl = $this->eventTicketPass->createJwtExistingObjects(
                            $issuerId,
                            $classSuffix,
                            $objectSuffix
                        );

                        // Store Google Wallet pass details in the database
                        \App\Models\WalletPass::updateOrCreate(
                            ['member_id' => $member->id],
                            [
                                'email' => $member->email,
                                'google_object_id' => $googlePassResponse->id,
                                'google_class_id' => $googlePassResponse->classId,
                                'google_pass_url' => $googlePassUrl,
                                'google_state' => $googlePassResponse->state,
                                'ticket_holder_name' => $googlePassResponse->ticketHolderName,
                                'ticket_number' => $googlePassResponse->ticketNumber,
                                'barcode_type' => $googlePassResponse->barcode->type ?? null,
                                'barcode_value' => $googlePassResponse->barcode->value ?? null,
                                'barcode_data' => $member->unique_member_id,
                                'status' => 'active',
                                // Extracting nested fields safely
                                'seat' => $googlePassResponse->seatInfo->seat->defaultValue->value ?? null,
                                'row' => $googlePassResponse->seatInfo->row->defaultValue->value ?? null,
                                'section' => $googlePassResponse->seatInfo->section->defaultValue->value ?? null,
                                'gate' => $googlePassResponse->seatInfo->gate->defaultValue->value ?? null,
                                'hero_image_url' => $googlePassResponse->heroImage->sourceUri->uri ?? null,
                                'latitude' => $googlePassResponse->locations[0]->latitude ?? null,
                                'longitude' => $googlePassResponse->locations[0]->longitude ?? null,
                            ]
                        );
                    }
                }
            } catch (Exception $e) {
                Log::error('Google Wallet Event Ticket generation failed: ' . $e->getMessage());
                $googlePassUrl = null; // Explicitly set to null on error
            }

            // Send email
            $emailSent = $this->emailService->sendMembershipEmail($member, $applePassUrl, $googlePassUrl);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Check your email for wallet passes.',
                'data' => [
                    'member_id' => $member->id,
                    'unique_member_id' => $member->unique_member_id,
                    'email' => $member->email,
                    'apple_pass_url' => $applePassUrl,
                    'google_pass_url' => $googlePassUrl,
                    'email_sent' => $emailSent,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Member registration failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
