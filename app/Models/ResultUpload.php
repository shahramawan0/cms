<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'session_id',
        'class_id',
        'section_id',
        'course_id',
        'teacher_id',
        'timetable_id',
        'student_enrollment_id',
        'student_id',
        'obtained_marks',
        'total_marks',

        'assignment1',
        'assignment2',
        'assignment3',
        'assignment4',
        'quiz1',
        'quiz2',
        'quiz3',
        'midterm',
        'final',
        'obtained_total',
        'course_total'
    ];


    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }



    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // In App\Models\StudentEnrollment.php
    public function enrolledCourses()
    {
        return $this->hasMany(StudentEnrollCourse::class, 'st_enroll_id')->with('course');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
