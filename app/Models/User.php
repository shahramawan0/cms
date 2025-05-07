<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'institute_id',
        'admin_id',
        'teacher_id',
        'name',
        'father_name',
        'cnic',
        'email',
        'phone',
        'profile_image',
        'designation',
        'address',
        'password',
        'qualification',
        'experience_years',
        'specialization',
        'joining_date',
        'salary',
        'account_title',
        'account_number',
        'roll_number',
        'class',
        'section',
        'admission_date',
        'gender',
        'dob',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'joining_date' => 'date',
        'dob' => 'date',
        'admission_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_image_url', 'role_name'];

    /**
     * Get the institute that owns the user.
     */
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * Get the admin who manages this user.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the teacher assigned to this student.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function courses()
{
    return $this->belongsToMany(Course::class, 'course_student', 'student_id', 'course_id');
}


    /**
     * Get the students assigned to this teacher.
     */
    public function students()
    {
        return $this->hasMany(User::class, 'teacher_id');
    }

    /**
     * Get the teachers created by this admin.
     */
    public function teachers()
    {
        return $this->hasMany(User::class, 'admin_id')->whereHas('roles', function($q) {
            $q->where('name', 'Teacher');
        });
    }

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image 
            ? asset('storage/'.$this->profile_image)
            : asset('assets/images/default-profile.png');
    }

    /**
     * Get the user's main role name.
     */
    public function getRoleNameAttribute()
    {
        return $this->roles->first()->name ?? null;
    }

    /**
     * Scope a query to only include teachers.
     */
    public function scopeTeachers($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'Teacher');
        });
    }

    /**
     * Scope a query to only include admins.
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->whereIn('name', ['Admin']);
        });
    }

    /**
     * Scope a query to only include students.
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'Student');
        });
    }

    /**
     * Check if user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('Teacher');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['Admin']);
    }

    /**
     * Check if user is a student.
     */
    public function isStudent(): bool
    {
        return $this->hasRole('Student');
    }

    /**
     * Format the salary attribute.
     */
    protected function salary(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($value, 2),
        );
    }

    /**
     * Format the joining date attribute.
     */
    protected function joiningDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('M d, Y') : null,
        );
    }

    /**
     * Format the date of birth attribute.
     */
    protected function dob(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('M d, Y') : null,
        );
    }
}
