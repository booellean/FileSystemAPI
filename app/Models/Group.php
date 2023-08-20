<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
	/**
	 * The table of the model
	 * @var string
	 */
	protected $table = 'groups';

	/**
	 * The name of the primary key
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 * @var string[]
	 */
	protected $fillable = ['weight', 'permissions'];


    /**
	 * --------------------------------------------------------------------------
	 *  Eloquent Model Relationships
	 * --------------------------------------------------------------------------
	*/

	/**
	 * The users that belong to the group
	 */
	public function users()
	{
		return $this->belongsToMany('App\Models\User', 'user_groups', 'group_id', 'user_id');
	}

    /**
	 * The files and directories that belong to the group
	 */
	public function nodes()
	{
		return $this->belongsToMany('App\Models\Node', 'node_groups', 'group_id', 'node_id');
	}
}
