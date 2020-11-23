@extends('mails.layoutNotify')

@section('content')
    <p>Sayın Yetkili,</p>
    <p>Aşağıda bilgileri yer alan çalışan tarafından, iletişim kaydı oluşturulmuştur. İlgili kayıt ve kaydı oluşturan personele ait bilgiler aşağıdaki gibidir.</p>
    <br><br>
    <table width="800">
        <tr style="background-color: rgb(0,31,91);color:white;" >
            <th colspan="3" >Personel Bilgisi</th>
        </tr>
        <tr>
            <td colspan="1">Adı Soyadı</td>
            <td colspan="2"  >
                {{ $employee->UsageName . ' ' . $employee->LastName }}
            </td>
        </tr>
        <tr>
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
        <tr>
            <td colspan="1">Şirket</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition ? $employee->EmployeePosition->Company ? $employee->EmployeePosition->Company->Sym : '' : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Organizasyon</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition ? $employee->EmployeePosition->Organization ? $employee->EmployeePosition->Organization->name : '' : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Departman</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition ? $employee->EmployeePosition->Department ? $employee->EmployeePosition->Department->Sym : '' : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Unvan</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition ? $employee->EmployeePosition->Title ? $employee->EmployeePosition->Title->Sym : '' : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Bölge</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition ? $employee->EmployeePosition->Region ? $employee->EmployeePosition->Region->Name : '' : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Çalıştığı İl</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition ? $employee->EmployeePosition->City ? $employee->EmployeePosition->City->Sym : '' : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Yöneticisi</td>
            <td colspan="2">
                {{ $employee->EmployeePosition->Manager ? $employee->EmployeePosition->Manager->UsageName . ' ' . $employee->EmployeePosition->Manager->LastName : '' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Birim Sorumlusu</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->UnitSupervisor ? $employee->EmployeePosition->UnitSupervisor->UsageName . ' ' . $employee->EmployeePosition->UnitSupervisor->LastName : '' }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,31,91);color:white;" >
            <th colspan="3" class="text-left">İletişim Formu Bilgileri</th>
        </tr>
        <tr>
            <td colspan="1">Destek Türü</td>
            <td colspan="2"  >
                {{ $contactUs->ContactUsType->Name }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Konu</td>
            <td colspan="2"  >
                {{ $contactUs->Subject }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Mesaj</td>
            <td colspan="2"  >
                {{ $contactUs->Description }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Gönderim Tarihi</td>
            <td colspan="2"  >
                {{ date("d.m.Y H:i:s",strtotime($contactUs->created_at)) }}
            </td>
        </tr>

    </table>
@endsection
