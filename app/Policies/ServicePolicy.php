<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        // Everyone can view active services (public catalogue)
        return true;
    }

    public function view(User $user, Service $service): bool
    {
        // Public can view active services
        if (!$service->is_active && !$user->isStaff()) {
            return false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->isAdmin();
    }
}
