<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|max:25',
            'email' => 'required|email|unique:students|unique:guardians',
            'password' => 'required|confirmed',
            'phoneNumber' => 'required|max:11',
            'role' => 'required|in:student,guardian', // Expect either 'student' or 'parent'
        ]);
    
        if ($fields['role'] === 'student') {
            $user = Student::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
                'phoneNumber' => $fields['phoneNumber'],
            ]);
        } else {
            $user = Guardian::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
                'phoneNumber' => $fields['phoneNumber'],
            ]);
        }
    
        $token = $user->createToken($fields['name'])->plainTextToken;
    
        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }


    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:student,guardian', // Expect either 'student' or 'parent'
        ]);
    
        // Check if the role is 'student' or 'parent' and attempt to find the user
        if ($fields['role'] === 'student') {
            $user = Student::where('email', $fields['email'])->first();
        } else {
            $user = Guardian::where('email', $fields['email'])->first();
        }
    
        // Check if the user exists and the password matches
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }
    
        // Create a token for the user
        $token = $user->createToken($user->name)->plainTextToken;
    
        return response([
            'user' => $user,
            'token' => $token
        ], 200);
    }
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->tokens()->delete();
    
        return response([
            'message' => 'Logged out successfully'
        ], 200);
    }
    
}
