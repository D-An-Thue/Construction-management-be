<?php

use App\Http\Controllers\Api\AppSettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\PersonGroupController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SubTaskController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::prefix('authentications')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-pasword', [AuthController::class, 'forgotPassword']);
});

Route::get('appsettings/public', [AppSettingController::class, 'public']);
Route::post('roles/seed', [RoleController::class, 'seed']);

Route::middleware(['jwt'])->group(function () {
    Route::prefix('authentications')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:role.manage');
        Route::get('{roleId}', [RoleController::class, 'show'])->middleware('permission:role.manage')->whereNumber('roleId');
        Route::get('me', [RoleController::class, 'me']);
        Route::get('permissions', [RoleController::class, 'permissions'])->middleware('permission:role.manage');

        Route::post('/', [RoleController::class, 'store'])->middleware('permission:role.manage');
        Route::put('/', [RoleController::class, 'update'])->middleware('permission:role.manage');
        Route::delete('{roleId}', [RoleController::class, 'destroy'])->middleware('permission:role.manage')->whereNumber('roleId');

        Route::post('{roleId}/permissions', [RoleController::class, 'assignPermission'])->middleware('permission:role.manage')->whereNumber('roleId');
        Route::delete('{roleId}/permissions/{permissionId}', [RoleController::class, 'removePermission'])
            ->middleware('permission:role.manage')
            ->whereNumber(['roleId', 'permissionId']);

        Route::post('{roleId}/users', [RoleController::class, 'assignUser'])->middleware('permission:role.manage')->whereNumber('roleId');
        Route::delete('{roleId}/users/{personId}', [RoleController::class, 'removeUser'])
            ->middleware('permission:role.manage')
            ->whereNumber(['roleId', 'personId']);
    });

    Route::prefix('groups')->group(function () {
        Route::get('group', [GroupController::class, 'index'])->middleware('permission:group.view');
        Route::get('group/{idGroups}', [GroupController::class, 'show'])->middleware('permission:group.view')->whereNumber('idGroups');
        Route::post('group', [GroupController::class, 'store'])->middleware('permission:group.create');
        Route::put('group', [GroupController::class, 'update'])->middleware('permission:group.update');
        Route::delete('group', [GroupController::class, 'destroy'])->middleware('permission:group.delete');
    });

    Route::prefix('persons')->group(function () {
        Route::get('person', [PersonController::class, 'index'])->middleware('permission:person.view');
        Route::get('person/{idPerson}', [PersonController::class, 'show'])->middleware('permission:person.view')->whereNumber('idPerson');
        Route::post('person', [PersonController::class, 'store'])->middleware('permission:person.update');
        Route::put('person', [PersonController::class, 'update'])->middleware('permission:person.update');
        Route::delete('person', [PersonController::class, 'destroy'])->middleware('permission:person.update');
    });

    Route::prefix('person/groups')->group(function () {
        Route::post('/', [PersonGroupController::class, 'store'])->middleware('permission:group.update');
        Route::put('/', [PersonGroupController::class, 'update'])->middleware('permission:group.update');
        Route::delete('/', [PersonGroupController::class, 'destroy'])->middleware('permission:group.update');
        Route::put('admin', [PersonGroupController::class, 'setAdmin'])->middleware('permission:group.update');
        Route::put('status', [PersonGroupController::class, 'setStatus'])->middleware('permission:group.update');
    });

    Route::prefix('appsettings')->group(function () {
        Route::get('/', [AppSettingController::class, 'show'])->middleware('permission:task.view');
        Route::post('/', [AppSettingController::class, 'store'])->middleware('permission:task.view');
        Route::put('/', [AppSettingController::class, 'update'])->middleware('permission:task.view');
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'summary'])->middleware('permission:task.view');
        Route::get('tickets', [DashboardController::class, 'ticketStats'])->middleware('permission:ticket.view');
        Route::get('tasks', [DashboardController::class, 'taskStats'])->middleware('permission:task.view');
    });

    Route::prefix('tasks')->group(function () {
        Route::get('task', [TaskController::class, 'index'])->middleware('permission:task.view');
        Route::get('task/{id}/details', [TaskController::class, 'show'])->middleware('permission:task.view')->whereNumber('id');
        Route::post('task', [TaskController::class, 'store'])->middleware('permission:task.create');
        Route::put('task', [TaskController::class, 'update'])->middleware('permission:task.update');
        Route::delete('task', [TaskController::class, 'destroy'])->middleware('permission:task.delete');

        Route::get('{taskId}/subtasks', [SubTaskController::class, 'index'])->middleware('permission:task.view')->whereNumber('taskId');
        Route::post('{taskId}/subtasks', [SubTaskController::class, 'store'])->middleware('permission:task.create')->whereNumber('taskId');
        Route::put('{taskId}/subtasks', [SubTaskController::class, 'update'])->middleware('permission:task.update')->whereNumber('taskId');
        Route::delete('{taskId}/subtasks', [SubTaskController::class, 'destroy'])->middleware('permission:task.delete')->whereNumber('taskId');

        Route::get('task/{taskId}/comments', [TaskCommentController::class, 'index'])->middleware('permission:task.view')->whereNumber('taskId');
        Route::post('task/{taskId}/comments', [TaskCommentController::class, 'store'])->middleware('permission:task.create')->whereNumber('taskId');
        Route::put('task/comments', [TaskCommentController::class, 'update'])->middleware('permission:task.update');
        Route::delete('task/comments', [TaskCommentController::class, 'destroy'])->middleware('permission:task.delete');
    });

    Route::prefix('tickets')->group(function () {
        Route::get('ticket', [TicketController::class, 'index'])->middleware('permission:ticket.view');
        Route::get('ticket/{id}/details', [TicketController::class, 'show'])->middleware('permission:ticket.view')->whereNumber('id');
        Route::post('ticket', [TicketController::class, 'store'])->middleware('permission:ticket.create');
        Route::put('ticket', [TicketController::class, 'update'])->middleware('permission:ticket.create');
        Route::put('approve', [TicketController::class, 'approve'])->middleware('permission:ticket.approve');
        Route::delete('ticket', [TicketController::class, 'destroy'])->middleware('permission:ticket.delete');
    });

    Route::prefix('transactions')->group(function () {
        Route::get('transaction/{id}/details', [TransactionController::class, 'show'])->middleware('permission:transaction.view');
    });

    Route::prefix('products')->group(function () {
        Route::get('product', [ProductController::class, 'index'])->middleware('permission:product.view');
        Route::post('product', [ProductController::class, 'store'])->middleware('permission:product.create');
        Route::put('product', [ProductController::class, 'update'])->middleware('permission:product.update');
        Route::delete('product', [ProductController::class, 'destroy'])->middleware('permission:product.delete');

        Route::post('product/import', [ProductController::class, 'import'])->middleware('permission:product.create');
        Route::get('product/export', [ProductController::class, 'export'])->middleware('permission:product.view');
        Route::get('product/{id}', [ProductController::class, 'show'])->middleware('permission:product.view')->whereNumber('id');
    });

    Route::prefix('Uploads')->group(function () {
        Route::post('Upload', [UploadController::class, 'store'])->middleware('permission:task.create');
    });
});
