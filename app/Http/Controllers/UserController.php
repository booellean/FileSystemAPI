<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\User;

use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function getUsers()
    {
        return response()->json([
            'users' => UserResource::collection(User::all())
        ], 200);
    }
}
