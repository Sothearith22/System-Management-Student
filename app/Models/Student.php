<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'phone', 'email'];

    /**
     * A student can enroll in many classes (Many-to-Many).
     * Uses the 'enrollment' pivot table.
     */
    public function classes()
    {
        return $this->belongsToMany(CourseClass::class, 'enrollment', 'student_id', 'class_id')
                    ->withTimestamps();
    }

    /**
     * A student has many attendance records.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }
}
