<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Attribute\Cache;

class DashBoardController extends Controller
{
    public function index()
    {
        try {
            $data = [
                'total_students' => Student::count(),
                'total_classes' => CourseClass::count(),
                'total_attendence'=> Attendance::count(),
               
            ];

            return response()->json([
                'status' => 200,
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            Log::error('Dashboard Error: '.$th->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Server Error',
            ], 500);
        }
    }


}
