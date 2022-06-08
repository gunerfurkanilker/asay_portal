<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PerformanceResource;
use App\Model\CarBrandModel;
use App\Model\CarColorModel;
use App\Model\CarFinesModel;
use App\Model\CarResult;
use App\Model\CarTypeModel;
use App\Model\CityModel;
use App\Model\DismissialResultModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\HESCodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CarColorController extends ApiController
{
    public function getCarColor(Request $request){

        $allColors = CarColorModel::All();
        return response([
            'status' => true,
            'data' => $allColors
        ],200);
    }

    public function getCarFines(Request $request){
        $carFines = CarFinesModel::all();
        return response([
            'status' => true,
            'data' => $carFines
        ],200);
    }
    public function getCarBrand(Request $request)
    {
        $carBrands = CarBrandModel::all();
        return response([
            'status' => true,
            'data' => $carBrands
        ],200);
    }
    public function getCarType(Request $request)
    {
        $getCarType = CarTypeModel::all();
        return response([
            'status' => true,
            'data' => $getCarType
        ],200);
    }

    public function getAllCity(Request $request)
    {
        $getAllCity = CityModel::all();
        return response([
            'status' => true,
            'data' => $getAllCity
        ],200);
    }

    public function carResult(Request $request){



        $carResult = new CarResult();

        $carResult->EmployeeID = $request->EmployeeID?? '';
        $carResult->Plate = $request->Plate?? '';
        $carResult->Type = $request->Type?? '';
        $carResult->Colour = $request->Colour?? '';
        $carResult->Brand = $request->Brand?? '';
        $carResult->Model = $request->Model?? '';
        $carResult->FineDocument = $request->FineDocument?? '';
        $carResult->FineItem = $request->FineItem?? '';
        $carResult->FineAmount = $request->FineAmount?? '';
        $carResult->FinePoint = $request->FinePoint?? '';
        $carResult->FineDate = $request->FineDate?? '';
        $carResult->FineHour = $request->FineHour?? '';
        $carResult->FineCity = CityModel::where('Sym',$request->FineCity)->first()->Id;
        $carResult->FineAdress = $request->FineAdress?? '';
        $carResult->Explanation = $request->Explanation?? '';
        $carResult->DriverId = $request->DriverId?? '';
        $carResult->RecourseId = $request->RecourseId?? '';
        $carResult->FileId = $request->FileId?? '';

        $carResult->save();


        return response()->json([
            'status' => 200,
            'message' => 'Kayıt Başarılı'
        ]);
    }

    public function getCarResult(Request $request)
    {
        $getCarResult = CarResult::with('city')
            ->with('type')
            ->with('brand')
            ->with('color')
            ->with('employee')
            ->with('employeer')
            ->with('carFines')
            ->with('model');

        if($request->searchText!=''){
            $getCarResult=$getCarResult->where('Plate','LIKE',"%$request->searchText%");
        }

            $getCarResult=$getCarResult->paginate($request->recordPerPage);

        return response()->json([
            'status' => 200,
            'data' => $getCarResult
        ]);
    }

    public function toExcel(Request $request){
        $carListss = CarResult::with('city')
            ->with('type')
            ->with('brand')
            ->with('color')
            ->with('employee')
            ->with('employeer')
            ->with('carFines')
            ->with('model')
            ->get();
        $carLists =[];
        foreach ($carListss as $item){
            array_push($carLists,$item);
        }

        $spreadsheet = new Spreadsheet();

        $workSheet = new Worksheet($spreadsheet, 'Araç Raporu');

        $columns = [
            'Araç Plaka',
            'Araç Cinsi',
            'Araç Rengi',
            'Araç Markası',
            'Araç Modeli',
            'Ceza Belge No',
            'Ceza Tutarı',
            'Ceza Puanı',
            'Ceza Tarihi',
            'Ceza Saati',
            'Ceza İli',
            'Ceza Yeri',
            'Sürücü',
            'Rücü',
            'Ceza Maddesi',
            'Açıklama'

        ];

        $asciiCapitalA = 65;

        foreach ($columns as $key => $column) {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter . "1", $column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }


        foreach ($carLists as $carList => $cars) {

            $asciiCapitalA = 65;
            $values = [];
//        $employeeId = $cars->EmployeeId;
        $plate = $cars->Plate;
        $type = $cars->type->Name;
        $colors = $cars->color->Colours;
        $brand = $cars->brand->Name;
        $model = $cars->Model;
        $fineDocument = $cars->FineDocument;
        $fineAmount = $cars->FineAmount;
        $finePoint = $cars->FinePoint;
        $fineDate = $cars->FineDate;
        $fineHour = $cars->FineHour;
        $fineCity = $cars->city->Sym_Tr;
        $fineAdress = $cars->FineAdress;
        $driverName = $cars->employee->FirstName ?? 'Çalışan ismi sistemde yok.';
        $driverLastName = $cars->employee->LastName ?? '';
        $driverId = $driverName ." ". $driverLastName;
        $rucuName = $cars->employee->FirstName ?? 'Çalışan ismi sistemde yok.';
        $rucuLastName = $cars->employee->LastName ?? '';
        $recourseId = $rucuName . " ". $rucuLastName;
        $fineItem = $cars->carFines->FinesItem;
        $explanation = $cars->Explanation;

            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values, $plate);
            array_push($values, $type);
            array_push($values, $colors);
            array_push($values, $brand);
            array_push($values, $model);
            array_push($values, $fineDocument);
            array_push($values, $fineAmount);
            array_push($values, $finePoint);
            array_push($values, $fineDate);
            array_push($values, $fineHour);
            array_push($values, $fineCity);
            array_push($values, $fineAdress);
            array_push($values, $driverId);

            array_push($values, $recourseId);
            array_push($values, $fineItem);
            array_push($values, $explanation);


            foreach ($columns as $keyColumns => $column) {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter . ($carList + 2), $values[$keyColumns]);
                $asciiCapitalA++;

            }

        }
        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $spreadsheet->addSheet($workSheet, 0);


        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("car.xlsx", $content);
        return response()->download(storage_path('app/' . "car.xlsx"));
    }

}
