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
    Route::post('auth/logout', "AuthController@logout")->name("apilogout");
    Route::post('auth/loginCheck', "AuthController@loginCheck")->name("apiloginCheck");
    Route::get('user/getUser/{user_id?}', "UserController@getUser")->name("apigetUser")->where(['user_id' => '[0-9]+']);
    Route::post('file/connectUpload', "FileController@connectUpload");


    Route::namespace('Processes')->group(function (){

        Route::get('processes/usersProject/list', "ProjectController@projectListOfUser")->name("project_projectListOfUser");
        Route::get('processes/project', "ProjectController@getProject")->name("project_by_id");
        Route::get('processes/categoriesOfProject/list', "ProjectController@categoryListOfProject")->name("categories_categoriesOfProjects");
        Route::get('processes/project/carList','ProjectController@getProjectCars')->name("project_cars");

        Route::prefix('processes/expense')->group(function () {
            Route::get('monthlyAll', "ExpenseController@expenseAllMonthly")->name("expense_allMonthly");
            Route::get('list', "ExpenseController@expenseList")->name("expense_expenseList");
            Route::post('expenseSave', "ExpenseController@expenseSave")->name("expense_expenseSave");
            Route::post('restartExpenseProcess', "ExpenseController@restartExpenseProcess")->name("expense_restart_process");
            Route::post('documentSave', "ExpenseController@documentSave")->name("expense_documentSave");
            Route::post('documentElementSave', "ExpenseController@documentElementSave")->name("document_elementsave");
            Route::get('getExpense', "ExpenseController@getExpense")->name("expense_getExpense");
            Route::get('getExpenseDocument', "ExpenseController@getExpenseDocument")->name("expense_getExpenseDocument");
            Route::get('expenseDocumentList', "ExpenseController@expenseDocumentList")->name("expense_expenseDocumentList");
            Route::get('expensePendingList', "ExpenseController@expensePendingList")->name("expense_expensePendingList");
            Route::get('expensePendingListCount', "ExpenseController@expensePendingListCount")->name("expense_expensePendingListCount");
            Route::get('printExpense',"ExpenseController@printExpense")->name("expense_print");
            Route::delete('expenseDelete', "ExpenseController@expenseDelete")->name("expense_expenseDelete");
            Route::delete('deleteDocument', "ExpenseController@deleteDocument")->name("expense_deleteDocument");
            Route::delete('deleteElement', "ExpenseController@deleteElement")->name("expense_deleteElement");
            Route::put('userTakeBack', "ExpenseController@userTakeBack")->name("expense_userTakeBack");
            Route::put('expenseDocumentConfirm', "ExpenseController@expenseDocumentConfirm")->name("expense_expenseDocumentConfirm");
            Route::put('documentConfirmTakeBack', "ExpenseController@documentConfirmTakeBack")->name("expense_documentConfirmTakeBack");
            Route::put('expenseComplete', "ExpenseController@expenseComplete")->name("expense_expenseComplete");

            Route::get('getCurrent', "ExpenseController@getCurrent")->name("expense_getCurrent");
            Route::post('currentSave', "ExpenseController@currentSave")->name("expense_currentSave");
            Route::post('SendCurrentToNetsis', "ExpenseController@SendCurrentToNetsis")->name("expense_SendCurrentToNetsis");
            Route::post('SendExpenseToNetsis', "ExpenseController@SendExpenseToNetsis")->name("expense_SendExpenseToNetsis");
            Route::get('listNetsisCurrent', "ExpenseController@listNetsisCurrent")->name("expense_listNetsisCurrent");

            Route::get('getCrmProjectCode', "ExpenseController@getCrmProjectCode")->name("expense_getCrmProjectCode");
            Route::get('getParaBirimleri', "ExpenseController@getParaBirimleri")->name("expense_getParaBirimleri");
            Route::get('getMuhasebeGiderHesaplari', "ExpenseController@getMuhasebeGiderHesaplari")->name("expense_getMuhasebeGiderHesaplari");
            Route::get('getAccountBalance', "ExpenseController@getAccountBalance")->name("expense_getAccountBalance");
            Route::get('listDocumentTypes', "ExpenseController@listDocumentTypes")->name("expense_listDocumentTypes");
            Route::get('listTypes', "ExpenseController@listTypes")->name("expense_listTypes");
            Route::get('listExpenseAccountCodes', "ExpenseController@listExpenseAccountCodes")->name("expense_listExpenseAccountCodes");
            Route::get('getDocumentType', "ExpenseController@getDocumentType")->name("expense_getDocumentType");
            Route::get('getType', "ExpenseController@getType")->name("expense_getType");

            Route::get('listTaxOffice', "ExpenseController@listTaxOffice")->name("expense_listTaxOffice");
            Route::post('isEmployeesManager', "ExpenseController@isLoggedPersonIsEmployeeManager")->name("expense_isEmployeeManager");
            Route::post('isManagerApprovedAllDocuments', "ExpenseController@isManagerApprovedAllDocuments")->name("expense_isManagerApprovedAllDocuments");
            Route::post('isProjectManagerApprovedAllDocuments', "ExpenseController@isProjectManagerApprovedAllDocuments")->name("expense_isProjectManagerApprovedAllDocuments");
            Route::post('isProjectManager', "ExpenseController@isLoggedPersonProjectManager")->name("expense_isLoggedPersonProjectManager");
            Route::post('isAccounterApprovedAllDocuments', "ExpenseController@isAccounterApprovedAllDocuments")->name("expense_isAccounterApprovedAllDocuments");
            Route::get('loggedUsersAuthorizations', "ExpenseController@loggedUsersAuthorizations")->name("expense_loggedUsersAuthorizations");
            //Route::get('test', "ExpenseController@test")->name("expense_test");
        });

        Route::prefix('processes/permit/')->group(function () {
            Route::get('getPermitById','PermitController@getPermitById')->name('permit_getById');
            Route::post('savePermit', 'PermitController@savePermit')->name('permit_savePermit');
            Route::get('getPermitTypes', 'PermitController@permitTypes')->name('permit_getPermitTypes');
            Route::get('permitList', 'PermitController@permitList')->name('permit_permitList');
            Route::get('permitListManager', 'PermitController@permitListManager')->name('permit_permitListManager');
            Route::get('getRemainingYearlyPermit', 'PermitController@getRemainingYearlyPermit')->name('permit_getRemainingYearlyPermit');
            Route::get('permitListHR', 'PermitController@permitListHR')->name('permit_permitListHR');
            Route::get('permitListPS', 'PermitController@permitListPS')->name('permit_permitListPS');
            Route::get('getPermit', 'PermitController@getPermit')->name('permit_getPermit');
            Route::get('getTransferPersons', 'PermitController@getTransferPersons')->name('permit_getTransferPersons');
            Route::get('permitPendingList', 'PermitController@permitPendingList')->name('permit_permitPendingList');
            Route::put('permitConfirm', 'PermitController@permitConfirm')->name('permit_permitConfirm');
            Route::put('permitConfirmTakeBack', 'PermitController@permitConfirmTakeBack')->name('permit_permitConfirmTakeBack');
            Route::delete('deletePermit', "PermitController@deletePermit")->name("delete_permit");
        });


        Route::prefix('processes/AdvancePayment/')->group(function () {

            Route::post('save', 'AdvancePaymentController@save')->name('AdvancePayment_save');
            Route::get('list', 'AdvancePaymentController@list')->name('AdvancePayment_list');
            Route::get('list2', 'AdvancePaymentController@list2')->name('AdvancePayment_list2');
            Route::get('getAdvance', 'AdvancePaymentController@getAdvance')->name('AdvancePayment_getAdvance');
            Route::get('listPending', 'AdvancePaymentController@listPending')->name('AdvancePayment_listPending');
            Route::put('confirmTakeBack', 'AdvancePaymentController@confirmTakeBack')->name('AdvancePayment_confirmTakeBack');
            Route::put('confirm', 'AdvancePaymentController@confirm')->name('AdvancePayment_confirm');
            Route::put('complete', 'AdvancePaymentController@complete')->name('AdvancePayment_complete');
            Route::delete('deleteAdvance', 'AdvancePaymentController@deleteAdvance')->name('AdvancePayment_deleteAdvance');
            Route::post('SendNetsis', 'AdvancePaymentController@SendNetsis')->name('AdvancePayment_SendNetsis');
            Route::get('BankCodes', 'AdvancePaymentController@BankCodes')->name('AdvancePayment_BankCodes');
        });

        Route::prefix('processes/Overtime/')->group(function () {
            Route::get('getOvertimeKindByDate','OvertimeController@getOvertimeKindByDate')->name('overtime_kind_by_date');
            Route::get('getOvertimeHRReports','OvertimeController@getOvertimeHRReports')->name('overtime_hr_reports');
            Route::get('getOvertimeHRReports2','OvertimeController@getOvertimeHRReports2')->name('overtime_hr_reports2');
            Route::get('getOvertimeById','OvertimeController@getOvertimeById')->name('overtime_getById');
            Route::get('getCarLocation','OvertimeController@getCarLocation')->name('overtime_getCarLocation');
            Route::get('Employees/all','OvertimeController@getEmployeesOvertimeRequests')->name('overtime_employee_all');
            Route::get('Employees/all2','OvertimeController@getEmployeesOvertimeRequests2')->name('overtime_employee_all2');
            Route::get('Managers/all','OvertimeController@getManagersOvertimeRequests')->name('overtime_manager_all');
            Route::get('Managers/all2','OvertimeController@getManagersOvertimeRequests2')->name('overtime_manager_all2');
            Route::get('statuses','OvertimeController@getOvertimeStatuses')->name('overtime_statuses');
            Route::get('hrEmployees','OvertimeController@getHREmployees')->name('overtime_hr_employees');
            Route::get('managersEmployees','OvertimeController@getManagersEmployees')->name('overtime_managers_employees');
            Route::get('employeesManagers','OvertimeController@getEmployeesManagers')->name('overtime_employees_managers');
            Route::get('overtimeKinds','OvertimeController@overtimeKinds')->name('overtime_kinds');
            Route::get('overtimeLimits','OvertimeController@getOvertimeLimits')->name('overtime_limits');
            Route::get('managersProjectList','OvertimeController@managersProjectList')->name('overtime_managers_project_list');
            Route::post('saveOvertimeRequest','OvertimeController@saveOvertimeRequest')->name('save_overtime_request');
            Route::post('overtimeReportsToExcel','OvertimeController@overtimeReportsToExcel')->name('overtime_reports_to_excel');
        });

        Route::prefix('processes/CarNotify/')->group(function () {
            Route::get('employees','CarNotifyController@getEmployeeList')->name('carnotify_employee_all');
            Route::get('notifyKinds','CarNotifyController@getNotifyKinds')->name('carnotify_notify_kinds');
            Route::get('carPlates','CarNotifyController@getCarPlates')->name('carnotify_notify_kinds');
            Route::get('regions','CarNotifyController@getRegions')->name('carnotify_getRegions');
            Route::get('cities','CarNotifyController@getCities')->name('carnotify_getCities');
            Route::get('issueKinds','CarNotifyController@getIssueKinds')->name('carnotify_getIssueKinds');
            Route::get('carDefects','CarNotifyController@getCarDefects')->name('carnotify_getCarDefects');
            Route::get('ticketCode','CarNotifyController@getTicketCode')->name('carnotify_getTicketCode');

            Route::post('saveCarNotify','CarNotifyController@saveCarNotify')->name('carnotify_saveCarNotify');
        });

        Route::prefix('processes/itsupport/')->group(function () {
            Route::get('employees','ItSupportController@getEmployeeList');
            Route::get('supportCategories','ItSupportController@supportCategories');
            Route::get('requestTypes','ItSupportController@requestTypes');
            Route::get('priority','ItSupportController@priority');
            Route::get('ticketCode','ItSupportController@getTicketCode')->name('itsupport_getTicketCode');

            Route::post('supportSave','ItSupportController@supportSave');
        });

        Route::prefix('processes/contactUs/')->group(function () {
            Route::post('saveContactUs','ContactUsController@saveContactUs');
            Route::get('contactUsTypes','ContactUsController@getContactUsTypes');
        });

    });




    Route::namespace("Ik")->group(function(){

        Route::prefix('ik')->group(function () {

            Route::post("employee/toExcel", "EmployeeController@toExcel")->name("employee_to_excel");
            Route::post('employee/verifySMSCode', "EmployeeController@verifySMSCode")->name("verifySMSCode");
            Route::get('employee/sendSMSCode', "EmployeeController@sendSMSCode")->name("sendSMSCode");
            Route::get('employee/searchEmployee', "EmployeeController@searchEmployees")->name("search_employees");
            Route::get('employee/all', "EmployeeController@allEmployees")->name("all_employees");
            Route::get('employee/employeeFullRecorded', "EmployeeController@employeeFullRecorded")->name("is_employee_full_recorded");
            Route::get('employee/{id}', "EmployeeController@getEmployeeById")->where(['id' => '[0-9]+'])->name("get_employee_byid");
            Route::get('employee/getEmployee', "EmployeeController@getEmployeeById2")->name("get_employee_byid2");
            Route::get('employee/general-informations/{id}', "EmployeeController@getGeneralInformationsOfEmployeeById")->where(['id' => '[0-9]+'])->name("get_employee_general_informations");
            Route::get('employee/general-informations/fields', "EmployeeController@getGeneralInformationFields")->name('fields_of_general_informations');
            Route::get('employee/position-informations/{id}', "PositionController@getJobPositionInformations")->where(['id' => '[0-9]+'])->name("get_employee_position_informations");
            Route::get('employee/payment-informations/{id}', "PaymentController@getPaymentsOfEmployee")->where(['id' => '[0-9]+'])->name("get_employee_payment_informations");
            Route::get('employee/payment-informations/additional/{id}', "PaymentController@getAdditionalPaymentsOfPayment")->where(['id' => '[0-9]+'])->name("get_additional_payment_of_payment");
            Route::get('employee/contact-informations/{id}', "EmployeeController@getContactInformationsOfEmployee")->where(['id' => '[0-9]+'])->name("get_employee_payment_informations");
            Route::get('employee/position-informations/fields', "PositionController@getJobPositionInformationFields")->name('fields_of_pos_information');
            Route::get('employee/payment-informations/fields', "PaymentController@getPaymentInformationFields")->name('fields_of_payment_information');
            Route::get('employee/location-informations/{id}', "LocationController@getLocation")->where(['id' => '[0-9]+'])->name("get_employee_location_informations");
            Route::get('employee/location-informations/fields', "LocationController@getLocationInformationFields")->name("get_location_fields");
            Route::get('employee/education-informations/fields', "EducationController@getEducationInformationFields")->name("get_education_fields");
            Route::get('employee/education-informations/{id}', "EducationController@getEducationInformations")->where(['id' => '[0-9]+'])->name("get_employee_educuation_informations");
            Route::get('employee/agi-informations/fields', "AGIController@getAGIInformationFields")->name("get_agi_fields");
            Route::get('employee/agi-informations/{id}', "AGIController@getAgiInformations")->name("get_employee_agi_informations");
            Route::get('employee/driving-license-informations/{id}', "DrivingLicenseController@getDrivingLicense")->where(['id' => '[0-9]+'])->name("get_employee_driving_license_informations");
            Route::get('employee/driving-license-informations/fields', "DrivingLicenseController@getDrivingLicenseFields")->name("get_driving_license_fields");
            Route::delete('employee/drivingLicense/delete', "DrivingLicenseController@deleteDrivingLicenseInfo")->name("driving_license_delete");
            Route::get('employee/drivingLicenseClasses', "DrivingLicenseController@getDrivingLicenseClasses")->name("get_driving_license_classes");
            Route::get('employee/emergency-informations/{id}', "EmergencyFieldController@getEmergencyInformations")->where(['id' => '[0-9]+'])->name("get_employee_emergency_informations");
            Route::get('employee/emergency-informations/fields', "EmergencyFieldController@getEmergencyInformationFields")->name("get_emergency_fields");
            Route::get('employee/body-measurements/fields', "BodyMeasurementsController@getBodyMeasurementsFields")->name("get_body_measurements_fields");
            Route::get('employee/body-measurements/{id}', "BodyMeasurementsController@getBodyMeasurements")->where(['id' => '[0-9]+'])->name("get_employee_body_measurements");
            Route::get('employee/id-card/fields', "IDCardController@getIDCardFields")->name("get_id_card_fields");
            Route::get('employee/id-card/{id}', "IDCardController@getIDCard")->where(['id' => '[0-9]+'])->name("get_employee_id_card");
            Route::get('employee/ssi/fields', "SocialSecurityInformationController@getSSInformationFields")->name("get_ssi_fields");
            Route::get('employee/ssi/{id}', "SocialSecurityInformationController@getSSInformations")->where(['id' => '[0-9]+'])->name("get_employee_ssi");
            //Route::get('employee/bank/fields', "EmployeeBankController@getSSInformationFields")->name("get_ssi_fields");
            Route::get('employee/bank', "EmployeeBankController@getEmployeeBankInformations")->name("get_employee_bank");
            Route::get('employee/banks', "EmployeeBankController@getBanks")->name("get_banks");
            Route::get('employee/children', "EmployeeController@getEmployeesChildren")->where(['id' => '[0-9]+'])->name("get_employees_children");
            Route::get('employee/activeDirectoryAccount', "EmployeeController@getEmployeesActiveDirectoryAccount")->name("get_employees_activeDirectoryAccount");

            Route::post('employee/sendActiveDirectoryCreateRequestMail', "EmployeeController@sendActiveDirectoryCreateRequestMail")->name("send_activeDirectoryCreateRequest");

            Route::post('employee/child/save', "EmployeeController@saveEmployeesChild")->name("save_employees_child");
            Route::delete('employee/child/delete', "EmployeeController@deleteEmployeesChild")->name("delete_employees_children");
            Route::post('employee/add', "EmployeeController@addEmployee")->name("add_employee");

            Route::post('employee/delete', "EmployeeController@deleteEmployee")->name("delete_employee");
            Route::delete('employee/destroy', "EmployeeController@destroyEmployee")->name("destroy_employee");

            Route::post('employee/general-informations/save/{id}', "EmployeeController@saveGeneralInformations")->where(['id' => '[0-9]+'])->name("employee_general_informations");
            Route::post('employee/general-informations/other/save/{id}', "EmployeeController@saveOtherGeneralInformations")->where(['id' => '[0-9]+'])->name("employee_other_general_informations");

            Route::post('employee/contact-information/save', "EmployeeController@saveContactInformation")->name("employee_contact_information");

            Route::post('employee/job-position/edit/{id}/{positionId}', "PositionController@editJobPosition")->where(['id' => '[0-9]+'])->name("employee_edit_job_position");
            Route::post('employee/job-position/save', "PositionController@saveJobPosition")->name("employee_save_job_position");
            Route::delete('employee/job-position/delete',"PositionController@deleteJobPosition")->name("position_delete");
            Route::post('employee/payment/edit/{id}/{paymentId}', "PaymentController@editPayment")->where(['id' => '[0-9]+'])->name("employee_edit_payment");
            Route::post('employee/payment/save', "PaymentController@savePayment")->name("save_payment");
            Route::post('employee/payment/delete', "PaymentController@deletePayment")->name("employee_delete_payment");

            Route::post('employee/additional-payment/save/{id}', "PaymentController@savePayment")->name("save_additional_payment");

            Route::post('employee/location/save', "LocationController@saveLocation")->name("save_location");

            Route::post('employee/education/save', "EducationController@saveEducation")->name("save_education");
            Route::delete('employee/education/delete', "EducationController@deleteEducation")->name("delete_education");
            Route::post('employee/education/document/save', "EducationController@saveEducationDocument")->name("save_education_document");
            Route::post('employee/education/document/download', "EducationController@downloadEducationDocument")->name("download_education_document");


            Route::post('employee/driving-license/save', "DrivingLicenseController@saveDrivingLicense")->name("save_driving_license");

            Route::post('employee/agi/save', "AGIController@saveAgi")->name("save_agi");

            Route::post('employee/emergency-field/save', "EmergencyFieldController@saveEmergencyField")->name("save_emergency_field");

            Route::post('employee/body-measurements/save', "BodyMeasurementsController@saveBodyMeasurements")->name("save_body_measurements");

            Route::post('employee/id-card/save', "IDCardController@saveIDCard")->name("save_id_card");

            Route::post('employee/ssi/save', "SocialSecurityInformationController@saveSocialSecurityInformation")->name("save_ssi");

            Route::post('employee/bank/save', "EmployeeBankController@saveEmployeeBank")->name("save_bank");

            Route::get('employee/position-informations/serviceCodes', "PositionController@getServiceCodes")->name('get_service_codes');


        });

    });

    Route::namespace('Common')->group(function (){

        Route::prefix('common')->group(function () {
            Route::get('cities', "CityController@getCities")->name("get_cities_of_country");
            Route::post('country/cities', "CountryController@getCitiesOfCountry")->name("get_cities_of_country");
            Route::post('cities/districts', "CityController@getDistrictsOfCity")->name("get_districts_of_city");
            Route::get('authorizations', "AuthorityController@loggedUserAuthorizations")->name("user_authorities");
            Route::post('objectFile/setObjectFile', "ObjectFileController@setObjectFile")->name("ObjectFileController_setObjectFile");

            Route::get('notifications', "NotificationController@getNotifications")->name("get_notifications");
            Route::get('notificationsCount', "NotificationController@getNotificationsCount")->name("get_notifications_count");
            Route::post('notificationRead', "NotificationController@notificationRead")->name("read_notification");
            Route::delete('notificationDelete', "NotificationController@deleteNotification")->name("delete_notification");

            Route::get('cars', "CarController@getCars")->name("get_cars");

            Route::get('departmentsContactUs', "DepartmentController@getDepartmentsContactUs")->name("departments_contact_us");

        });

    });
    Route::namespace("Components")->group(function() {
        Route::prefix('disk')->group(function () {
            Route::get('getStorage', "DiskController@getStorage")->name("disk_getStorage");
            Route::get('getFoldersAndFiles', "DiskController@getFoldersAndFiles")->name("disk_getFoldersAndFiles");
            Route::post('downloadFile', "DiskController@downloadFile")->name("disk_downloadFile");
            Route::post('viewFile', "DiskController@viewFile")->name("disk_viewFile");
            Route::post('addObjectFile', "DiskController@addObjectFile")->name("disk_addObjectFile");
            Route::post('addObjectFolder', "DiskController@addObjectFolder")->name("disk_addObjectFolder");
            Route::delete('deleteFile', "DiskController@deleteFile")->name("disk_deleteFile");

            Route::post('addFile', "DiskController@addFile")->name("disk_addFile");
            Route::get('getFile', "DiskController@getFile")->name("disk_getFile");
        });

        Route::prefix('stream')->group(function () {
            Route::get('streamList', "StreamController@streamList")->name("stream_streamList");
            Route::post('SaveStream', "StreamController@SaveStream")->name("stream_SaveStream");
            Route::get('toList', "StreamController@toList")->name("stream_toList");
            Route::get('categoryList', "StreamController@categoryList")->name("stream_categoryList");
            Route::delete('deleteStream', "StreamController@deleteStream")->name("stream_deleteStream");
        });
    });


});
