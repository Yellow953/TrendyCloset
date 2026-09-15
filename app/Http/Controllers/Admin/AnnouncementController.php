<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

/**
 * The rotating pre-header bar above the main nav on every storefront page.
 */
class AnnouncementController extends Controller
{
    public function index()
    {
        return view('admin.announcements.index', [
            'active' => 'announcements',
            'announcements' => Announcement::query()->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.announcements.form', [
            'active' => 'announcements',
            'announcement' => new Announcement([
                'is_active' => true,
                'position' => (int) Announcement::query()->max('position') + 1,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $announcement = Announcement::create($this->validated($request));

        return redirect()
            ->route('admin.announcements.edit', $announcement)
            ->with('status', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.form', [
            'active' => 'announcements',
            'announcement' => $announcement,
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validated($request));

        return back()->with('status', 'Announcement saved.');
    }

    /** Publish / unpublish from the list, without opening the announcement. */
    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('status', $announcement->is_active ? 'Announcement is live.' : 'Announcement hidden.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Announcement deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:200'],
            'link_label' => ['nullable', 'string', 'max:40'],
            'link_url' => ['nullable', 'string', 'max:2048'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['position'] = $data['position'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
