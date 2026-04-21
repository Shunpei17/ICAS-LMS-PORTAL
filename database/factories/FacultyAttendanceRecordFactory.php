<?php

namespace Database\Factories;

use App\Models\FacultyAttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacultyAttendanceRecord>
 */
class FacultyAttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['Present', 'Absent', 'Late'];

        return [
            'faculty_user_id' => User::factory()->state(['role' => 'faculty']),
            'student_name' => fake()->name(),
            'student_class' => fake()->randomElement(['9th A', '9th B', '10th A', '10th B', '11th A']),
            'attendance_date' => fake()->dateTimeBetween('-14 days', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement($statuses),
        ];
    }
}
