<?php

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

Route::get('file/disk/{storage}/{objectId}', "Api\Components\DiskController@viewObjectFile");
Route::get('file/disk/downloadFile/{storage}/{objectId}', "Api\Components\DiskController@downloadObjectFile");

Route::get('file/{moduleId}/{fileId}', "Api\Components\DiskController@viewFile");
Route::get('file/{moduleId}/downloadFile/{fileId}', "Api\Components\DiskController@downloadFile");

Route::get('file1/{moduleId}/{fileId}', "Api\GetFileController@viewFile");


/*
Route::get('/', "View\Ik\DefaultController@index")->name("home");
Route::get('ik/employee/list', "View\Ik\DefaultController@employee_list")->name("employee_list");
Route::get('calendar', "View\Ik\DefaultController@calendar")->name("calendar");
Route::get('settings', "View\Ik\DefaultController@settings")->name("settings");
Route::get('processes', "View\Ik\DefaultController@processes")->name("processes");
Route::get('projects', "View\Ik\DefaultController@projects")->name("projects");
Route::get('structure', "View\Ik\DefaultController@structure")->name("structure");

Route::get('login', "View\AuthController@login")->name("login");
Route::get('logout', "View\AuthController@logout")->name("logout");
Route::post('login', "View\AuthController@loginPost")->name("loginPost");

//Employee Route
Route::namespace("View\Ik")->group(function(){
    Route::prefix("ik/employee")->group(function(){
        Route::get('add', "DefaultController@employee_add")->name("employee_add");
        Route::get('edit/general/{id}', "DefaultController@employee_edit")->name("employee_edit");
        Route::get('edit/career/{id}', "DefaultController@employee_edit_career")->name("employee_edit_career");
        Route::get('edit/personal-info/{id}', "DefaultController@employee_edit_personal_info")->name("employee_edit_personal_info");
        Route::get('edit/more-info/{id}', "DefaultController@employee_edit_more_info")->name("employee_edit_more_info");
        Route::get('edit/trainings/{id}', "DefaultController@employee_edit_trainings")->name("employee_edit_trainings");
        Route::get('edit/isg-trainings/{id}', "DefaultController@employee_edit_isg_trainings")->name("employee_edit_isg_trainings");
        Route::get('edit/assets/{id}', "DefaultController@employee_edit_assets")->name("employee_edit_assets");
        Route::get('edit/user-groups/{id}', "DefaultController@employee_edit_user_groups")->name("employee_edit_user_groups");
    });
});


//Processes Route
Route::namespace("View\Processes")->group(function(){

    //Expense User
    Route::prefix("processes/expense")->group(function(){
        Route::get('list/{expense_id?}', "ExpenseController@expense_list")->name("expense_list")->where(['expense_id' => '[0-9]+']);
        Route::get('add', "ExpenseController@expense_add")->name("expense_add");
        Route::post('add', "ExpenseController@expense_add_post")->name("expense_add_post");
        Route::get('view/{expense_id}', "ExpenseController@expense_view")->name("expense_view");
        Route::get('print/{expense_id}', "ExpenseController@expense_print")->name("expense_print");
        Route::get('add-document/{expense_id}/{document_id?}', "ExpenseController@expense_add_document")->name("expense_add_document");
    });

    //Expense Manager
    Route::prefix("processes/expense/manager")->group(function(){
        Route::get('list', "ExpenseController@expense_manager_list")->name("expense_manager_list");
        Route::get('view/{expense_id}', "ExpenseController@expense_manager_view")->name("expense_manager_view");
        Route::get('document-pending/{expense_id}', "ExpenseController@expense_document_pending")->name("expense_document_pending");
    });

    //Expense Accounting
    Route::prefix("processes/expense/accounting")->group(function(){
        Route::get('list', "ExpenseController@expense_accounting_list")->name("expense_accounting_list");
        Route::get('view/{expense_id}', "Processes\ExpenseController@expense_accounting_view")->name("expense_accounting_view");
    });

    //Leave
    Route::prefix("processes/leave")->group(function(){
        Route::get('list', "LeaveController@list")->name("leave_list");
        Route::get('add', "LeaveController@leave_add")->name("leave_add");
        Route::get('view/{leave_id?}', "LeaveController@leave_view")->name("leave_view");

        Route::get('logs/{leave_id?}', "LeaveController@leave_logs")->name("leave_logs");

        Route::get('approval/list/{type?}', "LeaveController@approval_list")->name("leave_approval_list");
        Route::get('approval/view/{leave_id?}', "LeaveController@leave_approval_view")->name("leave_approval_view");
        Route::get('approval/edit/{leave_id?}', "LeaveController@leave_approval_edit")->name("leave_approval_edit");
    });

    //Overtime
    Route::prefix("processes/overtime")->group(function(){
        Route::get('list', "OvertimeController@list")->name("overtime_list");
        Route::get('add', "OvertimeController@overtime_add")->name("overtime_add");
        Route::get('view/{leave_id?}', "OvertimeController@overtime_view")->name("overtime_view");

        Route::get('logs/{leave_id?}', "OvertimeController@overtime_logs")->name("overtime_logs");

        Route::get('approval/list/{type?}', "OvertimeController@approval_list")->name("overtime_approval_list");
        Route::get('approval/view/{leave_id?}', "OvertimeController@overtime_approval_view")->name("overtime_approval_view");
        Route::get('approval/edit/{leave_id?}', "OvertimeController@overtime_approval_edit")->name("overtime_approval_edit");
    });
});*/

