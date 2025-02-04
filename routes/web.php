<?php

use App\Http\Controllers\DeliveryRchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordRchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardAdminController;
use App\Models\M_Model_Part;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ErrorController;

Route::get('/', function () {
    return redirect('/login');
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.post');
Route::get('/error', [ErrorController::class, 'show']);
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');


Route::middleware('auth')->group(function () {
    //dashboard 
    Route::get('get-spareparts-data', [DashboardController::class, 'getDataAll'])->name('getspareparts.data');
    Route::get('get-spareparts-datarch', [DashboardController::class, 'getDataAllRch'])->name('getspareparts.datarch');
    Route::get('spareparts-record', [DashboardController::class, 'getDataRecord'])->name('getrecord.data');
    Route::get('spareparts-recordrch', [DashboardController::class, 'getDataRecordRch'])->name('getrecord.datarch');
    Route::get('error-logs', [DashboardController::class, 'getDataError'])->name('getrecord.error');
    Route::get('error-logs-rch', [DashboardController::class, 'getDataErrorRch'])->name('getrecord.errorrch');
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
    Route::get('/dashboardreceh', [DashboardController::class,'create'])->name('dashboardreceh');
    Route::get('export-error-logs', [DashboardController::class, 'exportErrorLogs'])->name('export.error');
    Route::get('export-error-logs-rch', [DashboardController::class, 'exportErrorLogsRch'])->name('export.errorrch');
    Route::get('dashboard-transaksi', [DashboardController::class, 'dashboardTransaksi'])->name('export.dashboard');
    Route::get('dashboard-transaksi-detail', [DashboardController::class, 'dashboardTransaksiDetail'])->name('export.dashboarddtl');
    Route::get('dashboardrch-transaksi', [DashboardController::class, 'dashboardRecehTransaksi'])->name('export.dashboardrch');
    Route::get('dashboardrch-transaksi-detail', [DashboardController::class, 'dashboardRecehTransaksiDetail'])->name('export.dashboarddtlrch');

    //dashboard admin
    Route::get('/dashboardadmin', [DashboardAdminController::class,'index'])->name('dashboardadmin');
    Route::get('/dashboardrecehadmin', [DashboardAdminController::class,'create'])->name('dashboardrecehadmin');
    Route::get('/dashboarderror', [DashboardAdminController::class,'errorview'])->name('dashboarderror');

    //profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //import excel
    Route::get('/import', function() {
        return view('pemindahan.import'); 
    })->name('spareparts.import.view');

    //record
    Route::resource('/record', RecordController::class);
    Route::get('/get-model-data/{model}', [RecordController::class, 'getModelData']);
    Route::post('/get-plant-dest', [RecordController::class, 'getPlantDest'])->name('record.getPlantDest');
    Route::get('/getQtyBox/{model}', function($model) {
        $part = M_Model_Part::where('model', $model)->first();
        if ($part) {
            return response()->json(['qty_box' => $part->qty_box]);
        }
        return response()->json(['qty_box' => null]);
    });

    //deliveryrch
    Route::resource('/deliveryrch', controller: DeliveryRchController::class);
    Route::get('/delivery-data-receh', [DeliveryRchController::class, 'createreceh'])->name('delivery.createreceh');
    Route::post('/store-data-receh', [DeliveryRchController::class, 'storereceh'])->name('delivery.storereceh');
    Route::post('/delivery/checkSerialNumber', [DeliveryRchController::class, 'checkSerialNumber'])->name('delivery.checkSerialNumber');
    Route::get('/delivery-data-rch', [DeliveryRchController::class, 'getDeliveryDataRch'])->name('delivery.datareceh');
    Route::get('/delivery/{noTransaksi}/total-qty-rch', [DeliveryRchController::class, 'getTotalQty']);
    Route::post('/delivery/compare-rch', [DeliveryRchController::class, 'compareQty'])->name('delivery.comparereceh');
    Route::post('/verify-passwordrch', [DeliveryRchController::class, 'verifyPasswordRch'])->name('verify.passwordrch');
    Route::post('/save-log-rch', [DeliveryRchController::class, 'saveLogRch'])->name('save.logrch');

    //delivery
    Route::resource('/delivery', DeliveryController::class);
    Route::post('/delivery/compare', [DeliveryController::class, 'compareQty'])->name('delivery.compare');
    Route::get('/delivery-data', [DeliveryController::class, 'getDeliveryData'])->name('delivery.data');
    Route::get('/delivery/{noTransaksi}/total-qty', [DeliveryController::class, 'getTotalQty']);
    Route::post('/verify-password', [DeliveryController::class, 'verifyPassword'])->name('verify.password');
    Route::post('/save-log', [DeliveryController::class, 'saveLog'])->name('save.log');
});

    
require __DIR__.'/auth.php';

