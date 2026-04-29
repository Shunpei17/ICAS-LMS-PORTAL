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
        'attendance_date',
        'status',
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
}
