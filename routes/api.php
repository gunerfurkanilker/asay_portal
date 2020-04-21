<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace("Api")->group(function(){

    Route::post('auth/login', "AuthController@loginPost")->name("apiloginPost");
    Route::post('auth/loginCheck', "AuthController@loginCheck")->name("apiloginCheck");
    Route::get('user/getUser/{user_id?}', "UserController@getUser")->name("apigetUser")->where(['user_id' => '[0-9]+']);


    Route::get('processes/expense/list', "Processes\ExpenseController@expenseList")->name("expense_expenseList");
    Route::post('processes/expense/expenseSave', "Processes\ExpenseController@expenseSave")->name("expense_expenseSave");
    Route::get('processes/expense/getExpense', "Processes\ExpenseController@getExpense")->name("expense_getExpense");
    Route::post('processes/expense/addDocument', "Processes\ExpenseController@addDocument")->name("expense_addDocument");
    Route::get('processes/expense/getExpenseDocuments', "Processes\ExpenseController@getExpenseDocuments")->name("expense_getExpenseDocuments");

    Route::post('processes/expense/cariEkle', "Processes\ExpenseController@cariEkle")->name("expense_cariEkle");


    Route::get('processes/expense/getCrmProjectCode', "Processes\ExpenseController@getCrmProjectCode")->name("expense_getCrmProjectCode");
    Route::get('processes/expense/getParaBirimleri', "Processes\ExpenseController@getParaBirimleri")->name("expense_getParaBirimleri");
    Route::get('processes/expense/getMuhasebeGiderHesaplari', "Processes\ExpenseController@getMuhasebeGiderHesaplari")->name("expense_getMuhasebeGiderHesaplari");
    Route::get('processes/expense/getAccountBalance', "Processes\ExpenseController@getAccountBalance")->name("expense_getAccountBalance");

    Route::namespace("Ik")->group(function(){

        Route::prefix('ik')->group(function () {

            Route::get('employee/all', "EmployeeController@allEmployees")->name("all_employees");
            Route::get('employee/{id}', "EmployeeController@getEmployeeById")->name("employee");
            Route::get('employee/general-informations/{id}', "EmployeeController@getGeneralInformationsOfEmployee")->name("employee_general_informations");

            Route::post('employee/general-informations/save/{id}', "EmployeeController@saveGeneralInformations")->where(['id' => '[0-9]+'])->name("employee_general_informations");
            Route::post('employee/job-position/save/{id}', "EmployeeController@saveJobPosition")->where(['id' => '[0-9]+'])->name("employee_job_position");
            Route::post('employee/contact-information/save/{id}', "EmployeeController@saveContactInformation")->where(['id' => '[0-9]+'])->name("employee_contact_information");
            Route::post('employee/payment/save/{id}', "PaymentController@savePayment")->name("save_payment");
            Route::post('employee/additional-payment/save/{id}', "PaymentController@savePayment")->name("save_additional_payment");
            Route::post('employee/location/save/{id}', "LocationController@saveLocation")->name("save_location");
            Route::post('employee/education/save/{id}', "EducationController@saveEducation")->name("save_education");
            Route::post('employee/driving-license/save/{id}', "DrivingLicenseController@saveDrivingLicense")->name("save_driving_license");
            Route::post('employee/agi/save/{id}', "AGIController@saveAgi")->name("save_agi");
            Route::post('employee/emergency-field/save/{id}', "EmergencyFieldController@saveEmergencyField")->name("save_emergency_field");
            Route::post('employee/body-measurements/save/{id}', "BodyMeasurementsController@saveBodyMeasurements")->name("save_body_measurements");
            Route::post('employee/id-card/save/{id}', "IDCardController@saveIDCard")->name("save_id_card");
            Route::post('employee/ssi/save/{id}', "SocialSecurityInformationController@saveSocialSecurityInformation")->name("save_ssi");
            Route::post('employee/bank/save/{id}', "EmployeeBankController@saveEmployeeBank")->name("save_bank");


        });

    });



});
