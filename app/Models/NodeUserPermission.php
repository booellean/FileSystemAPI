<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NodeUserPermission extends Model
{
	/**
	 * The table of the model
	 * @var string
	 */
	protected $table = 'node_user_permissions';

	/**
	 * The name of the primary key
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 * @var string[]
	 */
	protected $fillable = ['node_id', 'user_id', 'permissions'];
}
