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
        $permissions = '00000';

        if ($node != null && $policy != 'move') $permissions = $this->getUserNodePermissions($user, $node);

        $user->permissions = $permissions;
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
	 * @param  int              $user_id
	 * @return bool
	 */
	public function update(User $user, Node $node, string $permissions, int $user_id = null)
	{
		return (
            $user->permissions[2] == "1" &&
            $this->permissionsAcceptable($permissions) &&
            $this->userCanUpdateUser($user, $node, $user_id)
        );
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
        // Last check prevents users from deleting root node
		return $user->permissions[3] == "1" && $node->name != '';
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

	/**
	 * Determine whether the user can move a file or directory to another directory
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @return bool
	 */
	public function move(User $user, Directory $destination, Node $child)
	{
        // If they are trying to move "root" stop them
        if ($child->name == '') return false;

        $parent = $child->get_parent();
        $parentPermissions = $this->getUserNodePermissions($user, $parent);
        $destinationPermissions = $this->getUserNodePermissions($user, $destination);
        $childPermissions = $this->getUserNodePermissions($user, $child);

        // This means user can create in destination directory,
        // user can delete in former parent directory
        // and user can update child node
		return $destinationPermissions[0] == "1" && $parentPermissions[3] == "1" && $childPermissions[2] == "1";
	}

    /**
	 * Gets the permissions available to a user for a node
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @return string
	 */
    private function getUserNodePermissions(User $user, Node $node): string
    {
        $permissions = '00000';

        if($permissionObject = $node->user_permissions()->where('user_id', '=', $user->id)->first()) {
            $permissions = $permissionObject->pivot->crudx;
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

        return $permissions;
    }

    /**
	 * Determines if the permissions string is formatted properly
	 *
	 * @param  string   $permissions
	 * @return bool
	 */
    private function permissionsAcceptable(string $permissions): bool
    {
        return preg_match('/\b[1,0]{5}\b/', $permissions) == 1;
    }

    /**
	 * Determines if logged in user can update a node for the passed user
	 *
	 * @param  App\Models\User  $user
	 * @param  App\Models\Node  $node
	 * @param  int              $user_id
	 * @return bool
	 */
    private function userCanUpdateUser(User $user, Node $node, int $user_id = null): bool
    {
        if ($user_id == null) return true;

        $affectedUser = User::findOrFail($user_id);

        return ($user->isAdmin() || !$affectedUser->isAdmin());
    }
}
