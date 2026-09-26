<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordCodeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Supply\SupplyController;
use App\Http\Controllers\Inspector\InspectorController;
use App\Http\Controllers\WorkNoteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// route for the landing page 
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});


// redirects to specific dashboard based on the role of the user
// NOTE: uses Auth::user()->role (the accessor on the User model), which
// reads the role chosen at login (session 'active_role') instead of just
// the first row in the role_user pivot table. This matters for users who
// have more than one role attached.
Route::get('/dashboard', function () {
    $role = Auth::user()->role;

    if ($role === 'admin') {
        return view('admin.dashboard');
    } elseif ($role === 'supply') {
        return redirect()->route('supply.dashboard');
    } elseif ($role === 'inspector') {
        return redirect()->route('inspector.dashboard');
    } else {
        return view('users.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// admin routes here 
Route::namespace('App\Http\Controllers\Admin')->prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {

    // add routes here for admin 
    Route::resource('/users', 'UserController', ['except' => ['create', 'store', 'destroy']]);
    Route::get('/userfeedbacks', 'UserController@userfeedback')->name('userfeedback');


    Route::get('/PRManagement', 'PRManagementController@index')->name('PRManagement');

    Route::post('/PRManagement', 'PRManagementController@PRstore')
        ->name('PRManagement');

    // Live AJAX check used by the PR Management form to warn if a PR number is already used
    Route::post('/pr-number/check', 'PRManagementController@checkPrNumber')
        ->name('checkPrNumber');

    Route::get('/IssueMaterial/issueindex', 'PRManagementController@IssueIndex')
        ->name('IssueMaterial');


    Route::get('/AuditTrial', 'adminbuttonCRTL@AuditIndex')
        ->name('AuditTrail');

    Route::get('/DraftTable', 'adminbuttonCRTL@DTIndex')
        ->name('DraftTable');


    Route::get('/ProjectSelection', 'adminbuttonCRTL@PSIndex')
        ->name('ProjectSelection');

    Route::get('/Receiving', 'adminbuttonCRTL@ReceiveIndex')
        ->name('Receiving');


    Route::get('/FinalSubmit', 'adminbuttonCRTL@FSIndex')
        ->name('FinalSubmit');


    Route::get('/Report', 'adminbuttonCRTL@ReportIndex')
        ->name('Report');

    Route::get('/Project', 'adminbuttonCRTL@ProjectIndex')
        ->name('Project');

    // Weekly Audit Trail — mirrors Supply's /supply/WeekAudit. The
    // controller method (PRManagementController@WeeklyIndex) already
    // existed and was already wired to the shared InventoryReporting
    // trait; it just had no route pointing at it yet.
    Route::get('/WeeklyAudit', 'PRManagementController@WeeklyIndex')
        ->name('WeeklyAudit');


    Route::prefix('Message')->name('Message')->group(function () {


        route::get('worknotes', 'MessageController@MessageIndex')->name('worknotes');

        Route::post('worknotes-store', 'MessageController@store')->name('store');

        // Live Work Notes: typing signal (debounced POST from the client
        // while typing) + poll (new messages + who's typing), so the page
        // updates without a full reload — matches Inspector's Work Notes.
        Route::post('worknotes-typing', 'MessageController@typing')->name('typing');
        Route::get('worknotes-poll', 'MessageController@poll')->name('poll');
    });
});


Route::namespace('App\Http\Controllers\Supply')
    ->prefix('supply')
    ->name('supply.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // 1. Gawin nating Controller-based ang main dashboard route
        Route::get('/dashboard', 'SupplyController@index')->name('dashboard');

        Route::get('/precurement', 'SupplyController@PreIndex')->name('precurement');
        Route::post('/precurement', 'SupplyController@PrStore')->name('PrStore');

        Route::get('/supply/procurement', 'SupplyController@procurementPage')->name('supply.procurement');
        Route::get('/joint-inspection', 'SupplyController@UploadActualItemIndex')->name('UploadActualItem');
        Route::post('/joint-inspection/upload', 'SupplyController@uploadDeliverySpecs')->name('uploadDeliverySpecs');
        Route::get('/Warehouse', 'SupplyController@WHIndex')->name('Warehouse');
        Route::post('/warehouse/assign', 'SupplyController@assignToWarehouse')->name('warehouse.assign');
        Route::get('/WeekAudit', 'SupplyController@WEEKIndex')->name('WeekAudit');
        Route::get('/Project', 'SupplyController@ProjectIndex')->name('Project');
        Route::post('/Project', 'SupplyController@ProjectStore')->name('Project.store');
        Route::get('/Report', 'SupplyController@ReportIndex')->name('Report');
        Route::get('/Distribution', 'SupplyController@DistributionIndex')->name('Distribution');
        Route::post('/Distribution', 'SupplyController@DistributionStore')->name('Distribution.store');

        Route::prefix('Message')->name('Message')->group(function () {
            Route::get('worknotes', 'SupplyMessageController@MessageIndex')->name('worknotes');
            Route::post('worknotes-store', 'SupplyMessageController@store')->name('store');

            // Live Work Notes: typing signal (debounced POST from the client
            // while typing) + poll (new messages + who's typing), so the page
            // updates without a full reload — matches Inspector's Work Notes.
            Route::post('worknotes-typing', 'SupplyMessageController@typing')->name('typing');
            Route::get('worknotes-poll', 'SupplyMessageController@poll')->name('poll');
        });
    });


Route::namespace('App\Http\Controllers\Inspector')
    ->prefix('inspector')
    ->name('inspector.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // Now controller-based so $stats (pending/approved/rejected/remote)
        // reaches the dashboard view — matches how Supply's dashboard route works.
        Route::get('/dashboard', 'InspectorController@index')->name('dashboard');

        Route::get('/inspect', 'InspectorController@InspectIndex')->name('inspect');

        Route::get('/Project', 'InspectorController@ProjectIndex')->name('Project');

        Route::get('/Report', 'InspectorController@ReportIndex')->name('Report');

        Route::get('/Message', 'InspectorController@WorknoteIndex')->name('Message');


        Route::get('/approve', 'InspectorController@approveIndex')->name('approve');

        Route::get('/reject', 'InspectorController@rejectIndex')->name('reject');

        Route::get('/pending', 'InspectorController@pendingIndex')->name('pending');

        Route::post('/purchase-order/{id}/status', 'InspectorController@updateStatus')->name('updateStatus');

        Route::post('/purchase-order/{id}/item/{index}/status', 'InspectorController@updateItemStatus')->name('item.status');


        Route::prefix('Message')->name('Message')->group(function () {
            route::get('worknotes', 'InspectorMessageController@MessageIndex')->name('worknotes');

            Route::post('worknotes-store', 'InspectorMessageController@store')->name('store');

            // Live Work Notes: typing signal (debounced POST from the client
            // while typing) + poll (new messages + who's typing), used by
            // WorknoteIndex.blade.php's setInterval loop instead of a reload.
            Route::post('worknotes-typing', 'InspectorMessageController@typing')->name('typing');
            Route::get('worknotes-poll', 'InspectorMessageController@poll')->name('poll');
        });
    });

// Forgot password by 6-digit code (replaces the old Breeze link-based routes)
Route::middleware('guest')->group(function () {
    // Step 1: enter email  (landing page "Forgot password?" links here)
    Route::get('forgot-password', [PasswordCodeController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordCodeController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    // Step 2: enter the emailed code + new password
    Route::get('reset-password-code', [PasswordCodeController::class, 'showCode'])
        ->name('password.code');

    Route::post('reset-password-code', [PasswordCodeController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('password.code.update');
});

// users routes here 

require __DIR__ . '/auth.php';


//testing