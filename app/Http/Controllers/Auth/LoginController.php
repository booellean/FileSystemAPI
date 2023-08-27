<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

use App\Models\User;

class LoginController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function login(Request $request)
	{
        // TODO: When the mobile application uses the token to make an API request to your application, it should pass the token in the Authorization header as a Bearer token.
        // TODO: get device name somehow for token name
        // dd($request);
        $device_name = 'test';
        $user = User::where('name', '=', $request->name)->first();

        // // TODO: check if a password is required for this user and validate
        // if (! $user || ! Hash::check($request->password, $user->password)) {
        //     throw ValidationException::withMessages([
        //         'name' => ['The provided credentials are incorrect.'],
        //     ]);
        // }

        return $user->createToken($device_name)->plainTextToken;
	}

    public function logout(Request $request)
    {
        if ($user = $request->user()) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'User was successfully logged out.'
            ], 200);
        }

        return response()->json([
            'message' => 'There were no logged in users.'
        ], 200);
    }
}
