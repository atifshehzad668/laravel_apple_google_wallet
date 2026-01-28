<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\WalletPass;
use App\Services\MemberService;
use App\Services\AppleWalletService;
use App\Services\GoogleWalletService;
use App\Services\EmailNotificationService;
use App\Services\GenericPassService;
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
    protected GenericPassService $genericPassService;

    public function __construct(
        MemberService $memberService,
        AppleWalletService $appleWalletService,
        GoogleWalletService $googleWalletService,
        EmailNotificationService $emailService,
        GenericPassService $genericPassService
    ) {
        $this->memberService = $memberService;
        $this->appleWalletService = $appleWalletService;
        $this->googleWalletService = $googleWalletService;
        $this->emailService = $emailService;
        $this->genericPassService = $genericPassService;
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

            // Generate Google Wallet pass (Generic Pass)
            $googlePassUrl = null;
            try {
                $passData = $this->genericPassService->generatePass($member->id);
                $googlePassUrl = $passData['pass_url'];
            } catch (Exception $e) {
                Log::error('Google Wallet Generic Pass generation failed: ' . $e->getMessage());
                $googlePassUrl = null;
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
