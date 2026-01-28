<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\WalletPass;
use App\Services\GenericPassService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Inertia\Inertia;

class PassController extends Controller
{
    protected GenericPassService $genericPassService;

    public function __construct(GenericPassService $genericPassService)
    {
        $this->genericPassService = $genericPassService;
    }

    /**
     * Download Apple Wallet pass (.pkpass).
     */
    public function download(int $id): BinaryFileResponse|Response
    {
        $member = Member::findOrFail($id);
        $walletPass = WalletPass::where('member_id', $member->id)
            ->where('status', 'active')
            ->first();

        // If no pass exists or no Apple pass path, generate it
        if (!$walletPass || !$walletPass->apple_pass_path || !file_exists($walletPass->apple_pass_path)) {
            try {
                $appleWalletService = app(\App\Services\AppleWalletService::class);
                $appleWalletService->generatePass($member->id);
                
                // Reload the wallet pass
                $walletPass = WalletPass::where('member_id', $member->id)
                    ->where('status', 'active')
                    ->first();
                    
                if (!$walletPass || !$walletPass->apple_pass_path) {
                    abort(500, 'Failed to generate Apple Wallet pass.');
                }
            } catch (\Exception $e) {
                \Log::error('Apple Wallet pass generation failed: ' . $e->getMessage());
                abort(500, 'Failed to generate Apple Wallet pass: ' . $e->getMessage());
            }
        }

        if (!file_exists($walletPass->apple_pass_path)) {
            abort(404, 'Pass file not found.');
        }

        if ($walletPass) {
            $walletPass->update(['is_apple_added' => true]);
        }

        return response()->download(
            $walletPass->apple_pass_path,
            $member->unique_member_id . '.pkpass',
            [
                'Content-Type' => 'application/vnd.apple.pkpass',
                'Content-Disposition' => 'attachment; filename="' . $member->unique_member_id . '.pkpass"',
            ]
        );
    }

    /**
     * Download Google Wallet pass (redirect to Google Wallet).
     */


    public function downloadGooglePass(Request $request)
    {
        $member_id = $request->query('id') ?? $request->query('member_id');
        // done
        if (!$member_id) {
            // Check if we have a default or fallback member for testing
            $member = Member::latest()->first();
        } else {
            $member = Member::find($member_id);
        }

        if (!$member) {
            return redirect()->back()->with('error', 'Member not found.');
        }

        $walletPass = WalletPass::where('member_id', $member->id)->first();

        if ($walletPass && $walletPass->google_pass_url) {
            $walletPass->update(['is_google_added' => true]);
            return redirect()->away($walletPass->google_pass_url);
        }

        // Fallback: Generate pass using GenericPassService
        try {
            $passData = $this->genericPassService->generatePass($member->id);
            $googlePassUrl = $passData['pass_url'];

            // Mark as added
            WalletPass::updateOrCreate(
                ['member_id' => $member->id],
                [
                    'google_pass_url' => $googlePassUrl,
                    'is_google_added' => true,
                    'status' => 'active'
                ]
            );

            return redirect()->away($googlePassUrl);
        } catch (\Exception $e) {
            Log::error('Google Wallet pass generation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Google Wallet pass.');
        }
    }
}
