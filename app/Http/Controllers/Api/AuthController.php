<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Traits\apiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use apiResponse;
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = auth('api')->login($user);
        $data = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->apiResponse(200, 'Register successful', null, $data);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (auth('api')->attempt($credentials)){
            $user = auth('api')->user();
            $token = auth('api')->login($user);
            $data = [
                'user' => $user,
                'token' => $token,
            ];
            return $this->apiResponse(200, 'Login successful', null, $data);
        }
        return $this->apiResponse(401, 'Invalid credentials');
    }

    public function logout()
    {
        auth()->guard('api')->logout();
        auth('api')->invalidate(true);
        return $this->apiResponse(200, 'Logout successful');
    }
}
