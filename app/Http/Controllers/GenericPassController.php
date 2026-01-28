<?php

namespace App\Http\Controllers;

use App\Services\GenericPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Controller for managing Generic Google Wallet passes.
 */
class GenericPassController extends Controller
{
    private GenericPassService $genericPassService;

    public function __construct(GenericPassService $genericPassService)
    {
        $this->genericPassService = $genericPassService;
    }

    /**
     * Generate a generic pass for a member.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePass(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id'
        ]);

        try {
            $passData = $this->genericPassService->generatePass($request->member_id);

            return response()->json([
                'success' => true,
                'message' => 'Generic pass generated successfully',
                'data' => $passData
            ]);
        } catch (Exception $e) {
            Log::error('Generic pass generation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate generic pass: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pass URL for a member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPassUrl($memberId)
    {
        try {
            $passUrl = $this->genericPassService->getPassUrl($memberId);

            if (!$passUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active pass found for this member'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pass_url' => $passUrl
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Get pass URL failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pass URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect to Google Wallet to download the pass.
     *
     * @param int $memberId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadGooglePass($memberId)
    {
        try {
            $passUrl = $this->genericPassService->getPassUrl($memberId);

            if (!$passUrl) {
                // If no pass exists, generate one
                $passData = $this->genericPassService->generatePass($memberId);
                $passUrl = $passData['pass_url'];
            }

            return redirect($passUrl);
        } catch (Exception $e) {
            Log::error('Google pass download redirect failed: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to download Google Wallet pass');
        }
    }

    /**
     * Regenerate pass for a member.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function regeneratePass(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id'
        ]);

        try {
            $passData = $this->genericPassService->regeneratePass($request->member_id);

            return response()->json([
                'success' => true,
                'message' => 'Generic pass regenerated successfully',
                'data' => $passData
            ]);
        } catch (Exception $e) {
            Log::error('Generic pass regeneration failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate generic pass: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke (expire) a pass for a member.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokePass(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id'
        ]);

        try {
            $result = $this->genericPassService->revokePass($request->member_id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active pass found to revoke'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Generic pass revoked successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Generic pass revocation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke generic pass: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active passes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllActivePasses()
    {
        try {
            $passes = $this->genericPassService->getAllActivePasses();

            return response()->json([
                'success' => true,
                'data' => $passes
            ]);
        } catch (Exception $e) {
            Log::error('Get all active passes failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active passes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a member has an active pass.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkActivePass($memberId)
    {
        try {
            $hasActivePass = $this->genericPassService->hasActivePass($memberId);

            return response()->json([
                'success' => true,
                'data' => [
                    'has_active_pass' => $hasActivePass
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Check active pass failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check active pass: ' . $e->getMessage()
            ], 500);
        }
    }
}
