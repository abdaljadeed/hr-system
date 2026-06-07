<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\Dashboard\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->hasAnyRole(['Admin', 'HR Manager', 'Team Lead']);
        $isHrManager = $user->hasAnyRole(['Admin', 'HR Manager']);
        $dismissed = session('dismissed_announcements', []);

        return view('dashboard', [
            'isManager' => $isManager,
            'showCelebrations' => $isHrManager,
            'stats' => $this->dashboard->getStats(),
            'attendanceTrend' => $this->dashboard->getAttendanceTrend(),
            'tasksByStatus' => $this->dashboard->getTasksByStatus(),
            'leavesByType' => $this->dashboard->getLeavesByType(),
            'topEmployees' => $isManager ? $this->dashboard->getTopEmployees() : collect(),
            'birthdays' => $isHrManager ? $this->dashboard->getBirthdaysThisMonth() : collect(),
            'anniversaries' => $isHrManager ? $this->dashboard->getAnniversariesThisMonth() : collect(),
            'announcements' => Announcement::active()->whereNotIn('id', $dismissed)->latest()->get(),
        ]);
    }
}
