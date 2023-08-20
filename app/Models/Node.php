<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Node extends Model
{
	/**
	 * The table of the model
	 * @var string
	 */
	protected $table = 'nodes';

	/**
	 * The name of the primary key
	 * @var string
	 */
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
	public function groups()
	{
		return $this->belongsToMany('App\Models\Group', 'node_groups', 'node_id', 'group_id');
	}

    /**
	 * The custom permissions set to users of this file or directory
	 */
	public function user_permissions()
	{
		return $this->hasMany('App\Models\NodeUserPermission', 'node_id', 'id');
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

    // public function get_children()
    // {
    //     Storage::disk('root')
    //     return self::where('name', '=', $parent_name)->first();
    // }
}
