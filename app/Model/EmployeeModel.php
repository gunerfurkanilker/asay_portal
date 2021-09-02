<?php

namespace App\Model;


use App\Library\Asay;
use Carbon\Carbon;
use Faker\Provider\Payment;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Employee";
    protected $guarded = [];
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';

    protected $appends = [
        "ContractType",
        'IDCard',
        'Domain',
        'EmployeePosition',
        'AccessTypes',
        'EmployeeGroup',
        'MobilePhone',
        'HomePhone',
        'REMMail',
        'Email',
        'BloodTypeID',
        'IsUnitSupervisor',
        'IsEmployeeManager'
    ];

    public static function toExcelGeneralInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Genel Bilgiler');

        $columns = [
            'T.C Kimlik No',
            'Personel ID',
            'Tam Adı',
            'Kullandığı Adı',
            'Soyadı',
            'Erişim Türü',
            'İş E-Posta',
            'Mobil Telefon (İş)',
            'Dahili Telefon'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }



        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $employeeStaffID = $employee->StaffID;
            $employeeFullName = $employee->FirstName;
            $employeeUsageName = $employee->UsageName;
            $employeeLastName = $employee->LastName;
            $employeeAccessTypes = EmployeeHasGroupModel::where('EmployeeID',$employee->Id)->get();
            $employeeAccessTypeArray = [];
            foreach ($employeeAccessTypes as $employeeAccessType)
            {
                $accessType = UserGroupModel::find($employeeAccessType->group_id);
                array_push($employeeAccessTypeArray,$accessType ? $accessType->name : "");
            }
            $employeeAccessTypes = implode(",",$employeeAccessTypeArray);
            $employeeJobEMail = $employee->JobEmail;
            $employeeJobMobilePhone = $employee->JobMobilePhone;
            $employeeInterPhone = $employee->InterPhone;

            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$employeeStaffID);
            array_push($values,$employeeFullName);
            array_push($values,$employeeUsageName);
            array_push($values,$employeeLastName);
            array_push($values,$employeeAccessTypes);
            array_push($values,$employeeJobEMail);
            array_push($values,$employeeJobMobilePhone);
            array_push($values,$employeeInterPhone);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        return $workSheet;

    }

    public static function toExcelContractInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Sözleşme Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Sözleşme Türü',
            'İşe Başlama Tarihi',
            'Sözleşme Bitiş Tarihi',
            'Çalışma Takvimi'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $contractType = ContractTypeModel::find($employee->ContractTypeID);
            $contractType = $contractType ? $contractType->Sym : '';
            $startDate = $employee->StartDate;
            $endDate = $employee->ContractFinishDate;
            $workingSchedule = WorkingScheduleModel::find($employee->WorkingScheduleID);
            $workingSchedule = $workingSchedule ? $workingSchedule->Sym : '';


            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$contractType);
            array_push($values,$startDate);
            array_push($values,$endDate);
            array_push($values,$workingSchedule);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;

    }

    public static function toExcelPositionInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Pozisyon Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Şirket Adı',
            'Unvan',
            'Organizasyon',
            'Departman',
            'Alt Departman',
            'Birim',
            'Hizmet Kodu',
            'Bölge',
            'Çalıştığı İl',
            'Çalıştığı İlçe',
            'Bağlı Olduğu Ofis',
            'Çalışma Alanı',
            'Yöneticisi',
            'Birim Sorumlusu',
            'Çalışma Şekli',
            'Başlangıç Tarihi',
            'Bitiş Tarihi',
            'Aktif Pozisyonu Mu ?',
            'Asıl Pozisyonu Mu ?',
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }


        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeePositions = EmployeePositionModel::where(['EmployeeID' => $employee->Id])->whereIn("Active", [0,1,2])->get();
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $positionArray = [];
            foreach ($employeePositions as $employeePosition)
            {
                $companyName = $employeePosition->Company ? $employeePosition->Company->Sym :'';
                $title = $employeePosition->Title ? $employeePosition->Title->Sym : '';
                $organization = $employeePosition->Organization ? $employeePosition->Organization->name : '';
                $department = $employeePosition->Department ? $employeePosition->Department->Sym : '';
                $subDepartment = $employeePosition->SubDepartment;
                $unit = $employeePosition->Unit;
                $serviceCode = $employeePosition->ServiceCode;
                $region = $employeePosition->Region ? $employeePosition->Region->Name : '';
                $city = $employeePosition->City ? $employeePosition->City->Sym : '';
                $district = $employeePosition->District ? $employeePosition->District->Sym : '';
                $office = $employeePosition->Office ? $employeePosition->Office->Name : '';
                $workField = $employeePosition->WorkingField ? $employeePosition->WorkingField->Name : '';
                $manager = $employeePosition->Manager ? $employeePosition->Manager->UsageName . ' ' . $employeePosition->Manager->LastName : '';
                $supervisor = $employeePosition->UnitSupervisor ? $employeePosition->UnitSupervisor->UsageName . ' ' . $employeePosition->UnitSupervisor->LastName : '';
                $workingType = $employeePosition->WorkingType ? $employeePosition->WorkingType->Sym : '';
                $startDate = $employeePosition->StartDate;
                $endDate = $employeePosition->EndDate;
                $activePosition = $employeePosition->Active == 1 || $employeePosition->Active == 2  ? 'Evet' : 'Hayır';
                $actualPosition = $employeePosition->Active == 2  ? 'Evet' : 'Hayır';

            }

            array_push($values,$employeeTCKN);
            array_push($values,$companyName);
            array_push($values,$title);
            array_push($values,$organization);
            array_push($values,$department);
            array_push($values,$subDepartment);
            array_push($values,$unit);
            array_push($values,$serviceCode);
            array_push($values,$region);
            array_push($values,$city);
            array_push($values,$district);
            array_push($values,$office);
            array_push($values,$workField);
            array_push($values,$manager);
            array_push($values,$supervisor);
            array_push($values,$workingType);
            array_push($values,$startDate);
            array_push($values,$endDate);
            array_push($values,$activePosition);
            array_push($values,$actualPosition);

            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        return $workSheet;


    }

    public static function toExcelPaymentInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Maaş Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Miktar',
            'Para Birimi',
            'Brüt / Net',
            'Asgari Ücret',
            'Geçerlilik Başlangıç Tarihi',
            'Ödeme Periyodu'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $payment = PaymentModel::where(['EmployeeID' => $employee->Id,'Active' => 1])->first();

            $amount = $payment ? $payment->Pay :'';
            $currency = $payment ? $payment->Currency ?  $payment->Currency->Sym : '' : '';
            $payMethod = $payment ? PayMethodModel::find($payment->PayMethodID) :null;
            $payMethod = $payMethod ? $payMethod->Sym :'';
            $lowestPay = $payment ? $payment->LowestPayID == 1 ? 'Evet' : 'Hayır' : '';
            $startDate = $payment ? $payment->StartDate :'';
            $payPeriod = $payment ? $payment->PayPeriodID : '';


            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$amount);
            array_push($values,$currency);
            array_push($values,$payMethod);
            array_push($values,$lowestPay);
            array_push($values,$startDate);
            array_push($values,$payPeriod);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;
    }

    public static function toExcelAdditionalPaymentInformations($spreadSheet, $employees)
    {

        $workSheet = new Worksheet($spreadSheet, 'Yan Haklar');

        $columns = [
            'T.C Kimlik No',
            'Yardım Türü',
            'Miktar',
            'Para Birimi',
            'Brüt / Net',
            'Bordroya Ekle',
            'Ödeme Periyodu',
            'Açıklama'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }
        $rowNum = 2;
        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $payment = PaymentModel::where(['EmployeeID' => $employee->Id,'Active' => 1])->first();

            if(!$payment)
                continue;

            $additionalPayments = AdditionalPaymentModel::where(["PaymentID" => $payment->Id,'Active' => 1])->get();

            foreach ($additionalPayments as $additionalPayment)
            {
                $asciiCapitalA = 65;
                $values = [];
                $kind = $additionalPayment->AdditionalPaymentType->Sym;
                $amount = $additionalPayment->Pay;
                $currency = CurrencyModel::find($additionalPayment->CurrencyID);
                $currency = $currency ? $currency->Sym : '';
                $payMethod = $additionalPayment->PayMethod == 1 ? 'Brüt' : 'Net';
                $addPayroll = $additionalPayment->AddPayroll == 1 ? 'Evet' : 'Hayır';
                $payPeriod = PayPeriodModel::find($additionalPayment->PayPeriodID);
                $payPeriod = $payPeriod ? $payPeriod->Sym : '';
                $description = $additionalPayment->Description;

                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values,$employeeTCKN);
                array_push($values,$kind);
                array_push($values,$amount);
                array_push($values,$currency);
                array_push($values,$payMethod);
                array_push($values,$addPayroll);
                array_push($values,$payPeriod);
                array_push($values,$description);


                foreach ($columns as $keyColumns => $column)
                {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter.($rowNum),$values[$keyColumns]);
                    $asciiCapitalA++;
                }
                $rowNum++;
            }

        }


        return $workSheet;

    }

    public static function toExcelEducationInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Eğitim Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Eğitim Durumu',
            'Tamamlanan En Yüksek Eğitim Seviyesi',
            'Son Tamamlanan Eğitim Kurumu'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }
        $rowNum = 2;
        foreach ($employees as $keyEmployee => $employee)
        {

            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $educationinfos = EducationModel::where(['EmployeeID' => $employee->Id, 'Active' => 1])->get();

            foreach ($educationinfos as $educationinfo)
            {
                //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
                $asciiCapitalA = 65;
                $values = [];
                $status = $educationinfo->EducationStatus->Sym;
                $level = $educationinfo->EducationLevel->Sym;
                $institution = $educationinfo->Institution;


                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values,$employeeTCKN);
                array_push($values,$status);
                array_push($values,$level);
                array_push($values,$institution);

                foreach ($columns as $keyColumns => $column)
                {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter.($rowNum),$values[$keyColumns]);
                    $asciiCapitalA++;
                }
                $rowNum++;
            }

        }


        return $workSheet;
    }

    public static function toExcelContactInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'İletişim Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Cep Telefonu (Kişisel)',
            'Ev Telefonu (Kişisel)',
            'E-Posta (Kişisel)',
            'KEP Adresi (Kişisel)'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $phone = $employee->MobilePhone;
            $homePhone = $employee->HomePhone;
            $eMail = $employee->Email;
            $kepMail = $employee->REMMail;


            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$phone);
            array_push($values,$homePhone);
            array_push($values,$eMail);
            array_push($values,$kepMail);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;
    }

    public static function toExcelAddressInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'Adres Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Adres',
            'Ülke',
            'Şehir',
            'İlçe',
            'Posta Kodu'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $location = LocationModel::where(['EmployeeID' => $employee->Id])->first();
            $location = $location ? $location : null;

            $address = $location ? $location->Address : '';
            $country = $location ? $location->Country ? $location->Country->Sym : '' : '';
            $city = $location ? $location->City ? $location->City->Sym : '' : '';
            $district =$location ? $location->District ? $location->District->Sym  : '' :'';
            $zipCode = $location ? $location->ZIPCode : '';


            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$address);
            array_push($values,$country);
            array_push($values,$city);
            array_push($values,$district);
            array_push($values,$zipCode);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;

    }

    public static function toExcelAGIInformations($spreadSheet, $employees){
        $workSheet = new Worksheet($spreadSheet, 'Asgari Geçim İndirimi Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Medeni Hali',
            'Eş Çalışma Durumu'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $agiInfo = AgiModel::where("EmployeeID",$employee->Id)->first();


            $maritalStatus = $agiInfo ? $agiInfo->MaritalStatus ? $agiInfo->MaritalStatus->Sym : '' : '' ;
            $spouseWorkingStatus = $agiInfo ? $agiInfo->SpouseWorkingStatus ? $agiInfo->SpouseWorkingStatus->Sym : '' : '' ;


            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$maritalStatus);
            array_push($values,$spouseWorkingStatus);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;
    }

    public static function toExcelChildrenInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'Çocuk Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Çocuk T.C Kimlik No',
            'Adı Soyadı',
            'Doğum Tarihi',
            'Cinsiyet',
            'Baba Adı',
            'Anne Adı',
            'Yakınlık Derecesi',
            'Eğitimine Devam Ediyor Mu ? ',
            'Okul Kayıt Tarihi',
            'Okul Seviyesi',
            'Okul Adı',
            'Açıklama'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        $rowNum = 2;

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter

            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $children= EmployeesChildModel::where(['EmployeeID' => $employee->Id])->get();

            foreach($children as $childKey =>  $child)
            {
                $asciiCapitalA = 65;
                $values = [];
                $tckn = $child->TCKN;
                $name = $child->name;
                $birthDate = $child->birth_date;
                $gender = $child->Gender ? $child->Gender->Sym : '';
                $fatherName = $child->father_name;
                $motherName = $child->mother_name;
                $relationshipDegree = $child->RelationshipDegree ? $child->RelationshipDegree->name : '';
                $educationContinue = $child->education_continue ? 'Evet' : 'Hayır';
                $schoolRegisterDate = $child->school_register_date;
                $educationLevel = $child->EducationLevel ? $child->EducationLevel->Sym : '';
                $schoolName = $child->school_name;
                $description = $child->description;



                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values,$employeeTCKN);
                array_push($values,$tckn);
                array_push($values,$name);
                array_push($values,$birthDate);
                array_push($values,$gender);
                array_push($values,$fatherName);
                array_push($values,$motherName);
                array_push($values,$relationshipDegree);
                array_push($values,$educationContinue);
                array_push($values,$schoolRegisterDate);
                array_push($values,$educationLevel);
                array_push($values,$schoolName);
                array_push($values,$description);

                foreach ($columns as $keyColumns => $column)
                {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter.($rowNum),$values[$keyColumns]);
                    $asciiCapitalA++;
                }

                $rowNum ++;

            }

        }


        return $workSheet;

    }

    public static function toExcelDrivingLicenseInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Sürücü Belgesi');

        $columns = [
            'T.C Kimlik No',
            'Sürücü Belgesi Durumu',
            'Sürücü Belgesi Tipi',
            'Ehliyet Sınıfı',
            'Belge No',
            'Doğum Tarihi',
            'Doğum Yeri',
            'Veriliş Tarihi',
            'Geçerlilik Tarihi',
            'Verildiği Yer (İl / İlçe)',
            'Düzenleyen',
            'Arka Yüz Seri No'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $drivingLicenseInfo = DrivingLicenseModel::where("EmployeeID",$employee->Id)->first();

            $hasDrivingLicense = $drivingLicenseInfo ? $drivingLicenseInfo->HasDrivingLicense ? 'Evet' : 'Hayır' : '';
            $drivingLicenseType = $drivingLicenseInfo ? $drivingLicenseInfo->DrivingLicenseKind ? 'Yeni' : 'Eski' : '';
            $drivingLicenseClasses = $drivingLicenseInfo ? $drivingLicenseInfo->DrivingLicenseClasses : '';
            $documentNo = $drivingLicenseInfo ? $drivingLicenseInfo->DocumentNo : '';
            $birthDate = $drivingLicenseInfo ? $drivingLicenseInfo->BirthDate : '';
            $birthPlace = $drivingLicenseInfo ? $drivingLicenseInfo->BirthPlace : '';
            $startDate = $drivingLicenseInfo ? $drivingLicenseInfo->StartDate : '';
            $effectiveDate = $drivingLicenseInfo ? $drivingLicenseInfo->EffectiveDate : '';
            $placeOfIssue = $drivingLicenseInfo ? $drivingLicenseInfo->PlaceOfIssue : '';
            $editPerson = $drivingLicenseInfo ? $drivingLicenseInfo->EditPerson : '';
            $backSerialNo = $drivingLicenseInfo ? $drivingLicenseInfo->BackSerialNo : '';




            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$hasDrivingLicense);
            array_push($values,$drivingLicenseType);
            array_push($values,$drivingLicenseClasses);
            array_push($values,$documentNo);
            array_push($values,$birthDate);
            array_push($values,$birthPlace);
            array_push($values,$startDate);
            array_push($values,$effectiveDate);
            array_push($values,$placeOfIssue);
            array_push($values,$editPerson);
            array_push($values,$backSerialNo);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        return $workSheet;
    }

    public static function toExcelPsychoTechnicInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'Psikoteknik Belgesi');

        $columns = [
            'T.C Kimlik No',
            'Geçerli Durumda Psikoteknik Belgesi',
            'Psikoteknik Belgesi Son Geçerlilik Tarihi',
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $psychotechnicInfo = DrivingLicenseModel::where("EmployeeID",$employee->Id)->first();

            $hasPsychotechnicDoc = $psychotechnicInfo ? $psychotechnicInfo->HasPsychotechnicDoc ? 'Evet' : 'Hayır' : '';
            $expireDate = $psychotechnicInfo  ? $psychotechnicInfo->PsychotechnicDate : '';



            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$hasPsychotechnicDoc);
            array_push($values,$expireDate);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        return $workSheet;

    }

    public static function toExcelSRCInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'SRC Belgesi');

        $columns = [
            'T.C Kimlik No',
            'Geçerli Durumda SRC Belgesi',
            'SRC Belgesi Tipi',
            'SRC Belgesi Son Geçerlilik Tarihi'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $SRCDocInfo = DrivingLicenseModel::where("EmployeeID",$employee->Id)->first();


            $hasSRCDocInfo = $SRCDocInfo ? $SRCDocInfo->HasSRCDoc ? 'Evet' : 'Hayır' : '';
            $SRCClasses = $SRCDocInfo ? $SRCDocInfo->SRCClasses ? $SRCDocInfo->SRCClasses : '' : '';
            $expireDate = $SRCDocInfo  ? $SRCDocInfo->SRCDate : '';



            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$hasSRCDocInfo);
            array_push($values,$SRCClasses);
            array_push($values,$expireDate);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        return $workSheet;

    }

    public static function toExcelEmergencyInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'Acil Durum Bilgisi');

        $columns = [
            'T.C Kimlik No',
            'Çalışanın Kan Grubu',
            'Acil Durumda Aranacak Birinci Kişi Ad Soyad',
            'Acil Durumda Aranacak Birinci Kişi Yakınlık Derecesi',
            'Acil Durumda Aranacak Birinci Kişi Telefon Numarası',
            'Acil Durumda Aranacak İkinci Kişi Ad Soyad',
            'Acil Durumda Aranacak İkinci Kişi Yakınlık Derecesi',
            'Acil Durumda Aranacak İkinci Kişi Telefon Numarası',
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }
        $rowNum = 2;
        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $emergencyInformations = EmergencyFieldModel::where(['EmployeeID' => $employee->Id])->get();

            foreach ($emergencyInformations as $emergencyInformation){
                $asciiCapitalA = 65;
                $values = [];

                if ($emergencyInformation->Priority == 0)
                {
                    array_push($values,$employeeTCKN);
                    array_push($values,$employee->BloodTypeID ? BloodTypeModel::find($employee->BloodTypeID)->Sym : '');
                    array_push($values,$emergencyInformation->EmergencyPerson);
                    array_push($values,$emergencyInformation->EPDegree);
                    array_push($values,$emergencyInformation->EPGsm);
                    array_push($values,'');
                    array_push($values,'');
                    array_push($values,'');
                }
                else {
                    array_push($values,$employeeTCKN);
                    array_push($values,$employee->BloodTypeID ? BloodTypeModel::find($employee->BloodTypeID)->Sym : '');
                    array_push($values,'');
                    array_push($values,'');
                    array_push($values,'');
                    array_push($values,$emergencyInformation->EmergencyPerson);
                    array_push($values,$emergencyInformation->EPDegree);
                    array_push($values,$emergencyInformation->EPGsm);
                }


                foreach ($columns as $keyColumns => $column)
                {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter.($rowNum),$values[$keyColumns]);
                    $asciiCapitalA++;
                }

                $rowNum++;
            }



            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE


        }

        return $workSheet;


    }

    public static function toExcelBodyMeasurementsInformations($spreadSheet,$employees){

        $workSheet = new Worksheet($spreadSheet, 'Giyim & Aksesuar');

        $columns = [
            'T.C Kimlik No',
            'Üst Giyim Bedeni',
            'Alt Giyim Bedeni',
            'Ayakkabı Numarası'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $bodyMeasurements = BodyMeasurementModel::where(['EmployeeID' => $employee->Id])->first();

            $upperBody = $bodyMeasurements ? UpperBodyModel::find($bodyMeasurements->UpperBody) : null;
            $upperBody = $upperBody ? $upperBody->Sym : '';
            $lowerBody = $bodyMeasurements ? LowerBodyModel::find($bodyMeasurements->LowerBody) : null;
            $lowerBody = $lowerBody ? $lowerBody->Sym : '';
            $shoeSize = $bodyMeasurements ? LowerBodyModel::find($bodyMeasurements->ShoeSize) : null;
            $shoeSize = $shoeSize ? $shoeSize->Sym : '';



            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$upperBody);
            array_push($values,$lowerBody);
            array_push($values,$shoeSize);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;

    }

    public static function toExcelIDCardInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'Kimlik Bilgileri');

        $columns = [
            'T.C Kimlik No',
            'Uyruğu',
            'Nüfus Adı',
            'Nüfus Soyadı',
            'Doğum Tarihi',
            'Cinsiyet',
            'Nüfus Cüzdanı Seri No',
            'Veriliş Tarihi',
            'Anne Adı',
            'Baba Adı',
            'Doğum Yeri',
            'Nüfusa Kayıtlı Olduğu İl',
            'Nüfusa Kayıtlı Olduğu İlçe',
            'Nüfusa Kayıtlı Olduğu Mahalle',
            'Nüfusa Kayıtlı Olduğu Köy',
            'Nüfus Cüzdanı Cilt No',
            'Nüfus Cüzdanı Sayfa No',
            'Nüfus Cüzdanı Kütük No',
            'Geçerlilik Tarihi'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $IDCard = IdCardModel::where("Id",$employee->Id)->first();

            if ($IDCard)
            {
                $nationality = $IDCard->Nationality ? $IDCard->Nationality->Sym : '';
                $name = $IDCard->FirstName;
                $surname = $IDCard->LastName;
                $birthDate = $IDCard->BirthDate;
                $gender = $IDCard->Gender ? $IDCard->Gender->Sym : '';
                $serialNumber = $IDCard->SerialNumber;
                $dateOfIssue = $IDCard->DateOfIssue;
                $motherName = $IDCard->MotherName;
                $fatherName = $IDCard->FatherName;
                $birthPlace = $IDCard->BirthPlace;
                $city = $IDCard->City ? $IDCard->City->Sym : '';
                $district = $IDCard->District ? $IDCard->District->Sym : '';
                $neighborhood = $IDCard->Neighborhood;
                $village = $IDCard->Village;
                $coverNo = $IDCard->CoverNo;
                $pageNo = $IDCard->PageNo;
                $registerNo = $IDCard->RegisterNo;
                $effectiveDate = $IDCard->ValidDate;
            }

            else{
                $nationality = '';
                $name = '';
                $surname = '';
                $birthDate = '';
                $gender = '';
                $serialNumber = '';
                $dateOfIssue = '';
                $motherName = '';
                $fatherName = '';
                $birthPlace = '';
                $city = '';
                $district ='';
                $neighborhood = '';
                $village = '';
                $coverNo = '';
                $pageNo = '';
                $registerNo = '';
                $effectiveDate = '';
            }




            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$nationality);
            array_push($values,$name);
            array_push($values,$surname);
            array_push($values,$birthDate);
            array_push($values,$gender);
            array_push($values,$serialNumber);
            array_push($values,$dateOfIssue);
            array_push($values,$motherName);
            array_push($values,$fatherName);
            array_push($values,$birthPlace);
            array_push($values,$city);
            array_push($values,$district);
            array_push($values,$neighborhood);
            array_push($values,$village);
            array_push($values,$coverNo);
            array_push($values,$pageNo);
            array_push($values,$registerNo);
            array_push($values,$effectiveDate);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;


    }

    public static function toExcelSocialSecurityInformations($spreadSheet, $employees){

        $workSheet = new Worksheet($spreadSheet, 'Sosyal Güvenlik Bilgileri');

        $columns = [
            'T.C Kimlik No',
            'İlk Sigorta Giriş Tarihi',
            'SGK No',
            'SGK Sicil No',
            'İlk Soyadı',
            'Engel Derecesi',
            'Meslek Kodu',
            'Meslek Açıklaması',
            'Terörle Mücadele Kapsamlı',
            'Sabıka Kayıtlı',
            'Eski Hükümlü',

        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $socialSecurityInformation = SocialSecurityInformationModel::where("EmployeeID",$employee->Id)->first();

            if ($socialSecurityInformation)
            {
                $SSICreateDate = $socialSecurityInformation->SSICreateDate;
                $sgkNo = "_".$socialSecurityInformation->SSINo;
                $sgkSicil = $socialSecurityInformation->SSIRecord ? SGKRegistryNumbersModel::find($socialSecurityInformation->SSIRecord) : '';
                $sgkSicil = $sgkSicil ? $sgkSicil->Name : '';
                $firstLastName = $socialSecurityInformation->FirstLastName;
                $disabledDegree = $socialSecurityInformation->DisabledDegree ? $socialSecurityInformation->DisabledDegree->Sym : '';
                $jobCode = $socialSecurityInformation->JobCode ? $socialSecurityInformation->JobCode->Code .' / ' . $socialSecurityInformation->JobCode->Sym : '';
                $jobDescription = $socialSecurityInformation->JobDescription;
                $terrorismComp = $socialSecurityInformation->TerrorismComp;
                $criminalRecord = $socialSecurityInformation->CriminalRecord;
                $convictRecord = $socialSecurityInformation->ConvictRecord;
            }

            else{
                $SSICreateDate = '';
                $sgkNo = '';
                $sgkSicil = '';
                $firstLastName = '';
                $disabledDegree = '';
                $jobCode = '';
                $jobDescription = '';
                $terrorismComp = '';
                $criminalRecord = '';
                $convictRecord = '';
            }




            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$SSICreateDate);
            array_push($values,$sgkNo);
            array_push($values,$sgkSicil);
            array_push($values,$firstLastName);
            array_push($values,$disabledDegree);
            array_push($values,$jobCode);
            array_push($values,$jobDescription);
            array_push($values,$terrorismComp);
            array_push($values,$criminalRecord);
            array_push($values,$convictRecord);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyEmployee+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }


        return $workSheet;


    }

    public static function toExcelBankInformations($spreadSheet, $employees)
    {
        $workSheet = new Worksheet($spreadSheet, 'Banka Bilgileri');

        $columns = [
            'T.C Kimlik No',
            'Hesap Türü',
            'Banka Adı',
            'Şube No',
            'Hesap No',
            'IBAN',
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }
        $rowNum = 2;
        foreach ($employees as $keyEmployee => $employee)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employeeTCKN = IdCardModel::where("Id",$employee->Id)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;

            $bankAccounts = EmployeeBankModel::where(['EmployeeID' => $employee->Id])->get();

            foreach ($bankAccounts as $account)
            {
                $asciiCapitalA = 65;
                $values = [];
                $accountType = $account->AccountTypeID ? BankAccountTypeModel::find($account->AccountTypeID) : null;
                $accountType = $accountType ? $accountType->Name : '';
                $bankName = $account->BankName;
                $branchNo = $account->BranchNo;
                $accountNo = $account->AccountNo;
                $IBAN = $account->IBAN;

                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values,$employeeTCKN);
                array_push($values,$accountType);
                array_push($values,$bankName);
                array_push($values,$branchNo);
                array_push($values,$accountNo);
                array_push($values,$IBAN);


                foreach ($columns as $keyColumns => $column)
                {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter.($rowNum),$values[$keyColumns]);
                    $asciiCapitalA++;
                }
                $rowNum++;
            }



        }


        return $workSheet;
    }

    public function toExcelInformations($spreadSheet)
    {

    }

    public static function getLastStaffID(){
        $ID_NO = 8011925;
        while(true)
        {
            $count = EmployeeModel::where(['StaffID' => $ID_NO])->count();
            if ($count > 0)
                $ID_NO++;
            else
                break;
        }

        return $ID_NO;
    }

    public static function addEmployee($request)
    {
        $employee = new EmployeeModel();
        $request->JobEmail = preg_replace("/\s+/", "", $request->JobEmail);
        $employee->StaffID              = $request->staffId ? $request->staffId : self::getLastStaffID() ;
        $employee->FirstName            = $request->FirstName;
        $employee->UsageName            = $request->UsageName;
        $employee->LastName             = $request->LastName;
        $employee->DomainID             = $request->DomainID;
        $employee->JobEmail             = isset($request->JobEmail) ? trim($request->JobEmail) : null;
        $employee->JobMobilePhone       = isset($request->JobMobilePhone)  ? $request->JobMobilePhone : null;
        $employee->InterPhone           = isset($request->InterPhone)  ? $request->InterPhone : null;


        try
        {
            $employee->save();
            $loggedUser = DB::table("Employee")->find($request->Employee);
            LogsModel::setLog($request->Employee,$employee->Id,15,34,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adında bir çalışan oluşturdu","","","","","");

        }catch (QueryException $queryException)
        {
            $errorCode = $queryException->errorInfo[1];
            if ($errorCode == 1062)// Duplicate Entry Code JobEmail İçin
            {
                $i=1;
                while (true)
                {
                    try
                    {
                        $mailPreSection = explode("@",$request->JobEmail)[0];
                        $mailPostSection = explode("@",$request->JobEmail)[1];

                        $mailPreSection = $mailPreSection . $i;
                        $mailFull = $mailPreSection .'@'. $mailPostSection;
                        $employee->JobEmail = $mailFull;
                        $employee->save();
                        break;

                    }catch (QueryException $queryException1)
                    {
                        $i++;
                    }
                }

            }

        }

        //Erişim Tiplerini Belirliyoruz.
        self::saveEmployeeAccessType($request->AccessTypes,$employee->Id);

        return $employee->fresh();
    }

    public static function deleteEmployee($id)
    {
        $employee = self::find($id);
        try
        {
            $employee->Active == 0 ? $employee->Active = 1 : $employee->Active = 0  ;
            $employee->save();
            return true;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function getGeneralInformationsFields($employeeId)
    {
        $data = [];
        $data['workingschedulefield']   = WorkingScheduleModel::all();
        $data['contractypefield']       = ContractTypeModel::all();
        $data['accesstypefield']        = UserGroupModel::all();
        $data['domainfield']            = DomainModel::where('active', 1)->get();

        return $data;

    }



    public static function saveGeneralInformations($employee,$request)
    {


        $employee->StaffID              = $request->staffId;
        $employee->FirstName            = $request->firstname;
        $employee->UsageName            = $request->usagename;
        $employee->LastName             = $request->lastname;
        $employee->DomainID             = $request->domain;
        $employee->JobEmail             = $request->jobemail;
        $employee->JobMobilePhone       = $request->jobphone;
        $employee->InterPhone           = $request->internalphone;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $dirtyFields = $employee->getDirty();
        $dirtyFieldsString = "";
        $dirtyFieldsArray = [];
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $employee->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,35,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın genel bilgilerini düzenledi","","","","","");
            }
        }


        try
        {
            $employee->save();
            $employee = $employee->fresh();

        }catch (QueryException $queryException)
        {
            $errorCode = $queryException->errorInfo[1];
            if ($errorCode == 1062)// Duplicate Entry Code JobEmail İçin
            {
                $i=1;
                while (true)
                {
                    try
                    {
                        $mailPreSection = explode("@",$request->jobemail)[0];
                        $mailPostSection = explode("@",$request->jobemail)[1];

                        $mailPreSection = $mailPreSection . $i;
                        $mailFull = $mailPreSection .'@'. $mailPostSection;
                        $employee->JobEmail = $mailFull;
                        $employee->save();
                        break;

                    }catch (QueryException $queryException1)
                    {
                        $i++;
                    }
                }

            }

        }


        self::saveEmployeeAccessType($request->accesstypes,$employee->Id);

        if ($request->hasFile('ProfilePicture')) {
            $file = file_get_contents($request->ProfilePicture->path());
            $guzzleParams = [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'IDCardPhoto_' . $employee->Id . '.' . $request->ProfilePicture->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'id_card'
                    ],
                    [
                        'name' => 'token',
                        'contents' => $request->token
                    ]
                ]
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $employee->Photo = $responseBody->data;
                $employee->save();
            }
        }


        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function saveEmployeeAccessType($accessTypeIDs,$employeeID)
    {
        $accessTypeIDs = !is_array($accessTypeIDs) ? explode(",","".$accessTypeIDs) : $accessTypeIDs;
        $currentAccessTypes = EmployeeHasGroupModel::where('EmployeeID',$employeeID)->where('active',1)->get();

        $currentAccessTypeIDs  = [];

        foreach ($currentAccessTypes as $currentAccessType)
        {
            array_push($currentAccessTypeIDs,$currentAccessType->group_id);
        }



        foreach ($currentAccessTypeIDs as $currentAccessTypeID)
        {

            if (!in_array($currentAccessTypeID,$accessTypeIDs))
            {
                $obj = EmployeeHasGroupModel::where('EmployeeID',$employeeID)->where('group_id',$currentAccessTypeID)
                ->update(['active' => 0]);
            }
        }
        foreach ($accessTypeIDs as $accessTypeID)
        {

            $accessType = EmployeeHasGroupModel::where('EmployeeID',$employeeID)->where('group_id',$accessTypeID);
            if(!$accessType->first())
            {
                $newAccessType = new EmployeeHasGroupModel();

                $newAccessType->EmployeeID = $employeeID;
                $newAccessType->group_id = $accessTypeID;
                $newAccessType->active = 1;
                $newAccessType->save();
            }
            else{
                $accessType->update(['active' => 1]);
            }


        }

    }

    public static function getAccessTypes($id)
    {
        $accessTypeIDs = [];
        $accessTypeObjects = EmployeeHasGroupModel::select('group_id')->where('EmployeeID',$id)->where('active',1)->get();
        foreach ($accessTypeObjects as $val)
        {
            array_push($accessTypeIDs,$val->group_id);
        }
        return $accessTypeIDs;
    }

    public static function saveOtherInformations($employee,$request)
    {
        $employee->ContractTypeID    = $request->contracttypeid;
        $employee->StartDate            = new Carbon($request->jobbegindate);
        $employee->ContractFinishDate   = isset($request->contractfinishdate) ? new Carbon($request->contractfinishdate) : null;
        $employee->WorkingScheduleID    = $request->workingscheduleid;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $dirtyFields = $employee->getDirty();
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $employee->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,38,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın sözleşme bilgisini düzenledi","","","",$field,"");
            }
        }

        if ($employee->save())
            return $employee->fresh();
        else
            return false;
    }

    public static function saveContactInformation($employee,$request)
    {

        $employee->MobilePhone = $request->personalmobilephone;
        $employee->HomePhone = $request->personalhomephone;
        $employee->Email = $request->personalemail;
        $employee->REMMail = $request->kepemail;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $dirtyFields = $employee->getDirty();
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $employee->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,45,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın iletişim bilgisini güncelledi","","","",$field,"");
            }
        }


        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function LdapUserLogin($search,$email,$ldap)
    {

        $userDetail = $search->in($ldap->base_dn)->findBy('mail', $email);

        if($userDetail->useraccountcontrol[0]==66048 || $userDetail->useraccountcontrol[0]==66080  || $userDetail->useraccountcontrol[0]==512)
            return true;
        else
            return false;
    }


    public static function createToken($data)
    {
        $tokenSearch = UserTokensModel::where("EmployeeID", $data["EmployeeID"]);
        $Employee = self::find($data["EmployeeID"]);
        $token = "";
        if($Employee->multi_session==1)
        {
            if($tokenSearch->count()>0)
            {
                $tokenDetail = $tokenSearch->first();
                if (self::tokenControl($tokenDetail->user_token)) {
                    $token = $tokenDetail->user_token;
                }
            }
        }
        if($token=="")
        {
            $token = md5(bin2hex(openssl_random_pseudo_bytes(16)) . $data["email"]);
        }

        if ($tokenSearch->first()) {
            $tokenSearch->update(["user_token" => $token]);
        } else {
            $userToken = new UserTokensModel();
            $userToken->EmployeeID = $data["EmployeeID"];
            $userToken->user_token = $token;
            $userToken->save();
        }

        return $token;
    }


    public static function tokenControl($token)
    {
        global $asayData;
        $tokenSearch = UserTokensModel::where("user_token", $token)->first();
        if (!$tokenSearch) {
            return false;
        } else {

            $date1 = strtotime($tokenSearch->updated_at);
            $date2 = strtotime(date("Y-m-d H:i:s"));
            $asayData["user_id"] = $tokenSearch->user_id;
            $hours = abs($date2-$date1)/(60*60);
            if ((int)$hours > 4) {
                return false;
            }
        }

        self::updateToken($token);
        return true;
    }

    public static function updateToken($token)
    {
        $now = date("Y-m-d H:i:s");
        $tokenSearch = UserTokensModel::where("user_token", $token);
        $tokenSearch->update(["updated_at" => $now]);
    }

    public function getEmployeeGroupAttribute()
    {
        $groups = [];
        $userGroups = $this->hasMany(EmployeeHasGroupModel::class, "EmployeeID", "Id")->get();
        foreach ($userGroups as $userGroup) {
            if ($userGroup->active == 1)
            {
                $group = UserGroupModel::find($userGroup->group_id);
                $groups[$userGroup->group_id] = $group->name;
            }
        }
        return $groups;
    }



    public function getContractTypeAttribute()
    {

        $contractType = $this->hasOne(ContractTypeModel::class,"Id","ContractTypeID");
        if ($contractType)
        {
            return $contractType->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }







    public function getIDCardAttribute()
    {

        $idCard = $this->hasOne(IdCardModel::class,"Id","IDCardID");
        if ($idCard)
        {
            return $idCard->first();
        }
        else
        {
            return "";
        }
    }


    public function getIsUnitSupervisorAttribute(){

        $unitSupervisorCount = EmployeePositionModel::where(["UnitSupervisorID" => $this->attributes['Id'], "Active" => 2])
            ->count();

        if ($unitSupervisorCount > 0)
            return true;
        else
            return false;


    }

    public function getIsEmployeeManagerAttribute(){

        $employeeManagerCount = EmployeePositionModel::where(["ManagerID" => $this->attributes['Id'], "Active" => 2])
            ->count();

        if ($employeeManagerCount > 0)
            return true;
        else
            return false;

    }

    public function getDomainAttribute()
    {
        $domain = $this->hasOne(DomainModel::class,"id","DomainID");
        if ($domain)
        {
            return $domain->first();
        }
        else
        {
            return "";
        }
    }

    public function getEmployeePositionAttribute()
    {
        $position = $this->hasOne(EmployeePositionModel::class,"EmployeeID","Id");
        if ($position)
        {
            return $position->where(['Active' => 2])->first();
        }
        else
        {
            return "";
        }
    }

    public function getAccessTypesAttribute()
    {
        $accessTypes = $this->hasMany(EmployeeHasGroupModel::class,"EmployeeID","Id")->where('active','=',1);
        if ($accessTypes)
        {
            $accessTypeIDs = [];
            foreach ($accessTypes->get() as $accessType)
            {
                array_push($accessTypeIDs,$accessType->group_id);
            }
            return $accessTypeIDs;
        }
        else
        {
            return "";
        }
    }

    public function setMobilePhoneAttribute($value)
    {
        $this->attributes['MobilePhone'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getMobilePhoneAttribute($value)
    {
        try {
            return $this->attributes['MobilePhone'] !== null || $this->attributes['MobilePhone'] != '' ? Crypt::decryptString($this->attributes['MobilePhone']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setHomePhoneAttribute($value)
    {
        $this->attributes['HomePhone'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getHomePhoneAttribute($value)
    {
        try {
            return $this->attributes['HomePhone'] !== null || $this->attributes['HomePhone'] != '' ? Crypt::decryptString($this->attributes['HomePhone']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setREMMailAttribute($value)
    {
        $this->attributes['REMMail'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getREMMailAttribute($value)
    {
        try {
            return $this->attributes['REMMail'] !== null || $this->attributes['REMMail'] != '' ? Crypt::decryptString($this->attributes['REMMail']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['Email'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getEmailAttribute($value)
    {
        try {
            return $this->attributes['Email'] !== null || $this->attributes['Email'] != '' ? Crypt::decryptString($this->attributes['Email']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setBloodTypeIDAttribute($value)
    {
        $this->attributes['BloodTypeID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getBloodTypeIDAttribute($value)
    {
        try {
            return $this->attributes['BloodTypeID'] !== null || $this->attributes['BloodTypeID'] != '' ? (int) Crypt::decryptString($this->attributes['BloodTypeID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function getEmployeePerformance()
    {
        return $this->hasOne('App\Model\PerformanceModel','EmployeeID','Id');
    }



}
