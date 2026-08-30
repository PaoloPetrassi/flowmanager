<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('assets.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('assets.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.update');
    }

    public function assign(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.assign');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.delete');
    }
}
