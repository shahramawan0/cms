<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes'; // Explicitly define table name (optional if model name matches)

    protected $fillable = [
        'session_id',
        'name',
        'status',
        'description',
    ];

    /**
     * Relationship: A class belongs to a session.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
