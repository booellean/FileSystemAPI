<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Directory;
use App\Models\File;
use App\Models\Group;

class User extends Authenticatable
{
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    protected $with = ['groups'];

    /**
	 * --------------------------------------------------------------------------
	 *  Eloquent Model Relationships
	 * --------------------------------------------------------------------------
	*/

	/**
	 * The groups that belong to the user
	 */
	public function groups(): MorphToMany
	{
		return $this->morphToMany(Group::class, 'groupable');
	}

    /**
	 * The custom permissions set to files assigned to this user
	 */
	public function file_permissions(): MorphToMany
	{
        return $this->morphedByMany(File::class, 'permissions');
    }

    /**
	 * The custom permissions set to directories assigned to this user
	 */
	public function directory_permissions(): MorphToMany
	{
        return $this->morphedByMany(Directory::class, 'permissions');
    }

    /**
	 * --------------------------------------------------------------------------
	 *  Accessors
	 * --------------------------------------------------------------------------
	*/

	/**
	 * Determine if a user is an admin
	 */
	public function isAdmin()
	{
		return ($this->groups()->where('name', '=', 'root')->first() != null ? true : false);
	}
}
