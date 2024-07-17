<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
class AuthController extends Controller
{


    public function register(Request $request): JsonResponse
    {
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return response()->json(['errors' => 'Email is already exists'], 422);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string',
                'password' => 'required|string',
                'account_number' => 'required|string'
            ]);

            if ($validator->fails()) {

                return response()->json(['errors' => $validator->errors()], 422);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => false, // Set initial status account to false (active account)
                'is_approved' => false, // Set initial status   to false (pending admin approval)
                 'account_number'=>$request->account_number
            ]);

            // Attach role to user
            $role = Role::where('name', 'User')->first();
            $user->roles()->attach($role);
            
            $response = [
                'success' => true,
                'data' => $user,
                'message' => 'User registered successfully ..waiting for the admin approval',
            ];

            return response()->json($response, 200);
        }
    }


    public function login(Request $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if (!$user->is_approved) {
                // Account is pending approval, prevent login and show pending approval message
                return response()->json(['errors' => 'Your account is pending approval. Please wait for admin approval.'], 422);
            } elseif (!$user->is_active) {
                // Account is not active, prevent login
                return response()->json(['errors' => 'Your account is deactivated. Please contact support.'], 422);
            }
            $success['token'] = $user->createToken('auth_token')->plainTextToken;


            $response = [
                'success' => true,
                'data' => $success,
                'message' => ' You have logged in successfully',

            ];

            return response()->json($response, 200);
        } else {

            return response()->json(['errors' => 'Unauthorized'], 401);
        }
    }


    public function logout(Request $request): JsonResponse
    {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json(['message' => 'You have logged out successfully.'], 201);

    }

    public function updateUser(Request $request): JsonResponse
    {
        $user = User::findOrFail(auth()->id());

        Log::info(auth()->id());
        Log::info($user);
        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
            'account_number' => 'sometimes|string',
        ]);

        $updatedFields = 0;

        if ($request->has('name')) {

            $user->name = $request->input('name');

            $updatedFields++;
        } elseif ($request->has('email')) {
            $user->email = $request->input('email');
            $updatedFields++;
        }

        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
            $updatedFields++;
        }
        if ($request->has('account_number')) {
            $user->account_number = $request->input('account_number');
            $updatedFields++;
        }
        if ($updatedFields === 0) {
            return response()->json([
                'message' => 'No fields provided for update. Please provide at least one field (name, email, or phone) for update.',
            ], 400);
        }


        $user->save();

        return response()->json([
            'message' => 'User info updated successfully',
            'user' => $user
        ], 201);
    }


    public function deleteUser(Request $request): JsonResponse
    {
        $user = User::findOrFail(auth()->id());
        if ($user)
        {
            $deleted = DB::table('users')->where('id',auth()->id())->delete();
        }



        return response()->json('Your account has been deleted successfully', 200);
    }

    public function reset(Request $request)
    {
    
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|min:8|string'
        ]);
    
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    
        $user = Auth::user();
        \Log::info('User trying to reset password: ' . $user->id);
    
        // Check if current password matches
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password does not match'], 400);
        }
    
        // Check if current password and new password are the same
        if (strcmp($validatedData['current_password'], $validatedData['new_password']) == 0) {
            return response()->json(['message' => 'New password cannot be the same as your current password'], 400);
        }
    
        // Update the password
        $user->password = Hash::make($validatedData['new_password']);
       
        $user->save();
    
        return response()->json(['success' => 'Password changed successfully'], 200);
    }
    


    public function showUserProfile(Request $request){
        
        $request->validate(['id'=>'nullable']);
       
   
        if($request->has('id')){
            $user=User::findOrFail($request->id);
            return response()->json($user, 200);
        
        }
       
       else
        {
            $user=User::find(auth()->id());
            return response()->json($user, 200);
        }
       
}}
