<?php

namespace App\Policies;

use App\Models\File;
use App\Models\Directory;
use App\Models\Node;
use App\Models\User;

class NodePolicy
{
    /**
	 * Modifies the code to make it easier to check if a user has the associated CRUDX policy
	 *
	 * @param  App\Models\User  $user
	 * @param  string           $policy
	 * @param  App\Models\Node  $node
	 * @return bool
	 */
	public function before(User $user, string $policy, Node $node)
	{
        if ($node != null) {
            $permissions = '00000';

            if($permissionObject = $node->current_user_permissions->first()) {
                $permissions = $permissionObject->permissions;
            } else if ($user->isAdmin()) {
                $permissions = '11111';
            } else if (
                $sharedGroup = $user->groups->whereIn('id', $node->groups->pluck('id')->toArray())
                    ->unique('id')
                    ->sortBy('weight')->first()
            )
            {
                $permissions = $sharedGroup->permissions;
            }

            $user->permissions = $permissions;
        }
	}

	/**
	 * Determine whether the user can create a node in this directory
	 *
	 * @param  App\Models\User      $user
	 * @param  App\Models\Node      $node
	 * @return bool
	 */
	public function create(User $user, Node $node)
	{
		return $user->permissions[0] == "1";
	}

	/**
	 * Determine whether the user can read a node
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @return bool
	 */
	public function read(User $user, Node $node)
	{
        return $user->permissions[1] == "1";
	}

	/**
	 * Determine whether the user can update a node's permissions
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @return bool
	 */
	public function update(User $user, Node $node)
	{
		return $user->permissions[2] == "1";
	}

	/**
	 * Determine whether the user can delete a node
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @return bool
	 */
	public function delete(User $user, Node $node)
	{
		return $user->permissions[3] == "1";
	}

	/**
	 * Determine whether the user can execute a file
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @return bool
	 */
	public function execute(User $user, Node $node)
	{
		return $user->permissions[4] == "1";
	}
}
