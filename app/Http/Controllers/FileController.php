<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Announcement;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    public function show(Request $request, $type, $id)
    {
        switch ($type) {
            case 'profile_image':
                $user = User::findOrFail($id);
                if (!$user->profile_image_blob) abort(404);
                return Response::make($user->profile_image_blob, 200, [
                    'Content-Type' => $user->profile_image_mime ?? 'image/jpeg',
                    'Content-Disposition' => 'inline'
                ]);

            case 'receipt_proof':
                $user = User::findOrFail($id);
                if (!$user->receipt_proof_blob) abort(404);
                return Response::make($user->receipt_proof_blob, 200, [
                    'Content-Type' => $user->receipt_proof_mime ?? 'image/jpeg',
                    'Content-Disposition' => 'inline'
                ]);

            case 'student_id_proof':
                $user = User::findOrFail($id);
                if (!$user->student_id_proof_blob) abort(404);
                return Response::make($user->student_id_proof_blob, 200, [
                    'Content-Type' => $user->student_id_proof_mime ?? 'image/jpeg',
                    'Content-Disposition' => 'inline'
                ]);

            case 'announcement_attachment':
                $announcement = Announcement::findOrFail($id);
                if (!$announcement->attachment_blob) abort(404);
                return Response::make($announcement->attachment_blob, 200, [
                    'Content-Type' => $announcement->attachment_mime ?? 'application/octet-stream',
                    'Content-Disposition' => 'attachment; filename="' . ($announcement->attachment_filename ?? 'attachment') . '"'
                ]);

            default:
                abort(404);
        }
    }
}
