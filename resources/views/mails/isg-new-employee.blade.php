@extends('mails.isg-new-employee-layout')

@section('content')
    <p>Sayın Yetkili,</p>
    <p>{{ $mailContext }}</p>
    <table width="800">
        <tr>
            <th>Personel Adı Soyadı</th>
            <th>SGK Sicil Bilgisi</th>
            <th>Şirket</th>
            <th>Organizasyon</th>
            <th>Ünvan</th>
            <th>Çalıştığı Bölge</th>
            <th>Çalıştığı İl</th>
            <th>Yöneticisi</th>
        </tr>


        <tr
        >
            <td>{{ $employee->UsageName . ' ' . $employee->LastName  }}</td>
            <td>{{ $employee->SocialSecurityInfo ? $employee->SocialSecurityInfo->SSIRecordObject ? $employee->SocialSecurityInfo->SSIRecordObject->Name : '' : '' }}</td>
            <td>{{ $employee->EmployeePosition ? $employee->EmployeePosition->Company ? $employee->EmployeePosition->Company->Sym : '' : '' }}</td>
            <td>{{ $employee->EmployeePosition ? $employee->EmployeePosition->Organization ?  $employee->EmployeePosition->Organization->name : '' : '' }}</td>
            <td>{{ $employee->EmployeePosition ? $employee->EmployeePosition->Title ? $employee->EmployeePosition->Title->Sym : '' : '' }}</td>
            <td>{{ $employee->EmployeePosition ? $employee->EmployeePosition->Region ? $employee->EmployeePosition->Region->Name : '' : '' }}</td>
            <td>{{ $employee->EmployeePosition ? $employee->EmployeePosition->City ? $employee->EmployeePosition->City->Sym : '' : '' }}</td>
            <td>{{ $employee->EmployeePosition ? $employee->EmployeePosition->Manager ? $employee->EmployeePosition->Manager->UsageName . ' ' . $employee->EmployeePosition->Manager->LastName : '' : '' }}</td>
        </tr>


@endsection
