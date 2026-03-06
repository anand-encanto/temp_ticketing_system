<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketSummaryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Location Routes
Route::get('location/list', [LocationController::class, 'getAllLocations']);
Route::post('location/add', [LocationController::class, 'addLocation']);
Route::post('location/edit/{id}', [LocationController::class, 'editLocation']);
Route::get('location/details/{id}', [LocationController::class, 'locationDetails']);
Route::get('location/delete/{id}', [LocationController::class, 'locationDelete']);

// Department Routes
Route::get('department/list', [DepartmentController::class, 'getAllDepartments']);
Route::post('department/add', [DepartmentController::class, 'addDepartment']);
Route::post('department/edit/{id}', [DepartmentController::class, 'editDepartment']);
Route::get('department/details/{id}', [DepartmentController::class, 'departmentDetails']);
Route::get('department/delete/{id}', [DepartmentController::class, 'departmentDelete']);

Route::get('notification/my', [NotificationController::class, 'getMyNotification']);
Route::post('notification/update/{id}', [NotificationController::class, 'updateNotification']);
Route::get('notification/delete/{id}', [NotificationController::class, 'notificationDelete']);

// Signup
Route::post('register', [LoginController::class, 'register'])->name('register');
// Login
Route::post('login', [LoginController::class, 'login'])->name('login');
// Forgot Password
Route::post('forgot_password', [LoginController::class, 'forgot_password']);
Route::post('reset_password', [LoginController::class, 'reset_password']);

// Admin
Route::middleware('AdminLogin')->group(function () {
	Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {

		// User Management
		Route::post('user/add', [AdminController::class, 'addUser']);
		Route::get('user/list', [AdminController::class, 'getAllUsers']);
		Route::post('user/edit/{id}', [AdminController::class, 'editUser']);
		Route::get('user/details/{id}', [AdminController::class, 'userDetails']);
		Route::get('user/delete/{id}', [AdminController::class, 'userDelete']);

		// Ticket Management
		Route::get('ticket/list', [TicketController::class, 'getAllTickets']);
		Route::get('ticket/details/{id}', [TicketController::class, 'ticketDetails']);

		// Summary
		Route::get('weekly_summary', [AdminTicketController::class, 'weeklySummary']);
		Route::get('tickets/urgent', [AdminTicketController::class, 'urgentTickets']);
		Route::get('tickets/resolution-times', [AdminTicketController::class, 'resolutionTimes']);
		Route::get('tickets/top-locations', [AdminTicketController::class, 'topLocations']);
		Route::get('tickets/open', [AdminTicketController::class, 'openTickets']);
		Route::get('tickets/closed', [AdminTicketController::class, 'closedTickets']);

	});
});



// Standard User
Route::middleware('Login')->group(function () {
	// User
	Route::get('user/profile', [UserController::class, 'profile']);
	Route::post('user/update_profile', [UserController::class, 'updateProfile']);

	// Ticket Management
	Route::get('ticket/list', [TicketController::class, 'getAllTickets']);
	Route::post('ticket/create', [TicketController::class, 'createTicket']);
	Route::post('ticket/update/{id}', [TicketController::class, 'updateTicket']);
	Route::get('ticket/my', [TicketController::class, 'getMyTickets']);
	Route::get('ticket/details/{id}', [TicketController::class, 'ticketDetails']);

	// Summary
	Route::get('weekly_summary', [TicketSummaryController::class, 'weeklySummary']);
	Route::get('tickets/urgent', [TicketSummaryController::class, 'urgentTickets']);
	Route::get('tickets/resolution-times', [TicketSummaryController::class, 'resolutionTimes']);
	Route::get('tickets/top-locations', [TicketSummaryController::class, 'topLocations']);
	Route::get('tickets/open', [TicketSummaryController::class, 'openTickets']);
	Route::get('tickets/closed', [TicketSummaryController::class, 'closedTickets']);

	// Comment
	Route::post('comment/add/{ticketId}', [CommentController::class, 'addComment']);
	Route::post('comment/edit/{id}', [CommentController::class, 'editComment']);
	Route::get('comment/delete/{id}', [CommentController::class, 'commentDelete']);

	// Depatment
	Route::get('get_all_executive', [HomeController::class, 'getAllExecutive']);
	Route::post('ticket/assign_ticket/{id}', [TicketController::class, 'assignTicket']);
	// Executive
	Route::get('executive/weekly_summary', [TicketController::class, 'executiveWeeklySummary']);

	Route::get('ticket/assigned_list', [TicketController::class, 'getAssignedTickets']);
	Route::post('ticket/accept/{id}', [TicketController::class, 'acceptTicket']);
	Route::post('ticket/update_status/{id}', [TicketController::class, 'updateTicketStatus']);
});

// Department
Route::middleware('DepartmentLogin')->group(function () {
});

// Executive
Route::middleware('ExecutiveLogin')->group(function () {

});

Route::get('get_countries', [HomeController::class, 'get_countries'])->name('get_countries');
Route::get('get_states/{id}', [HomeController::class, 'get_states'])->name('get_states');
Route::get('get_cities/{id}', [HomeController::class, 'get_cities'])->name('get_cities');



Route::get('subscription-plans/all', [SubscriptionPlanController::class, 'index']);
        Route::post('subscription-plans/add', [SubscriptionPlanController::class, 'store']);
        Route::get('subscription-plans/{id}', [SubscriptionPlanController::class, 'show']);
        Route::put('subscription-plans/{id}', [SubscriptionPlanController::class, 'update']);

		