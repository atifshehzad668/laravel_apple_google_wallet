<?php

/*
|--------------------------------------------------------------------------
| Generic Pass Routes Example
|--------------------------------------------------------------------------
|
| These routes demonstrate how to integrate the Generic Pass functionality
| Add these to your routes/web.php or routes/api.php file
|
*/

use App\Http\Controllers\GenericPassController;

// API Routes for Generic Pass Management
Route::prefix('api/generic-pass')->middleware(['auth:sanctum'])->group(function () {
    
    // Generate a new generic pass for a member
    Route::post('/generate', [GenericPassController::class, 'generatePass'])
        ->name('api.generic-pass.generate');
    
    // Get pass URL for a specific member
    Route::get('/{memberId}/url', [GenericPassController::class, 'getPassUrl'])
        ->name('api.generic-pass.url');
    
    // Regenerate pass (e.g., after member data update)
    Route::post('/regenerate', [GenericPassController::class, 'regeneratePass'])
        ->name('api.generic-pass.regenerate');
    
    // Revoke/expire a pass
    Route::post('/revoke', [GenericPassController::class, 'revokePass'])
        ->name('api.generic-pass.revoke');
    
    // Check if member has active pass
    Route::get('/{memberId}/check', [GenericPassController::class, 'checkActivePass'])
        ->name('api.generic-pass.check');
    
    // Get all active passes
    Route::get('/active', [GenericPassController::class, 'getAllActivePasses'])
        ->name('api.generic-pass.all-active');
});

// Public redirect route for "Add to Google Wallet" button
Route::get('/google-wallet/generic/{memberId}', [GenericPassController::class, 'downloadGooglePass'])
    ->name('google.wallet.generic.download');

/*
|--------------------------------------------------------------------------
| Usage Examples in Blade
|--------------------------------------------------------------------------
|
| <!-- In your member details page -->
| <a href="{{ route('google.wallet.generic.download', ['memberId' => $member->id]) }}" 
|    class="btn btn-primary">
|     Add to Google Wallet
| </a>
|
| <!-- Or using JavaScript/AJAX -->
| <script>
| async function generatePass(memberId) {
|     const response = await fetch('/api/generic-pass/generate', {
|         method: 'POST',
|         headers: {
|             'Content-Type': 'application/json',
|             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
|         },
|         body: JSON.stringify({ member_id: memberId })
|     });
|     const data = await response.json();
|     if (data.success) {
|         window.open(data.data.pass_url, '_blank');
|     }
| }
| </script>
|
*/
