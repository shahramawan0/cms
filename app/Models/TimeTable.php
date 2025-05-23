<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    use HasFactory;
    protected $table = 'timetables';

    protected $fillable = [
        'institute_id',
        'session_id',
        'class_id',
        'section_id',
        'course_id',
        'teacher_id',
        'time_slot_id',
        'date',
        'slot_times',
        'total_slots',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
    

    public function time_slot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id', 'id');
    }
    

}
