<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => \App\Support\PersonName::rules(255),
            'last_name'  => \App\Support\PersonName::rules(255),
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
        ], [
            'first_name.regex' => \App\Support\PersonName::messages('First name')['regex'],
            'last_name.regex' => \App\Support\PersonName::messages('Last name')['regex'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => 'Validation Error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'first_name' => \App\Support\PersonName::normalize($request->first_name),
            'last_name'  => \App\Support\PersonName::normalize($request->last_name),
            'department' => 'General',
            'designation'=> 'Employee',
            'email'      => $request->email,
            'role'       => 'employee',
            'manager_id' => null,
            'is_admin'   => 0,
            'is_active'  => 1,
            'password'   => Hash::make($request->password),
        ]);

        $response = [
            'token' => $user->createToken('API Token')->accessToken,
            'user'  => $user->first_name,
            'email' => $user->email,
        ];

        return response()->json([
            'status'  => 1,
            'message' => 'User registered successfully',
            'data'    => $response,
        ], 201);
    }

    public function login(Request $request){
            if(Auth::attempt(["email"=> $request->email ,"password" => $request ->password])){
            $user = Auth::user();

            $response = [];
            $response['token'] = $user->createToken("API Token")->accessToken;
            $response['email'] = $user ->name;
            $response['user'] = $user ->email;
               
             return response()->json([
            'status'  => 1,
            'message' => 'User registered successfully',
            'data'    => $response,
            ], 201);


            }

        return response()->json([
            'status'  => 0,
            'message' => 'User unauthentication',
            'data'    => null,
        ], 201);
    }
     #check API health status and database connectivity
    public function check(Request $request)
    {
        try {
            // Basic DB connectivity check
            DB::connection()->getPdo();

            return response()->json([
                'status'  => 1,
                'message' => 'API is healthy',
                'details' => [
                    'app'       => config('app.name'),
                    'env'       => config('app.env'),
                    'db_status' => 'connected',
                    'time'      => now()->toDateTimeString(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'API unhealthy',
                'details' => [
                    'error' => $e->getMessage(),
                    'time'  => now()->toDateTimeString(),
                ],
            ], 500);
        }
    }
}