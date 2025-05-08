<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollCourse extends Model
{
    use HasFactory;

    protected $table = 'student_enroll_courses';

    protected $fillable = [
        'st_enroll_id',
        'course_id',
        'session_id',
        'class_id',
        'section_id',
    ];

    // Relationships

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function course()
    {
    return $this->belongsTo(Course::class, 'course_id');
    }   


    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class); // Replace with actual class name if not `ClassModel`
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
