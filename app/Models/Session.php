<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $fillable = [
        'institute_id',
        'session_name',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    // Define relationship with Institute
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
