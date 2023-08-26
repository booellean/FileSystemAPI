<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use App\Models\Directory;
use App\Models\File;
use App\Models\User;

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
	public function users(): MorphToMany
	{
        return $this->morphedByMany(User::class, 'groupable');
    }

    /**
	 * The files that belong to the group
	 */
	public function files(): MorphToMany
	{
		return $this->morphedByMany(File::class, 'groupable');
	}

    /**
	 * The directories that belong to the group
	 */
	public function directories(): MorphToMany
	{
		return $this->morphedByMany(Directory::class, 'groupable');
	}
}
