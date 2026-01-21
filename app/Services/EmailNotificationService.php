<?php

namespace App\Services;

use App\Models\Member;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Exception;

class EmailNotificationService
{
    /**
     * Send membership welcome email with wallet passes.
     *
     * @param Member $member
     * @param string|null $applePassUrl
     * @param string|null $googlePassUrl
     * @return bool
     */
    public function sendMembershipEmail(Member $member, ?string $applePassUrl, ?string $googlePassUrl): bool
    {
        try {
            $emailData = [
                'member' => $member,
                'applePassUrl' => $applePassUrl,
                'googlePassUrl' => $googlePassUrl,
                'organizationName' => config('wallet.branding.organization_name'),
                'supportEmail' => config('wallet.branding.support_email'),
            ];

            Mail::send('emails.membership-welcome', $emailData, function ($message) use ($member) {
                $message->to($member->email, $member->full_name)
                    ->subject('Welcome to ' . config('wallet.branding.organization_name') . ' - Your Membership Card');
                $message->from(
                    config('mail.from.address'),
                    config('wallet.branding.organization_name')
                );
            });

            // Log success
            $this->logEmail($member->id, $member->email, 'Membership Welcome Email', 'sent');

            return true;
        } catch (Exception $e) {
            // Log failure
            $this->logEmail($member->id, $member->email, 'Membership Welcome Email', 'failed', $e->getMessage());

            return false;
        }
    }

    /**
     * Send pass regeneration email.
     *
     * @param Member $member
     * @param string|null $applePassUrl
     * @param string|null $googlePassUrl
     * @return bool
     */
    public function sendPassRegenerationEmail(Member $member, ?string $applePassUrl, ?string $googlePassUrl): bool
    {
        try {
            $emailData = [
                'member' => $member,
                'applePassUrl' => $applePassUrl,
                'googlePassUrl' => $googlePassUrl,
                'organizationName' => config('wallet.branding.organization_name'),
                'supportEmail' => config('wallet.branding.support_email'),
            ];

            Mail::send('emails.pass-regeneration', $emailData, function ($message) use ($member) {
                $message->to($member->email, $member->full_name)
                    ->subject('Your Updated Membership Card - ' . config('wallet.branding.organization_name'));
                $message->from(
                    config('mail.from.address'),
                    config('wallet.branding.organization_name')
                );
            });

            // Log success
            $this->logEmail($member->id, $member->email, 'Pass Regeneration Email', 'sent');

            return true;
        } catch (Exception $e) {
            // Log failure
            $this->logEmail($member->id, $member->email, 'Pass Regeneration Email', 'failed', $e->getMessage());

            return false;
        }
    }

    /**
     * Log email to database.
     *
     * @param int|null $memberId
     * @param string $recipientEmail
     * @param string $subject
     * @param string $status
     * @param string|null $errorMessage
     */
    private function logEmail(
        ?int $memberId,
        string $recipientEmail,
        string $subject,
        string $status,
        ?string $errorMessage = null
    ): void {
        EmailLog::create([
            'member_id' => $memberId,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => now(),
        ]);
    }
}
