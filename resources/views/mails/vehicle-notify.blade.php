@extends('mails.layout)

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
            <th colspan="3" class="text-left">Araç Bildirimi</th>
        </tr>
        <tr>
            <td colspan="1">Ticket No</td>
            <td colspan="2"  >
                {{ 'Ticket No\'su' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Talep Tarih, Saat</td>
            <td colspan="2"  >
                {{ 'Talep Tarih ve Saat' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Bildirim Türü</td>
            <td colspan="2"  >
                {{ 'Bildirim Türü' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Plaka</td>
            <td colspan="2"  >
                {{ '35TT34' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Araç Marka,Model</td>
            <td colspan="2"  >
                {{ 'DACIA, DUSTER' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Model Yılı</td>
            <td colspan="2"  >
                {{ '2017' }}
            </td>
        </tr>
        @if(true)
            <tr>
                <td colspan="1">Güncel KM Bilgisi</td>
                <td colspan="2"  >
                    {{ '122512 KM' }}
                </td>
            </tr>
            @endif
        <tr>
            <td colspan="1">Aracın Bulunduğu Bölge</td>
            <td colspan="2"  >
                {{ 'Asya' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Aracın Bulunduğu İl</td>
            <td colspan="2"  >
                {!! 'İstanbul' !!}
            </td>
        </tr>
        @if(true)
            <tr>
                <td colspan="1">Arıza & Problem Türü</td>
                <td colspan="2"  >
                    {!! 'Araç Kamerası' !!}
                </td>
            </tr>
            @endif
        @if(true)
            <tr>
                <td colspan="1">Eksik Kategorisi</td>
                <td colspan="2"  >
                    {!! 'Eksik Kategorisi' !!}
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="1">Konu</td>
            <td colspan="2"  >
                {!! 'Kamera Siyah Ekran Sorunu' !!}
            </td>
        </tr>
        <tr>
            <td colspan="3">Açıklama</td>
        </tr>
        <tr>
            <td colspan="3"  >
                {!! 'Açıklama' !!}
            </td>
        </tr>
    </table>
    @endsection
