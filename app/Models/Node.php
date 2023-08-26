<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use App\Models\Group;
use App\Models\User;

abstract class Node extends Model
{
    protected $primaryKey = 'id';

    protected $with = ['current_user_permissions', 'groups'];

    /**
	 * --------------------------------------------------------------------------
	 *  Eloquent Model Relationships
	 * --------------------------------------------------------------------------
	*/

	/**
	 * The groups of the file or directory
	 */
	public function groups(): MorphToMany
	{
		return $this->morphToMany(Group::class, 'groupable');
	}

    /**
	 * The custom permissions set to users of this file or directory
	 */
	public function user_permissions(): MorphToMany
	{
		return $this->morphToMany(User::class, 'permissions');
	}

    /**
	 * The custom permissions set to users of this file or directory
	 */
	public function current_user_permissions()
	{
        $user = request()->user();
		return $this->user_permissions()->where('user_id', '=', ($user != null ? $user->id : '0'));
	}

    /**
	 * --------------------------------------------------------------------------
	 *  Accessors
	 * --------------------------------------------------------------------------
	*/

    public function get_parent()
    {
        // TODO: Do we want the root parent to be the parent?
        // if ($this->name == '') return null;

        $directoryParts = explode('/', $this->name);
        array_pop($directoryParts);
        $parent_name = implode('/', $directoryParts);

        return self::where('name', '=', $parent_name)->first();
    }

    abstract public function read_node();

}
