<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class AnnouncementController extends Controller
{
    public function manage(Request $request): View
    {
        $announcements = Announcement::query()
            ->latest()
            ->get();

        $editAnnouncementId = (int) $request->query('edit', 0);

        $editingAnnouncement = $editAnnouncementId > 0
            ? Announcement::query()->find($editAnnouncementId)
            : null;

        return view('admin.announcements.index', compact('announcements', 'editingAnnouncement'));
    }

    public function facultyIndex(): View
    {
        $announcements = Announcement::query()
            ->visibleToAudience('faculty')
            ->latest()
            ->get();

        return view('faculty.announcements.index', compact('announcements'));
    }

    public function studentIndex(): View
    {
        $announcements = Announcement::query()
            ->visibleToAudience('student')
            ->latest()
            ->get();

        return view('student.announcements.index', compact('announcements'));
    }

    public function index(Request $request): JsonResponse
    {
        $audience = strtolower(trim((string) $request->query('audience', '')));

        if (! in_array($audience, ['all', 'faculty', 'student'], true)) {
            $role = strtolower((string) $request->user()?->role);
            $audience = in_array($role, ['faculty', 'student'], true) ? $role : 'all';
        }

        $announcements = Announcement::query()
            ->visibleToAudience($audience)
            ->latest()
            ->get();

        return response()->json([
            'data' => $announcements,
        ]);
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        try {
            $attachmentPath = $request->file('attachment')?->store('announcements', 'public');

            $announcement = Announcement::query()->create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'audience' => $validated['audience'],
                'attachment_path' => $attachmentPath,
            ]);

            $announcement->created_at = Carbon::parse((string) $validated['announcement_date'])
                ->setTimeFromTimeString(now()->format('H:i:s'));
            $announcement->save();
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse($request, 'Failed to create announcement. Please try again.');
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'message' => 'Announcement created successfully.',
                'data' => $announcement,
            ], 201);
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Announcement created successfully.');
    }

    public function update(
        UpdateAnnouncementRequest $request,
        Announcement $announcement
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();

        try {
            if ($request->boolean('remove_attachment') && $announcement->attachment_path !== null) {
                Storage::disk('public')->delete($announcement->attachment_path);
                $announcement->attachment_path = null;
            }

            if ($request->hasFile('attachment')) {
                if ($announcement->attachment_path !== null) {
                    Storage::disk('public')->delete($announcement->attachment_path);
                }

                $announcement->attachment_path = $request->file('attachment')?->store('announcements', 'public');
            }

            $announcement->title = $validated['title'];
            $announcement->content = $validated['content'];
            $announcement->audience = $validated['audience'];
            $announcement->created_at = Carbon::parse((string) $validated['announcement_date'])
                ->setTimeFromTimeString($announcement->created_at?->format('H:i:s') ?? now()->format('H:i:s'));
            $announcement->save();
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse($request, 'Failed to update announcement. Please try again.');
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'message' => 'Announcement updated successfully.',
                'data' => $announcement,
            ]);
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): JsonResponse|RedirectResponse
    {
        try {
            if ($announcement->attachment_path !== null) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }

            $announcement->delete();
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse($request, 'Failed to delete announcement. Please try again.');
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'message' => 'Announcement deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Announcement deleted successfully.');
    }

    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'message' => $message,
            ], 500);
        }

        return back()
            ->withInput()
            ->withErrors(['announcement' => $message]);
    }
}
