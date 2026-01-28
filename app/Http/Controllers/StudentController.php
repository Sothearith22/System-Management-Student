<?php
namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // show with class
    public function ShowStudentWithClass()
    {
        try {
            $students = Student::with('course_classes')->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'All students with courses retrieved successfully',
                'data'    => $students,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    // show all students
    public function showAllStudents(Request $request)
    {
        try {
            $user = $request->user();

            // Find students who are in classes that are assigned to this user
            $students = Student::whereHas('classes', function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->get();
            return response()->json([
                'status'  => 'success',
                'message' => 'All students retrieved successfully',
                'data'    => $students,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function createStudent(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:students,email',
                'phone' => 'required|string|min:8|max:15',

            ]);
            $student = Student::create($validated);

            return response()->json($student, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        // DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:students,email',
                'phone'    => 'required|string|min:8|max:15',
                'class_id' => 'required|exists:course_classes,id',
            ]);

            $student = Student::create([
                'name'  => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            $student->classes()->attach($validated['class_id']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Student created and enrolled successfully',
                'data'    => $student->load('classes'),
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id'   => 'required|exists:course_classes,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $student->classes()->syncWithoutDetaching([
            $validated['class_id'],
        ]);

        return response()->json([
            'message' => 'Student enrolled successfully',
            'student' => $student,
        ], 201);
    }

    /**
     * Display a specific student.
     */
    public function show($id)
    {
        try {
            $student = Student::with('course_classes')->findOrFail($id);

            return response()->json([
                'status'  => 'success',
                'message' => 'Student retrieved successfully',
                'data'    => $student,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Student not found',
            ], 404);

        } catch (\Throwable $th) {

            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $student = Student::findOrFail($id);

            $validated = $request->validate([
                'name'  => 'sometimes|string|min:4',
                'phone' => 'sometimes|string|min:8',
                'email' => 'sometimes|email|unique:students,email,' . $id,
            ]);

            $student->update($validated);

            return response()->json([
                'status'  => 'success',
                'message' => 'Student updated successfully',
                'data'    => $student->fresh(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function delete($id)
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Student deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Deletion failed: ' . $th->getMessage(),
            ], 500);
        }
    }
}
