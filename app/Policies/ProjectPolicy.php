<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isProjectManager() || $user->isAdmin();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Project managers can view all projects
        if ($user->isProjectManager()) {
            return true;
        }

        // Customers can only view their own projects
        if ($user->isCustomer()) {
            return $project->customer_id === $user->id;
        }

        // Freelancers/employees can view projects they're assigned to
        if ($user->isFreelancer() || $user->isEmployee()) {
            return $project->members()->where('users.id', $user->id)->exists()
                || $project->project_manager_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isProjectManager() || $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isProjectManager()) {
            return $project->project_manager_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function updateStatus(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isProjectManager()) {
            return $project->project_manager_id === $user->id;
        }

        return false;
    }
}
