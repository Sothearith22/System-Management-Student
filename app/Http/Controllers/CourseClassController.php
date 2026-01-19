<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use Illuminate\Http\Request;

class CourseClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $class = CourseClass::with('students')->get();

            return response()->json([
                'status' => 200,
                'message' => ' Class retrieved successfully',
                'data' => $class,
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
                'course' => 'required|string|max:255',  // React+laravel  C++/OPP/MYSQL
                'room' => 'required|string|min:3| max:10', // B202
                'term' => 'required|string', // Monday - Thursdy
                'class_time' => 'required|string', // 9:00 - 10:30
            ]);

            $class = CourseClass::create($validated);

            return response()->json($class, 201);

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
    public function show(CourseClass $courseClass, $id)
    {
        try {
            $Courseclass = CourseClass::with('students')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $Courseclass,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getStudentsByClass($id)
    {
        try {

            $courseClass = CourseClass::with('students')->find($id);

            // Check if the class exists
            if (! $courseClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The specified class was not found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'class_info' => [
                    'id' => $courseClass->id,
                    'course_name' => $courseClass->course,
                    'room' => $courseClass->room,
                    'total_students' => $courseClass->students->count(),
                ],
                'data' => $courseClass->students,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.


     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $courseClass = CourseClass::findOrFail($id);

            $validated = $request->validate([
                'course' => 'required|string|max:255',
                'room' => 'required|string|min:3|max:100',
                'term' => 'required|string',
                'class_time' => 'required|string',
            ]);

            $courseClass->update($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Class updated Successfully',
                'data' => $courseClass,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseClass $courseClass, $id)
    {
        try {
            $courseClass = CourseClass::findOrFail($id);

            if (! $courseClass) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Class Not Found!',
                ], 404);
            }
            $courseClass->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Class Deleted Successfully!',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
