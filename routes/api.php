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
            Route::get('employee/{id}', "EmployeeController@getEmployeeById")->name("get_employee_byid");
            Route::get('employee/general-informations/{id}', "EmployeeController@getGeneralInformationsOfEmployeeById")->name("get_employee_general_informations");
            Route::get('employee/position-informations/{id}', "PositionController@getJobPositionInformations")->where(['id' => '[0-9]+'])->name("get_employee_position_informations");
            Route::get('employee/payment-informations/{id}', "PaymentController@getPaymentsOfEmployee")->where(['id' => '[0-9]+'])->name("get_employee_payment_informations");
            Route::get('employee/contact-informations/{id}', "EmployeeController@getContactInformationsOfEmployee")->where(['id' => '[0-9]+'])->name("get_employee_payment_informations");
            Route::get('employee/position-informations/fields', "PositionController@getJobPositionInformationFields")->name('fields_of_pos_information');
            Route::get('employee/payment-informations/fields', "PaymentController@getPaymentInformationFields")->name('fields_of_payment_information');
            Route::get('employee/location-informations/{id}', "LocationController@getLocation")->where(['id' => '[0-9]+'])->name("get_employee_location_informations");
            Route::get('employee/location-informations/fields', "LocationController@getLocationInformationFields")->name("get_location_fields");
            Route::get('employee/education-informations/fields', "EducationController@getEducationInformationFields")->name("get_education_fields");
            Route::get('employee/education-informations/{id}', "EducationController@getEducationInformations")->where(['id' => '[0-9]+'])->name("get_employee_educuation_informations");

            Route::post('employee/general-informations/save/{id}', "EmployeeController@saveGeneralInformations")->where(['id' => '[0-9]+'])->name("employee_general_informations");
            Route::post('employee/contact-information/save', "EmployeeController@saveContactInformation")->name("employee_contact_information");
            Route::post('employee/job-position/edit/{id}/{positionId}', "PositionController@editJobPosition")->where(['id' => '[0-9]+'])->name("employee_edit_job_position");
            Route::post('employee/job-position/add', "PositionController@addJobPosition")->name("employee_add_job_position");
            Route::post('employee/job-position/delete', "PositionController@deleteJobPosition")->name("employee_delete_job_position");
            Route::post('employee/payment/edit/{id}/{paymentId}', "PaymentController@editPayment")->where(['id' => '[0-9]+'])->name("employee_edit_payment");
            Route::post('employee/payment/add', "PaymentController@addPayment")->name("add_payment");
            Route::post('employee/payment/delete', "PaymentController@deletePayment")->name("employee_delete_payment");
            Route::post('employee/additional-payment/save/{id}', "PaymentController@savePayment")->name("save_additional_payment");
            Route::post('employee/location/save', "LocationController@saveLocation")->name("save_location");
            Route::post('employee/education/save', "EducationController@saveEducation")->name("save_education");
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
