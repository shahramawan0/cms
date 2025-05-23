<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'type', 'title', 'marks', 'weightage_percent'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
