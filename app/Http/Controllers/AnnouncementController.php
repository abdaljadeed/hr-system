<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Announcement\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', Announcement::class);

        $announcements = Announcement::with('publisher')->latest()->paginate(15);

        return view('announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('announcements.create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->authorize('create', Announcement::class);

        $this->service->create($request->validated(), auth()->user());

        return redirect()->route('announcements.index')->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        return view('announcements.edit', compact('announcement'));
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $this->service->update($announcement, $request->validated(), auth()->user());

        return redirect()->route('announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $this->service->deactivate($announcement, auth()->user());

        return redirect()->route('announcements.index')->with('success', 'Announcement deactivated.');
    }

    public function dismiss(Announcement $announcement): RedirectResponse
    {
        $dismissed = session('dismissed_announcements', []);
        $dismissed[] = $announcement->id;
        session(['dismissed_announcements' => array_values(array_unique($dismissed))]);

        return back();
    }
}
