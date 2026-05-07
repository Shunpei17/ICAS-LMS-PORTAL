<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ForgotPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_number',
        'admin_number',
        'onboarding_source',
        'enrollment_type',
        'is_verified',
        'needs_password_change',
        'verification_file',
        'status',
        'receipt_proof',
        'student_id_proof',
        'academic_level',
        'course',
        'strand',
        'title',
        'designation',
        'department',
        'office_hours',
        'gender',
        'address',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function moduleRecords(): HasMany
    {
        return $this->hasMany(StudentModuleRecord::class);
    }

    public function facultyAttendanceRecords(): HasMany
    {
        return $this->hasMany(FacultyAttendanceRecord::class, 'faculty_user_id');
    }

    public function classroomsAsFaculty(): HasMany
    {
        return $this->hasMany(Classroom::class, 'faculty_user_id');
    }

    public function classroomsAsStudent(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_students', 'user_id', 'classroom_id')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ForgotPasswordNotification($token));
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new \App\Events\AdminModelChanged('user', $model->id, 'created'));
        });

        // only broadcast on create/delete to reduce update noise (profile edits are frequent)
        static::deleted(function ($model) {
            event(new \App\Events\AdminModelChanged('user', $model->id, 'deleted'));
        });
    }
}
