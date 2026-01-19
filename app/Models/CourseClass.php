<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use HasFactory;
    protected $table = 'course_classes';

    protected $fillable = ['course', 'room', 'term', 'class_time'];

    /**
     * A class has many students enrolled.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollment', 'class_id', 'student_id')
                    ->withTimestamps();
    }

    /**
     * A class has many attendance records.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}
