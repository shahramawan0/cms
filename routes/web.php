<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\TeacherController;




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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
});

Route::get('/permissions/assign', [PermissionController::class, 'showAssignForm'])->name('permissions.assign');
Route::post('/permissions/assign', [PermissionController::class, 'assignPermissions'])->name('permissions.submit');
Route::get('/permissions/role/{role}', [PermissionController::class, 'getRolePermissions']);
Route::put('/permissions/update/{role}', [PermissionController::class, 'update'])->name('permissions.update');
   
// Institute Routes
Route::get('/institutes', [InstituteController::class, 'index'])->name('institutes.index');
Route::get('/institutes/create', [InstituteController::class, 'create'])->name('institutes.create');
Route::post('/institutes/store', [InstituteController::class, 'store'])->name('institutes.store');
Route::get('/institutes/edit/{id}', [InstituteController::class, 'edit'])->name('institutes.edit');
Route::post('/institutes/update/{id}', [InstituteController::class, 'update'])->name('institutes.update');
Route::delete('/institutes/delete/{id}', [InstituteController::class, 'destroy'])->name('institutes.destroy');
Route::get('/institutes/data', [InstituteController::class, 'getInstitutes'])->name('institutes.data');


// Admin User Routes
// Admin Users Routes
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/edit/{id}', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::post('/users/update/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/delete/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/users/data', [AdminUserController::class, 'getUsers'])->name('admin.users.data');
    Route::get('/admin/users/view/{id}', [AdminUserController::class, 'view'])->name('admin.users.view');
});

Route::middleware(['auth'])->group(function () {
// Teacher Routes
Route::get('/admin/teachers', [TeacherController::class, 'index'])->name('admin.teachers.index');
Route::get('/admin/teachers/create', [TeacherController::class, 'create'])->name('admin.teachers.create');
Route::post('/admin/teachers/store', [TeacherController::class, 'store'])->name('admin.teachers.store');
Route::get('/admin/teachers/edit/{id}', [TeacherController::class, 'edit'])->name('admin.teachers.edit');
Route::post('/admin/teachers/update/{id}', [TeacherController::class, 'update'])->name('admin.teachers.update');
Route::delete('/admin/teachers/delete/{id}', [TeacherController::class, 'destroy'])->name('admin.teachers.destroy');
Route::get('/admin/teachers/view/{id}', [TeacherController::class, 'view'])->name('admin.teachers.view');
Route::get('/admin/teachers/data', [TeacherController::class, 'getTeachers'])->name('admin.teachers.data');
Route::get('/admin/teachers/getAdmins/{institute_id}', [TeacherController::class, 'getAdminsByInstitute']);
});


