<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemberService
{
    /**
     * Create a new member.
     *
     * @param array $data
     * @return Member
     * @throws ValidationException
     */
    public function createMember(array $data): Member
    {
        // Validate data
        $validatedData = $this->validateMemberData($data);

        // Format mobile number
        $validatedData['mobile'] = Member::formatMobileNumber($validatedData['mobile']);

        // Generate unique member ID
        $validatedData['unique_member_id'] = Member::generateUniqueMemberId();

        // Create member
        return Member::create($validatedData);
    }

    /**
     * Update member information.
     *
     * @param int $memberId
     * @param array $data
     * @return Member
     * @throws ValidationException
     */
    public function updateMember(int $memberId, array $data): Member
    {
        $member = Member::findOrFail($memberId);

        // Validate data (excluding email if it's the same)
        $rules = $this->getValidationRules($memberId);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Format mobile number if provided
        if (isset($data['mobile'])) {
            $data['mobile'] = Member::formatMobileNumber($data['mobile']);
        }

        // Update member
        $member->update($data);

        return $member->fresh();
    }

    /**
     * Validate member data.
     *
     * @param array $data
     * @param int|null $memberId
     * @return array
     * @throws ValidationException
     */
    private function validateMemberData(array $data, ?int $memberId = null): array
    {
        $rules = $this->getValidationRules($memberId);
        
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get validation rules.
     *
     * @param int|null $memberId
     * @return array
     */
    private function getValidationRules(?int $memberId = null): array
    {
        $emailRule = 'required|email|max:255|unique:members,email';
        
        if ($memberId) {
            $emailRule .= ',' . $memberId;
        }

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => $emailRule,
            'mobile' => 'required|string|max:20',
            'status' => 'sometimes|in:active,inactive,deleted',
        ];
    }

    /**
     * Delete member (soft delete by setting status).
     *
     * @param int $memberId
     * @return bool
     */
    public function deleteMember(int $memberId): bool
    {
        $member = Member::with('walletPass')->findOrFail($memberId);
        
        // Clean up Apple Wallet pass file if exists
        if ($member->walletPass && $member->walletPass->apple_pass_path) {
            if (file_exists($member->walletPass->apple_pass_path)) {
                @unlink($member->walletPass->apple_pass_path);
            }
        }

        return $member->delete();
    }

    /**
     * Get member statistics.
     *
     * @return array
     */
    public function getMemberStatistics(): array
    {
        return [
            'total_members' => Member::where('status', '!=', 'deleted')->count(),
            'active_members' => Member::where('status', 'active')->count(),
            'inactive_members' => Member::where('status', 'inactive')->count(),
            'today_registrations' => Member::whereDate('created_at', today())->count(),
            'this_week_registrations' => Member::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'this_month_registrations' => Member::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }
}
