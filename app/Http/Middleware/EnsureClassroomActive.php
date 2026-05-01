<?php

namespace App\Http\Middleware;

use App\Models\Classroom;
use Closure;
use Illuminate\Http\Request;

class EnsureClassroomActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1) If route model binding provides a Classroom, use it
        $routeClassroom = $request->route('classroom');
        if ($routeClassroom instanceof Classroom) {
            if ($routeClassroom->status !== 'active') {
                abort(403, 'Classroom is inactive.');
            }

            return $next($request);
        }

        // 2) Check common request fields that carry a classroom code
        $possibleKeys = ['student_class', 'module_code', 'subject_id'];

        foreach ($possibleKeys as $key) {
            $value = $request->input($key);
            if (! $value) {
                continue;
            }

            // If subject_id is numeric, skip lookup
            if ($key === 'subject_id' && is_numeric($value)) {
                continue;
            }

            $classroom = Classroom::where('code', $value)->first();
            if ($classroom !== null && $classroom->status !== 'active') {
                abort(403, 'Classroom is inactive.');
            }
        }

        return $next($request);
    }
}
