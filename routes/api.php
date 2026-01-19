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
        Route::get('/student','index');
        Route::post('/student/enroll',  'enroll');
        Route::post('/student','store');
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
        Route::get('/attendence',"index");
        Route::post('/attendence/{stuid}',"getStudentAttendance");
        Route::get('/attendence/{id}','show');
        Route::patch('/attendence/{id}','update');
        Route::delete('/attendence/{id}','destroy');
    });

});
