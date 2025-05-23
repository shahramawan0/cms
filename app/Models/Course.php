<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_name',
        'course_code',
        'description',
        'total_marks',
        'credit_hours',
        'created_by',
        'updated_by',
        'institute_id',
       
        'duration_months',
       
        'is_active',
    ];

    // Relations
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'course_student', 'course_id', 'student_id');
    }
    // Add this relationship
    public function assessments()
    {
        return $this->hasMany(CourseAssessment::class);
    }
     
}
