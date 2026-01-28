<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseClassController;
use App\Http\Controllers\DashBoardController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::post('/register',[AuthController::class,"register"]);
Route::post('/login',[AuthController::class,"login"]);

Route::middleware(['auth:sanctum'])->group(function(){

    //logout
    Route::post('/logout', [AuthController::class, 'logout']);

    //dashbaord
    Route::get('dashboard/all',[DashBoardController::class,"index"]);

    //Student
    Route::controller(StudentController::class)->group(function(){
        Route::get('/student/class','ShowStudentWithClass');
        Route::get('/student/all',  'showAllStudents');
        Route::post('/student/create','createStudent');
        Route::post('/student','store');
        Route::post('/student/enroll','enroll');
        Route::get('/student/{id}','show');
        Route::patch('/student/{id}','update');
        Route::delete('/student/{id}','delete');
    });

    //Class
    Route::controller(CourseClassController::class)->group(function(){
        Route::get('/class','index');
        Route::post('/class','store');
        Route::get('/class/{id}','getStudentsByClass');
        Route::patch('/class/{id}','update');
        Route::delete('/class/{id}','destroy');
    });

    //Attendentces
    Route::controller(AttendanceController::class)->group(function(){
        Route::get('/attendence/all',"index");
        Route::post('/attendence','store');
        Route::get('/attendence/Stats','getAttendanceStats'); // avg attendance
        Route::get('/attendence/{stuid}',"getStudentAttendance"); //by id
        Route::patch('/attendence/{id}','update'); //sometime
        Route::delete('/attendence/{id}','destroy');
        Route::delete('/attendence','removeStudentFromClass');
    });


});
