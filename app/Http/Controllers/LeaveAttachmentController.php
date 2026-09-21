<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LeaveAttachmentController extends Controller
{
    /**
     * Securely stream a medical certificate or leave attachment.
     * Enforces PDPA Malaysia privacy and hierarchical role-based access control.
     */
    public function show(Request $request, LeaveRequest $leave): Response
    {
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(401, 'Unauthenticated.');
        }

        // Load the applicant and their manager relationship
        $leave->loadMissing('user');

        // RBAC Authorization:
        // 1. Applicant themselves
        // 2. Direct supervisor / assigned manager
        // 3. HR Executives
        // 4. Directors / Super Admins
        $canAccess = $currentUser->isAdmin()
            || $currentUser->isHr()
            || ($currentUser->isManager() && $leave->user?->manager_id === $currentUser->id)
            || $leave->user_id === $currentUser->id;

        if (! $canAccess) {
            abort(403, 'Unauthorized access to medical certificate or supporting evidence.');
        }

        if (empty($leave->attachment_path)) {
            abort(404, 'No attachment recorded for this leave request.');
        }

        $path = $leave->attachment_path;

        // Check private 'local' disk first (new uploads), then fallback to 'public' (legacy records)
        $disk = null;
        if (Storage::disk('local')->exists($path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = 'public';
        } else {
            abort(404, 'The requested attachment file was not found on the server.');
        }

        return Storage::disk($disk)->response($path);
    }
}
