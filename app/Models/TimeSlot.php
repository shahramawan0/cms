<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'slot_duration',
        'session_id',
        'week_number',
    ];

    // Accessor to get readable time range
    public function getTimeRangeAttribute()
    {
        return date('h:i A', strtotime($this->start_time)) . ' - ' . date('h:i A', strtotime($this->end_time));
    }

    // Relationship with Section (assuming section exists)
    // public function section()
    // {
    //     return $this->belongsTo(Section::class);
    // }

    
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    // App\Models\TimeSlot.php

public function timetableSlots()
{
    return $this->hasMany(TimeTable::class, 'time_slot_id', 'id');
}

}
