<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\WalletPass;
use App\Lib\EventTicketPass;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Inertia\Inertia;

class PassController extends Controller
{
    protected EventTicketPass $eventTicketPass;

    public function __construct(EventTicketPass $eventTicketPass)
    {
        $this->eventTicketPass = $eventTicketPass;
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

        if (!$walletPass || !$walletPass->apple_pass_path) {
            abort(404, 'Pass not found.');
        }

        if (!file_exists($walletPass->apple_pass_path)) {
            abort(404, 'Pass file not found.');
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


    public function GooglePass()
    {
        return view('admin.google_pass');
    }
    public function downloadGooglePass(Request $request)
    {
        $member_id = $request->query('id') ?? $request->query('member_id');
        
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

        // Fallback: Generate the "Save to Wallet" JWT URL
        $issuerId = config('wallet.google.issuer_id');
        $classSuffix = config('wallet.google.class_id');
        $objectSuffix = 'obj_' . $member->unique_member_id;

        $googlePassUrl = $this->eventTicketPass->createJwtExistingObjects(
            $issuerId,
            $classSuffix,
            $objectSuffix
        );

        // Update or create the wallet pass record and mark as added
        WalletPass::updateOrCreate(
            ['member_id' => $member->id],
            [
                'email' => $member->email,
                'google_pass_url' => $googlePassUrl,
                'is_google_added' => true,
                'status' => 'active'
            ]
        );

        return redirect()->away($googlePassUrl);
    }
}
