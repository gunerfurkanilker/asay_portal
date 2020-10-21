@extends('mails.layout')

@section('content')
    <br><br>
    <table width="800">
        <tr style="background-color: rgb(0,31,91);color:white" >
            <th colspan="3" class="text-left">Talep Eden Bilgileri</th>
        </tr>
        <tr >
            <td colspan="1">Adı Soyadı</td>
            <td colspan="2"  >
                {{ $employee->UsageName . ' ' . $employee->LastName }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white">
            <td colspan="1">E-Posta</td>
            <td colspan="2"  >
                {{ $employee->JobEmail }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Mobil Telefon (İş)</td>
            <td colspan="2"  >
                {{ $employee->JobMobilePhone }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white">
            <td colspan="1">Organizasyon</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Organization->name }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Departman</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Department->Sym }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white">
            <td colspan="1">Unvan</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Title->Sym }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Unvan</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Title->Sym }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white">
            <td colspan="1">Bölge</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Region->Name }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Çalıştığı İl</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->City->Sym }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white">
            <td colspan="1">Yöneticisi</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Manager->UsageName . ' ' . $employee->EmployeePosition->Manager->LastName }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Birim Sorumlusu</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->UnitSupervisor->UsageName . ' ' . $employee->EmployeePosition->UnitSupervisor->LastName }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white" >
            <th colspan="3" class="text-left">Destek Talebi</th>
        </tr>
        <tr>
            <td colspan="1">Ticket No</td>
            <td colspan="2"  >
                {{ 'TKT-IT-'.$itSupport->id }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Talep Tarih, Saat</td>
            <td colspan="2"  >
                {{ $itSupport->CreatedDate }}
            </td>
        </tr>
        <tr>
            <td colspan="1">İstek Türü</td>
            <td colspan="2"  >
                {{ $itSupport->RequestTypeName }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Öncelik</td>
            <td colspan="2"  >
                {{ $itSupport->PriorityName }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Kategori</td>
            <td colspan="2"  >
                {{ $itSupport->CategoryName }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Alt Kategori</td>
            <td colspan="2"  >
                {{ $itSupport->SubCategoryName }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Alt Kategori İçeriği</td>
            <td colspan="2"  >
                {{ $itSupport->SubCategoryContentName }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Konu</td>
            <td colspan="2"  >
                {{ $itSupport->Subject }}
            </td>
        </tr>
        <tr>
            <td colspan="3">Açıklama</td>
        </tr>
        <tr>
            <td colspan="3"  >
                {!! $itSupport->Content !!}
            </td>
        </tr>
    </table>
    @endsection
