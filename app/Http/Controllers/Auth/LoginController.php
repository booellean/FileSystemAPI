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
        $device_name = 'local';

        if (!$user = User::where('name', '=', $request->name)->first()) {
            return response()->json([
                'message' => 'User was not found.'
            ], 404);
        }

        if ($user->password) {
            if (!$request->password) {
                return response()->json([
                    'message' => 'This user requires a password.'
                ], 405);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'The password was incorret.'
                ], 405);
            }
        }

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
