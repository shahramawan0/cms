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
use App\Http\Controllers\TeacherCourseEnrollController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\ResultUploadController;










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
    
    // Assessment Routes
    Route::get('/admin/courses/{courseId}/assessments', [CourseController::class, 'getAssessments'])->name('admin.courses.assessments');
    Route::post('/admin/courses/assessments/store', [CourseController::class, 'storeAssessment'])->name('admin.courses.assessments.store');
});

Route::middleware(['auth'])->group(function () {
    // Student Routes
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/data', [StudentController::class, 'getStudents'])->name('students.data');
    Route::post('/students/store', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/edit/{id}', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/update/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/delete/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    
    // New filter routes
    Route::get('students/filter/options', [StudentController::class, 'getFilterOptions'])->name('students.filter.options');
    Route::get('students/sections/by-class', [StudentController::class, 'getSectionsByClass'])->name('students.sections.by.class');
});

Route::middleware(['auth'])->group(function () {
    // Enrollment Routes
    Route::get('/enrollments', [StudentEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/edit/{id}', [StudentEnrollmentController::class, 'edit'])->name('enrollments.edit');
    Route::get('/enrollments/students', [StudentEnrollmentController::class, 'getStudents'])->name('enrollments.students');
    Route::get('/enrollments/dropdowns', [StudentEnrollmentController::class, 'getDropdowns'])->name('enrollments.dropdowns');
    Route::post('/enrollments/store', [StudentEnrollmentController::class, 'store'])->name('enrollments.store');
    Route::post('/enrollments/upload-csv', [StudentEnrollmentController::class, 'uploadCsv'])->name('enrollments.upload-csv');
    Route::put('/enrollments/update/{id}', [StudentEnrollmentController::class, 'update'])->name('enrollments.update');

    Route::get('/enrollments/data', [StudentEnrollmentController::class, 'getEnrollments'])->name('enrollments.data');
    Route::delete('/enrollments/delete/{id}', [StudentEnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    Route::get('/enrollments/courses/{id}', [StudentEnrollmentController::class, 'getEnrolledCourses'])->name('enrollment.course');


        // Route::get('/report', [EnrollmentReportController::class, 'index'])->name('enrollments.report');
        // Route::post('/report/generate', [EnrollmentReportController::class, 'generateReport'])->name('enrollments.report.generate');
        // Route::get('/report/student/{id}', [EnrollmentReportController::class, 'studentDetail'])->name('enrollments.report.student');
        // Route::post('/report/pdf', [EnrollmentReportController::class, 'generatePdf'])->name('enrollments.report.pdf');
        // Route::post('/report/student-pdf', [EnrollmentReportController::class, 'generateStudentPdf'])->name('enrollments.report.student-pdf');

    
        Route::get('/enrollments/report', [StudentEnrollmentController::class, 'EnrollmentReport'])->name('enrollments.report');

        Route::post('enrollments/report/generate', [StudentEnrollmentController::class, 'generateReport'])->name('enrollments.report.generate');
        Route::get('enrollments/report/student/{id}', [StudentEnrollmentController::class, 'studentDetail'])->name('enrollments.report.student');
        Route::post('enrollments/report/pdf', [StudentEnrollmentController::class, 'generatePdf'])->name('enrollments.report.pdf');
        Route::post('enrollments/report/student-pdf', [StudentEnrollmentController::class, 'generateStudentPdf'])->name('enrollments.report.student-pdf');


        // Add these new routes
        Route::get('enrollments/report/attendance-details', [StudentEnrollmentController::class, 'attendanceDetails'])->name('enrollments.report.attendance-details');
        Route::get('enrollments/report/result-details', [StudentEnrollmentController::class, 'resultDetails'])->name('enrollments.report.result-details');
        Route::post('enrollments/report/generate-attendance-pdf', [StudentEnrollmentController::class, 'generateAttendancePdf'])->name('enrollments.report.generate-attendance-pdf');
        Route::post('enrollments/report/generate-result-pdf', [StudentEnrollmentController::class, 'generateResultPdf'])->name('enrollments.report.generate-result-pdf');

        
    

});
Route::prefix('teacher/enrollments')->name('teacher.enrollments.')->group(function () {
    Route::get('/', [TeacherCourseEnrollController::class, 'index'])->name('index');
    Route::get('/form', [TeacherCourseEnrollController::class, 'form'])->name('form');
    Route::get('/session-data', [TeacherCourseEnrollController::class, 'getSessionData'])->name('session-data');
    Route::get('/assigned-data', [TeacherCourseEnrollController::class, 'getAssignedData'])->name('assigned-data');
    Route::post('/store', [TeacherCourseEnrollController::class, 'store'])->name('store');
    Route::post('/update', [TeacherCourseEnrollController::class, 'update'])->name('update');
    Route::post('/unassign', [TeacherCourseEnrollController::class, 'unassignTeacher'])->name('unassign');
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


// Lectures Routes
Route::prefix('lectures')->group(function () {
    Route::get('/', [LectureController::class, 'index'])->name('lectures.index');
    Route::post('/store', [LectureController::class, 'store'])->name('lectures.store');
    Route::get('/edit/{id}', [LectureController::class, 'edit'])->name('lectures.edit');
    Route::put('/update/{id}', [LectureController::class, 'update'])->name('lectures.update');
    Route::delete('/delete/{id}', [LectureController::class, 'destroy'])->name('lectures.destroy');
    Route::get('/data', [LectureController::class, 'getLectures'])->name('lectures.data');
    Route::get('/dropdowns', [LectureController::class, 'getDropdowns'])->name('lectures.dropdowns');
    Route::get('/teachers', [LectureController::class, 'getTeachers'])->name('lectures.teachers');
    Route::get('/view/{id}', [LectureController::class, 'view'])->name('lectures.view');
    Route::get('/download/{id}/{type}', [LectureController::class, 'download'])->name('lectures.download');

});
// attendence
Route::middleware(['auth'])->group(function () {
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('attendances/students', [AttendanceController::class, 'getStudents'])->name('attendances.students');
    Route::get('attendances/dropdowns', [AttendanceController::class, 'getDropdowns'])->name('attendances.dropdowns');
    Route::get('attendances/timetable', [AttendanceController::class, 'getTimetable'])->name('attendances.timetable');
    Route::post('attendances/mark', [AttendanceController::class, 'markAttendance'])->name('attendances.mark');
    Route::get('attendances/courses', [AttendanceController::class, 'getCourses'])->name('attendances.courses');
    Route::get('attendances/slots', [AttendanceController::class, 'getTimeSlots'])->name('attendances.slots');
    Route::get('attendances/report', [AttendanceController::class, 'report'])->name('attendances.report');
    Route::post('attendances/report/generate', [AttendanceController::class, 'generateReport'])->name('attendances.report.generate');
    Route::post('attendances/report/pdf', [AttendanceController::class, 'generatePdf'])->name('attendances.report.pdf');
});
// routes/web.php
Route::group(['middleware' => ['auth']], function() {
    Route::prefix('time-table')->name('time-table.')->group(function() {
        Route::get('/', [TimeTableController::class, 'index'])->name('index');
        Route::get('/dropdowns', [TimeTableController::class, 'getDropdowns'])->name('dropdowns');
        Route::get('/time-slot-templates', [TimeTableController::class, 'getTimeSlotTemplates'])->name('time-slot-templates');
        Route::get('/calculate-slots', [TimeTableController::class, 'calculateSlots'])->name('calculate-slots');
        Route::post('/check-availability', [TimeTableController::class, 'checkAvailability'])->name('check-availability');
        Route::get('/load-timetable', [TimeTableController::class, 'loadTimetable'])->name('load-timetable');
        Route::post('/', [TimeTableController::class, 'store'])->name('store');
        
        // Edit/Update/Delete routes
        Route::get('/edit/{id}', [TimeTableController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [TimeTableController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [TimeTableController::class, 'destroy'])->name('destroy');

         // Add these new routes for reporting
        Route::get('/report', [TimeTableController::class, 'report'])->name('report');
        Route::get('/generate-report', [TimeTableController::class, 'generateReport'])->name('generate-report');
        Route::post('/export-report', [TimeTableController::class, 'exportReport'])->name('export-report');
        // Route::post('time-table/export-report', [TimeTableController::class, 'export'])->name('export-report');

    });
    
    Route::prefix('class-slots')->group(function() {
        Route::get('/', [TimeTableController::class, 'slotindex'])->name('class-slots.index');
        Route::get('/list', [TimeTableController::class, 'getClassSlots'])->name('class-slots.list');
        Route::post('/', [TimeTableController::class, 'storeClassSlot'])->name('class-slots.store');
        Route::get('/{id}', [TimeTableController::class, 'getSlotDetails'])->name('class-slots.show');
        Route::put('/{id}', [TimeTableController::class, 'updateClassSlot'])->name('class-slots.update');
        Route::delete('/{id}', [TimeTableController::class, 'deleteClassSlot'])->name('class-slots.destroy');
    });
});

// routes/web.php
Route::group(['middleware' => ['auth']], function() {
    // Result routes
    Route::get('results', [ResultUploadController::class, 'index'])->name('results.index');
    Route::post('results/students', [ResultUploadController::class, 'getStudents'])->name('results.students');
    Route::get('results/dropdowns', [ResultUploadController::class, 'getDropdowns'])->name('results.dropdowns');
    Route::post('results/store', [ResultUploadController::class, 'store'])->name('results.store');
    
    // Result View Routes
    Route::get('/results/view', [ResultUploadController::class, 'view'])->name('results.view');
    Route::post('/results/view-data', [ResultUploadController::class, 'getViewData'])->name('results.view-data');
    Route::get('/results/details', [ResultUploadController::class, 'getResultDetails'])->name('results.details');
    Route::get('/results/generate-pdf', [ResultUploadController::class, 'generatePdf'])->name('results.generate-pdf');
    Route::get('/results/generate-student-pdf', [ResultUploadController::class, 'generateStudentPdf'])->name('results.generate-student-pdf');
});


// Route::get('/EnrollCourse', function () {
//     return view('enrollments.teacherCourseEnrollment.index');
// });

// Lecture Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/lectures', [LectureController::class, 'index'])->name('lectures.index');
    Route::get('/lectures/get-courses', [LectureController::class, 'getCourses'])->name('lectures.get-courses');
    Route::get('/lectures/get-time-slots', [LectureController::class, 'getTimeSlots'])->name('lectures.get-time-slots');
    Route::post('/lectures/store', [LectureController::class, 'store'])->name('lectures.store');
    Route::get('/lectures/dropdowns', [LectureController::class, 'getDropdowns'])->name('lectures.dropdowns');
    Route::get('/lectures/edit/{id}', [LectureController::class, 'edit'])->name('lectures.edit');
    Route::put('/lectures/update/{id}', [LectureController::class, 'update'])->name('lectures.update');
    Route::delete('/lectures/delete/{id}', [LectureController::class, 'destroy'])->name('lectures.delete');
    Route::get('/lectures/view/{id}', [LectureController::class, 'view'])->name('lectures.view');
    Route::get('/lectures/download/{id}/{type}', [LectureController::class, 'download'])->name('lectures.download');
});