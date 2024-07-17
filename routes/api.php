<?php


use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvestmentCycleController;
use App\Http\Controllers\InvestmentGroupingController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;



///////all api 's related with authenticated users (admin-investor)

Route::prefix('investor')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->prefix('UserProfile')->group(function () {
    Route::get('/show-profile', [AuthController::class,'showUserProfile']);

    Route::put('/user/update-profile', [AuthController::class, 'updateUser']);
    Route::put('/user/change-password', [AuthController::class, 'reset']);
    Route::delete('/ user/delete-account', [AuthController::class, 'deleteUser']);

});
Route::middleware(['auth:sanctum'])->prefix('investment')->group(function () {
    Route::post('/investment', [InvestmentController::class, 'store']);
    Route::get('/investments', [InvestmentController::class, 'index']);
    Route::get('/investment/{id}', [InvestmentController::class, 'show']);
    Route::delete('/investment', [InvestmentController::class, 'destroy']);

    Route::put('/{id}/request-renewal', [InvestmentController::class, 'requestRenewal']);
    Route::put('/{id}/request-closing', [InvestmentController::class, 'requestClosing']);
    Route::put('/{investment}/request-withdraw-profits', [WithdrawalController::class, 'requestWithdrawalProfits']);
   
});


Route::middleware(['auth:sanctum'])->prefix('InvestorHome')->group(function () {

    Route::get('/account-number', [HomeController::class, 'getAccountNumber']);
    Route::post('/balance', [HomeController::class, 'getBalance']);
    Route::get('/roi-details', [HomeController::class, 'getROIDetails']);
    Route::get('/investments-list', [HomeController::class, 'investmentsList']);
    Route::get('/total-roi', [HomeController::class, 'calculateTotalROI']);
    Route::get('/accumulated-profit', [HomeController::class, 'getAccumulatedProfitPerDeposit']);
    Route::post('/deposit', [DepositController::class, 'depositTransction']);
    // Route::post('/withdraw', [WithdrawalController::class, 'withdrawTransction']);
   
});


///////all api 's related with admin


Route::middleware(['auth:sanctum', 'admin'])->prefix('managing-roles')->group(function () {

    // Existing admin route for assigning roles
    Route::post('/admin/users/{userId}/assign-role', [AdminUserController::class, 'assignRole']);
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('managing-investors')->group(function () {

    // New admin routes for pending accounts, approving accounts, and rejecting accounts
    Route::get('/pending-accounts', [AdminController::class, 'pendingAccounts']);
    Route::put('/approve-account/{userId}', [AdminUserController::class, 'approveAccountAndActiveIt']);
    Route::put('/reject-account/{userId}', [AdminController::class, 'rejectAccount']);
    //deactivate account
    Route::put('/deactivate-account/{userId}', [AdminController::class, 'deactivateAccountByAdmin']);
    //activate account
    Route::put('/activate-account/{userId}', [AdminController::class, 'activateAccountByAdmin']);

    //update user status
    Route::put('/admin/update-user/{id}', [AdminUserController::class, 'updateUserByAdmin']);
    Route::delete('/admin/delete-user/{id}', [AdminUserController::class, 'deleteUserByAdmin']);
    Route::post('/createInvestor', [AdminUserController::class, 'createInvestor']);
    
    Route::get('/show-investors', [AdminUserController::class,'showInvestors']);
    
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('managing-investments')->group(function () {

    Route::resource('investment', InvestmentController::class);
    Route::get('all-investments/{id}',[InvestmentController::class, 'allUserInvestments']);
  
    /// can set any attribute related to investment
    Route::put('update-investment/{id}', [InvestmentController::class, 'update']);

    //investment-cycles controlling
    Route::put('/investment-cycles/{id}/close', [InvestmentCycleController::class, 'closeCycle']);
    Route::put('/investment-cycles/{id}/change', [InvestmentCycleController::class, 'ChangeInvestmentCycle']);
    // renewal investment  controlling
    Route::put('/{id}/approve-renewal', [InvestmentController::class, 'approveRenewal']);
    Route::put('/{id}/cancel-renewal', [InvestmentController::class, 'cancelRenewal']);

    Route::post('/set-contract-duration/{investmentId}', [InvestmentController::class, 'setContractDuration']);
    Route::post('/add-bonus/{investmentId}', [BonusController::class, 'addBonus']);

    Route::put('/{investmentId}/change-profit-percentage', [InvestmentController::class, 'changeProfitPercentage']);

    Route::put('/approve-withdrawal/{withdrawalId}', [WithdrawalController::class, 'approveWithdrawal']);


    Route::get('/view-roi/{investmentId}', [HomeController::class, 'viewRoiReport']);

    Route::post('group-closed-investments', [InvestmentGroupingController::class, 'groupClosedInvestments']);
   });
// In routes/web.php
Route::middleware(['web'])->group(function () {
    Route::post('/group-closed-investments', [InvestmentGroupingController::class, 'groupClosedInvestments']);
    Route::post('/store-data-with-file-driver', [InvestmentGroupingController::class, 'storeDataWithFileDriver']);
    Route::post('/store-data-with-database-driver', [InvestmentGroupingController::class, 'storeDataWithDatabaseDriver']);
    Route::get('/get-virtual-cycles', [InvestmentGroupingController::class, 'getVirtualCycles']);
    Route::get('/get-user-profits', [InvestmentGroupingController::class, 'getUserProfits']);
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('Activity_logs')->group(function () {

    Route::get('/activities-filtered', [ActivityLogController::class, 'viewActivities']);
});



