<?php

namespace App\Http\Controllers;
use App\Models\Investment;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Role;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
//use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;



use App\Mail\ResetPasswordMail;
class AdminUserController extends Controller
{
    public function showInvestors(Request $request)
    {
        // Get the role IDs for 'user' and 'admin'
        $userRoleId = Role::where('name', 'User')->value('id');

        $adminRoleId = Role::where('name', 'Admin')->value('id');

        // Get users with 'user' role and not 'admin' role
        $users = User::whereHas('roles', function($query) use ($userRoleId) {
            $query->where('role_id', $userRoleId);
        })->whereDoesntHave('roles', function($query) use ($adminRoleId) {
            $query->where('role_id', $adminRoleId);
        })->get();
        


        $result = [];
    $totalProfits=0;

        foreach ($users as $user) {
            \Log::info('User ID: ' . $user->id . ' has roles: ' . json_encode($user->roles->pluck('name')));

            $investmentsCount = $user->investments()->count();

      
            $balance = $user->wallet ? $user->wallet->balance : 0;
            $investments = $user->investments()->get();
            foreach ($investments as $investment) {
          
        
             
                  $totalProfits+=$investment->calculateROI();
              }
    
      
            
              $result[] = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'account_number' => $user->account_number,
                ],
                'investments_count' => $investmentsCount,
                'balance' => $balance,
                'total_profits' => $totalProfits,
            ];
        }
    
          


        return response()->json($result);
    }
   
    public function assignRole(\Illuminate\Http\Request $request, $userId): JsonResponse
    {
        Log::info("userId" . $userId);

        Log::info("role_id" . $request->role_id);
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);


        //Find the user:
        $user = User::findOrFail($userId);

        //Asign the role:
        $role = Role::findOrFail($request->role_id);
        $user->roles()->sync([
            $role->id
        ]);
        return response()->json('Role assigned successfully');

    }

    public function approveAccountAndActiveIt(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->is_approved = true;
        $user->is_active = true;
        $user->wallet()->create([
                 'user_id' => $userId,
                 'balance' =>0.00
             ]);


        $user->save();
        return response()->json('Account activated successfully');
    }

    public function createInvestor(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            Log::info('Request data:', $request->all());

            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string',
                'account_number' => 'required|string'
            
            ]);

            // Generate a random password
            $password = Str::random(10);

            // Create the investor
            $investor = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'password' => Hash::make($password),
                'is_active' => true,
                'is_approved' => true,
                'account_number'=>$validatedData['account_number']
                
            ]);
                Log::info( $investor);
             // Attach role to user
            $role = Role::where('name', 'User')->first();
            $investor->roles()->attach($role);
//            // Send email with reset password link
//            if ($investor) {
//                Mail::to($investor->email)->send(new ResetPasswordMail($password));
//            }

            return response()->json(['message' => 'Investor added successfully. An email with reset password link has been sent.']);


        } catch (\Exception $e) {
            // Log any exceptions that occur during execution
            Log::error('Exception occurred: ' . $e->getMessage());
            // Return an error response
            return response()->json(['error' => 'An error occurred. Please try again later.'], 500);
        }
    }


    public function updateUserByAdmin(Request $request, $id): JsonResponse
    {
//        Log::info('Request input: ',$id);
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
        ]);

        $updatedFields = 0;

        if ($request->has('name')) {
            $user->name = $request->input('name');
            $updatedFields++;
        }

        if ($request->has('email')) {
            $user->email = $request->input('email');
            $updatedFields++;
        }

        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
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


    public function deleteUserByAdmin(Request $request, $id): JsonResponse
    { Log::info('User deleted', ['user_id' => $id]);
        $user = User::findOrFail($id);
        if ($user)
        {
            $deleted = DB::table('users')->where('id', $id)->delete();
        }

        return response()->json('Your account has been deleted successfully', 200);

    }
}
