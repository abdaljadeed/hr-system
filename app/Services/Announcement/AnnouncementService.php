<?php

namespace App\Services\Announcement;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementService
{
    public function create(array $data, User $publisher): Announcement
    {
        $announcement = Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
            'published_by' => $publisher->id,
        ]);

        activity()->causedBy($publisher)->performedOn($announcement)
            ->log("Published announcement '{$announcement->title}'");

        return $announcement;
    }

    public function update(Announcement $announcement, array $data, User $editor): Announcement
    {
        $announcement->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $announcement->is_active),
        ]);

        activity()->causedBy($editor)->performedOn($announcement)
            ->log("Updated announcement '{$announcement->title}'");

        return $announcement;
    }

    public function deactivate(Announcement $announcement, User $actor): Announcement
    {
        $announcement->update(['is_active' => false]);

        activity()->causedBy($actor)->performedOn($announcement)
            ->log("Deactivated announcement '{$announcement->title}'");

        return $announcement;
    }
}
