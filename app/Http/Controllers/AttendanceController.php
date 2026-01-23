<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $attendence = Attendance::with('students')->get();

            return response()->json([
                'status' => 200,
                'message' => 'Attendence Retrieved Successfully',
                'data' => $attendence,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|integer|exists:students,id',
                'class_id' => 'required|integer',
                'data' => 'required|string|min:1',
                'status' => 'required|string',
                'remark' => 'required|string',
            ]);

            $attendence = Attendance::create($validated);


            $attendence->load('students');

            return response()->json([
                'status' => 200,
                'message' => 'Attendance Created Successfully',
                'data' => $attendence,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getStudentAttendance($stuid)
    {
        $student = Student::find($stuid);

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student ID not found',
            ], 404);
        }

        $data = Attendance::with('course_classes')
            ->where('student_id', $stuid)
            ->latest()
            ->paginate(10); // change number if needed

        return response()->json([
            'status' => 'success',
            'student' => [
                'name' => $student->name,
                'phone' => $student->phone,
                'email' => $student->email,
            ],
            'attendance' => $data,
        ]);
    }

    // public function getAttendanceStats()
    // {
    //     try {

    //         $classes = CourseClass::withCount([
    //             'attendances as total_absent' => function ($query) {
    //                 $query->where('status', 'absent');
    //             },
    //         ])->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $classes,
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //         'status' => 'error',
    //         'message' => $th->getMessage()], 500);
    //     }
    // }

    // public function getTopAbsentByDate(Request $request)
    // {
    //     try {
    //         // Get month and year from request, default to current month/year if not provided
    //         $month = $request->input('month', date('m'));
    //         $year = $request->input('year', date('Y'));

    //         $students = Student::withCount(['attendances as absent_count'
    //                 => function ($query) use ($month, $year) {
    //             $query->where('status', 'absent')
    //                 ->whereMonth('attendance_date', $month)
    //                 ->whereYear('attendance_date', $year);
    //         }])
    //             ->having('absent_count', '>', 0) // Only include students with at least 1 absence
    //             ->orderBy('absent_count', 'desc') // Rank by most absences
    //             ->take(10) // Limit to top 10 for dashboard clarity
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'month' => $month,
    //                 'year' => $year,
    //             ],
    //             'data' => $students,
    //         ], 200);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to fetch absent report: '.$th->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance, $id)
    {
       try {
            $attendance = Attendance::findOrFail($id);

            $attendance->update($request->all());

            return response()->json([
                'status' => 200,
                'message' => 'Updated successfully',
                'data' => $attendance
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
