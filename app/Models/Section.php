<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = [
        'class_id',
        'section_name',
        'status',
        'description',
    ];

    /**
     * Relationship: A section belongs to a class.
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
