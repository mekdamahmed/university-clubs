<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        try {
            // تحقق صارم
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            // إنشاء المستخدم في الداتا بيز
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => false 
            ]);

            // إنشاء التوكن
            $token = $user->createToken('auth_token')->plainTextToken;

            // إرجاع الرد
            return $this->successResponse([
                'user' => $user,
                'token' => $token
            ], 'Account created successfully', 201);

        } catch (ValidationException $e) {
            return $this->errorResponse($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Database Error: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid Email or Password', 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token
            ], 'Login successful', 200);

        } catch (ValidationException $e) {
            return $this->errorResponse($e->validator->errors()->first(), 422);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logged out successfully', 200);
    }
}