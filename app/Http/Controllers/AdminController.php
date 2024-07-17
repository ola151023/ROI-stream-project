<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// app/Http/Controllers/AdminController.php




use App\Models\User;


class AdminController extends Controller
{
    public function pendingAccounts()
    {
        $pendingAccounts = User::where('is_approved', false)->get();
        return response()->json($pendingAccounts);
    }



    public function rejectAccount(Request $request, $userId)
    {

        $user = User::findOrFail($userId);

        $user->delete();
        return response()->json('Account rejected successfully');
    }
    public function deactivateAccountByAdmin(Request $request, $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $user->is_active = false;
        $user->save();
        return response()->json('Account deactivated successfully');
    }
    public function activateAccountByAdmin(Request $request, $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $user->is_active = true;
        $user->save();
        return response()->json('Account activated successfully');
    }
}
