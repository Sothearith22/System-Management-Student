<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $attendence = Attendance::with('student')->get();

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
            //  Validate request data
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'class_id' => 'required|exists:course_classes,id',
                'date' => 'required|date',
                'status' => 'required|string',
                'remark' => 'sometimes|string',
            ]);

            //  Check if student is enrolled in the class
            $isEnrolled = DB::table('enrollment')
                ->where('id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Student is not enrolled in this class.',
                ], 403);
            }

            //  Check if attendance already marked for this date
            $alreadyMarked = Attendance::where('id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->where('date', $validated['date'])
                ->exists();

            if ($alreadyMarked) {
                return response()->json([
                    'status' => 409,
                    'message' => 'Attendance already marked for this student on this date.',
                ], 409);
            }

            //  Check if the date is in the future
            $attendanceDate = Carbon::parse($validated['date']);
            if ($attendanceDate->gt(Carbon::today())) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Cannot set attendance date in the future.',
                ], 422);
            }

            //  Create attendance
            $attendance = Attendance::create($validated);

            //  Return success response
            return response()->json([
                'status' => 201,
                'message' => 'Attendance created successfully.',
                'data' => $attendance,
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

        if (!$student) {
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

    public function getAttendanceStats(Request $request)
    {
        try {
            //  Validate request
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'class_id' => 'required|exists:course_classes,id',
            ]);

            $student = Student::findOrFail($validated['student_id']);
            $class = CourseClass::findOrFail($validated['class_id']);

            // Check enrollment
            $isEnrolled = DB::table('enrollment')
                ->where('student_id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Student is not enrolled in this class',
                ], 403);
            }

            //   Attendance statistics
            $stats = Attendance::where('student_id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->selectRaw("
                        COUNT(*) as total_records,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as total_present,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as total_absent,
                        SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as total_permission,
                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as total_late
                    ")
                ->first();

            // Attendance rate
            $attendanceRate = $stats->total_records > 0
                ? round(($stats->total_present / $stats->total_records) * 100, 2)
                : 0;

            return response()->json([
                'status' => 'success',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                ],
                'class' => [
                    'id' => $class->id,
                    'name' => $class->course,
                ],
                'attendance' => [
                    'total_records' => (int) $stats->total_records,
                    'total_present' => (int) $stats->total_present,
                    'total_absent' => (int) $stats->total_absent,
                    'total_permission' => (int) $stats->total_permission,
                    'total_late' => (int) $stats->total_late,
                    'attendance_rate' => $attendanceRate . '%',
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

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

            $validated = $request->validate([
                'status' => 'sometimes|string|in:present,absent,late,permission',
                'remark' => 'sometimes|nullable|string',
            ]);

            $attendance->update($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Updated successfully',
                'data' => $attendance,
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
