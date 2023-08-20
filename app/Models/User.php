<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

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
	public function groups()
	{
		return $this->belongsToMany('App\Models\Group', 'user_groups', 'user_id', 'group_id');
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
