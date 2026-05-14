<?php

namespace App\Models;

use Database\Factories\FacultyAttendanceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyAttendanceRecord extends Model
{
    /** @use HasFactory<FacultyAttendanceRecordFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'faculty_user_id',
        'student_user_id',
        'student_name',
        'student_class',
        'course_strand',
        'academic_level',
        'subject_code',
        'attendance_date',
        'status',
        'academic_year',
        'semester',
    ];

    public function studentUser()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new \App\Events\AdminModelChanged('faculty_attendance_record', $model->id, 'created'));
        });

        // avoid broadcasting on every update to reduce noise
        static::deleted(function ($model) {
            event(new \App\Events\AdminModelChanged('faculty_attendance_record', $model->id, 'deleted'));
        });
    }
}
