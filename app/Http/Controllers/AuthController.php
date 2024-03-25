<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;

            return response()->json([
                'message' => 'Login success',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'token' => $token
                ]
            ], 200);
        }
        return response()->json([
            'message' => 'Email or password incorrect'
        ], 401);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        $user = new User();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'message' => 'Success',
            'data' => $user,
            'token' => $token,
        ]);
    }
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
            return response()->json([
                'message' => 'Logout success'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }
    }
    public function redirectlogin()
    {

        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }
}
