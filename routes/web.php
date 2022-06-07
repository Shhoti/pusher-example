<?php

use App\Events\OrderStatusUpdated;
use App\Events\TaskCreated;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Psy\Readline\Hoa\Console;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/projects/{project}', function(Project $project){
    $project->load('tasks');

    return view('projects.show', compact('project'));
}) ;


Route::get('api/projects/{project}', function(Project $project) {
    $task = $project->tasks()->pluck('body');

    return $task;
});

Route::post('api/projects/{project}/tasks',function(Request $request , Project $project){
    $task = $project->tasks()->create(request(['body']));

    event(new TaskCreated($task));
    
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
