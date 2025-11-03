<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any projects
     */
    public function viewAny(User $user): bool
    {
        // Alle geauthenticeerde gebruikers kunnen projects zien
        return true;
    }

    /**
     * Determine whether the user can view the project
     */
    public function view(User $user, Project $project): bool
    {
        // Super admin kan alles zien
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admins kunnen alle projecten in hun company zien
        if ($user->role === 'admin' && $user->company_id === $project->customer->company_id) {
            return true;
        }

        // Check of user is toegewezen aan het project
        return $project->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create projects
     */
    public function create(User $user): bool
    {
        // Alleen super_admin, admin en project_manager kunnen projecten aanmaken
        return in_array($user->role, ['super_admin', 'admin', 'project_manager']);
    }

    /**
     * Determine whether the user can update the project
     */
    public function update(User $user, Project $project): bool
    {
        // Super admin kan alles bewerken
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admins kunnen projecten in hun company bewerken
        if ($user->role === 'admin' && $user->company_id === $project->customer->company_id) {
            return true;
        }

        // Project managers kunnen projecten bewerken waar ze aan toegewezen zijn
        if ($user->role === 'project_manager') {
            return $project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the project
     */
    public function delete(User $user, Project $project): bool
    {
        // Alleen super_admin en admin kunnen projecten verwijderen
        if ($user->role === 'super_admin') {
            return true;
        }

        if ($user->role === 'admin' && $user->company_id === $project->customer->company_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can manage financial aspects
     */
    public function manageFinancials(User $user, Project $project): bool
    {
        // Alleen super_admin en admin kunnen financiële aspecten beheren
        return in_array($user->role, ['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can manage team members
     */
    public function manageTeam(User $user, Project $project): bool
    {
        // Super admin, admin en project managers kunnen team beheren
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return true;
        }

        if ($user->role === 'project_manager') {
            return $project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can log time
     */
    public function logTime(User $user, Project $project): bool
    {
        // Super admin kan altijd
        if ($user->role === 'super_admin') {
            return true;
        }

        // Check of user is toegewezen aan het project
        return $project->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can approve time entries
     */
    public function approveTime(User $user, Project $project): bool
    {
        // Alleen super_admin, admin en project_manager kunnen tijd goedkeuren
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return true;
        }

        if ($user->role === 'project_manager') {
            return $project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view project finances
     */
    public function viewFinances(User $user, Project $project): bool
    {
        // Super admin en admin kunnen altijd financiën zien
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return true;
        }

        // Project managers kunnen financiën zien van hun projecten
        if ($user->role === 'project_manager') {
            return $project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can change project status
     */
    public function changeStatus(User $user, Project $project): bool
    {
        // Super admin, admin en project managers kunnen status wijzigen
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return true;
        }

        if ($user->role === 'project_manager') {
            return $project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}