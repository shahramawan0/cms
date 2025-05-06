<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentEnrollmentController;










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
Route::middleware(['auth'])->group(function () {
    // Institute Routes
    Route::get('/institutes', [InstituteController::class, 'index'])->name('institutes.index');
    Route::get('/institutes/data', [InstituteController::class, 'getInstitutes'])->name('institutes.data');
    Route::post('/institutes/store', [InstituteController::class, 'store'])->name('institutes.store');
    Route::get('/institutes/edit/{id}', [InstituteController::class, 'edit'])->name('institutes.edit');
    Route::put('/institutes/update/{id}', [InstituteController::class, 'update'])->name('institutes.update');
    Route::delete('/institutes/delete/{id}', [InstituteController::class, 'destroy'])->name('institutes.destroy');
});

// Admin User Routes
// Admin Users Routes
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/edit/{id}', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/update/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/delete/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/users/data', [AdminUserController::class, 'getUsers'])->name('admin.users.data');
    Route::get('/admin/users/view/{id}', [AdminUserController::class, 'view'])->name('admin.users.view');
});

Route::middleware(['auth'])->group(function () {
    // Teacher Routes
    Route::get('/admin/teachers', [TeacherController::class, 'index'])->name('admin.teachers.index');
    Route::get('/admin/teachers/data', [TeacherController::class, 'getTeachers'])->name('admin.teachers.data');
    Route::post('/admin/teachers/store', [TeacherController::class, 'store'])->name('admin.teachers.store');
    Route::get('/admin/teachers/edit/{id}', [TeacherController::class, 'edit'])->name('admin.teachers.edit');
    Route::post('/admin/teachers/update/{id}', [TeacherController::class, 'update'])->name('admin.teachers.update');
    Route::delete('/admin/teachers/delete/{id}', [TeacherController::class, 'destroy'])->name('admin.teachers.destroy');
    Route::get('/admin/teachers/getAdmins/{institute_id}', [TeacherController::class, 'getAdminsByInstitute'])->name('admin.teachers.getAdmins');
});
Route::middleware(['auth'])->group(function () {
    // Course Routes
    Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses.index');
    Route::get('/admin/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::post('/admin/courses/store', [CourseController::class, 'store'])->name('admin.courses.store');
    Route::get('/admin/courses/edit/{id}', [CourseController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/admin/courses/update/{id}', [CourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/admin/courses/delete/{id}', [CourseController::class, 'destroy'])->name('admin.courses.destroy');
    Route::get('/admin/courses/view/{id}', [CourseController::class, 'view'])->name('admin.courses.view');
    Route::get('/admin/courses/data', [CourseController::class, 'getCourses'])->name('admin.courses.data');
});

Route::middleware(['auth'])->group(function () {
    // Student Routes
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/data', [StudentController::class, 'getStudents'])->name('students.data');
    Route::post('/students/store', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/update/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/delete/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
});

Route::middleware(['auth'])->group(function () {
    // Enrollment Routes
    Route::get('/enrollments', [StudentEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/students', [StudentEnrollmentController::class, 'getStudents'])->name('enrollments.students');
    Route::get('/enrollments/dropdowns', [StudentEnrollmentController::class, 'getDropdowns'])->name('enrollments.dropdowns');
    Route::post('/enrollments/store', [StudentEnrollmentController::class, 'store'])->name('enrollments.store');
    Route::get('/enrollments/data', [StudentEnrollmentController::class, 'getEnrollments'])->name('enrollments.data');
    Route::delete('/enrollments/delete/{id}', [StudentEnrollmentController::class, 'destroy'])->name('enrollments.destroy');
});

Route::group(['middleware' => ['auth']], function() {
    // Session Routes
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/data', [SessionController::class, 'getSessions'])->name('sessions.data');
    Route::post('/sessions', [SessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/edit/{id}', [SessionController::class, 'edit'])->name('sessions.edit');
    Route::put('/sessions/update/{id}', [SessionController::class, 'update'])->name('sessions.update');
    Route::delete('/sessions/delete/{id}', [SessionController::class, 'destroy'])->name('sessions.destroy');
});

Route::group(['middleware' => ['auth']], function() {
    // Class Routes
    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/data', [ClassController::class, 'getClasses'])->name('classes.data');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/edit/{id}', [ClassController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/update/{id}', [ClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/delete/{id}', [ClassController::class, 'destroy'])->name('classes.destroy');
});

Route::group(['middleware' => ['auth']], function() {
    // Section Routes
    Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
    Route::get('/sections/data', [SectionController::class, 'getSections'])->name('sections.data');
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::get('/sections/edit/{id}', [SectionController::class, 'edit'])->name('sections.edit');
    Route::put('/sections/update/{id}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/delete/{id}', [SectionController::class, 'destroy'])->name('sections.destroy');
});