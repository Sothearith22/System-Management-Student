<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        try {

            $students = Student::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Students retrieved successfully',
                'data' => $students

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve students: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:students,email',
                'phone' => 'required|string|min:8|max:15',
            ]);

            $student = Student::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Student created successfully',
                'data' => $student,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    // register course

    public function enroll(Request $request)
    {
        try {

            $student = Student::findOrFail($request->student_id);

            $student->classes()->attach($request->class_id);

            return response()->json([
                'status' => 200,
                'message' => 'Student enrolled successfully!',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Display a specific student.
     */
    public function show($id)
    {
        try {

            $student = Student::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $student,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
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
                'name' => 'sometimes|string|min:4',
                'phone' => 'sometimes|string|min:8',
                'email' => 'sometimes|email|unique:students,email,'.$id,
            ]);

            $student->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Student updated successfully',
                'data'    => $student->fresh()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy($id)
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Student deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Deletion failed: '.$th->getMessage(),
            ], 500);
        }
    }
}
