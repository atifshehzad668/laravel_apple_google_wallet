<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\ActivityLog;
use App\Services\MemberService;
use App\Services\AppleWalletService;
use App\Services\GoogleWalletService;
use App\Services\EmailNotificationService;
use App\Services\GenericPassService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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
     * Display member list.
     */
    public function index(Request $request)
    {
        $query = Member::with('walletPass');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('unique_member_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.members.index', compact('members'));
    }

    /**
     * Show member creation form.
     */
    public function create()
    {
        return view('admin.members.create');
    }

    /**
     * Store a new member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:members,email',
            'mobile' => 'required|string|max:20',
        ]);

        try {
            // Create member
            $member = $this->memberService->createMember($request->all());

            // Generate Apple Wallet pass
            $applePassUrl = null;
            try {
                $this->appleWalletService->generatePass($member->id);
                $applePassUrl = route('pass.download', ['id' => $member->id]);
            } catch (Exception $e) {
                Log::error('Apple Wallet pass generation failed: ' . $e->getMessage());
            }

            // Generate Google Wallet pass
            $googlePassUrl = null;
            try {
                $passData = $this->genericPassService->generatePass($member->id);
                $googlePassUrl = $passData['pass_url'];
            } catch (Exception $e) {
                Log::error('Google Wallet generation failed: ' . $e->getMessage());
            }

            // Send email - DEFERRED: Email will be sent when user clicks "Add to Google Wallet"
            // $this->emailService->sendMembershipEmail($member, $applePassUrl, $googlePassUrl);

            return redirect()->route('admin.members.index')->with('success', 'Member created successfully.');
        } catch (Exception $e) {
            Log::error('Member creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create member.')->withInput();
        }
    }

    /**
     * Show member details.
     */
    public function show(int $id)
    {
        $member = Member::with('walletPass')->findOrFail($id);
        return view('admin.members.show', compact('member'));
    }

    /**
     * Regenerate wallet passes for a member.
     */
    public function regeneratePass(Request $request): JsonResponse
    {
        try {
            $memberId = $request->input('member_id');
            $member = Member::findOrFail($memberId);

            // Regenerate Apple Wallet pass
            $applePass = null;
            $applePassUrl = null;
            try {
                $applePass = $this->appleWalletService->generatePass($member->id);
                $applePassUrl = route('pass.download', ['id' => $member->id]);
            } catch (Exception $e) {
                Log::error('Apple Wallet pass regeneration failed: ' . $e->getMessage());
            }

            // Regenerate Google Wallet pass (Generic Pass)
            $googlePassUrl = null;
            $googlePassPdf = null;
            try {
                $passData = $this->genericPassService->regeneratePass($member->id);
                $googlePassUrl = $passData['pass_url'];
                // Refresh member to get new PDF path
                $member->load('walletPass');
            } catch (Exception $e) {
                Log::error('Google Wallet regeneration failed: ' . $e->getMessage());
            }

            // Send email
            $emailSent = $this->emailService->sendPassRegenerationEmail($member, $applePassUrl, $googlePassUrl);

            // Log activity
            ActivityLog::logAction(
                Auth::guard('admin')->id(),
                'pass_regenerated',
                'member',
                $member->id,
                ['member_id' => $member->unique_member_id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pass regenerated successfully and email sent.',
                'data' => [
                    'apple_pass_url' => $applePassUrl,
                    'google_pass_url' => $googlePassUrl,
                    'email_sent' => $emailSent,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Pass regeneration failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Pass regeneration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a member.
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $memberId = $request->input('member_id');
            $member = Member::with('walletPass')->findOrFail($memberId);

            // Perform hard delete via service (cascading will handle walletPass if defined in DB or model)
            $this->memberService->deleteMember($memberId);

            // Log activity
            ActivityLog::logAction(
                Auth::guard('admin')->id(),
                'member_deleted',
                'member',
                $member->id,
                [
                    'member_id' => $member->unique_member_id,
                    'name' => $member->full_name,
                    'email' => $member->email,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Member and their wallet passes deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Member deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Member deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of a wallet pass.
     */
    public function updatePassStatus(Request $request): JsonResponse
    {
        try {
            $memberId = $request->input('member_id');
            $status = $request->input('status');

            $member = Member::with('walletPass')->findOrFail($memberId);
            
            if ($member->walletPass) {
                $member->walletPass->update(['status' => $status]);
                
                // Regenerate pass (and PDF) with the new status
                $this->genericPassService->regeneratePass($member->id, $status);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pass status updated successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Pass status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pass status.',
            ], 500);
        }
    }
}
