@extends('mails.layoutNotify')

@section('content')
    <p>Sayın Yetkili,</p>
    <p>Aşağıda bilgileri yer alan çalışan tarafından araç bildirimi oluşturulmuştur.</p>
    <br><br>
    <table width="800">
        <tr style="background-color: rgb(0,31,91);color:white;" >
            <th colspan="3" >Talep Eden Bilgileri</th>
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
                {{ $employee->EmployeePosition->Company->Sym }}
            </td>
        </tr>
        <tr>
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
        <tr>
            <td colspan="1">Unvan</td>
            <td colspan="2"  >
                {{ $employee->EmployeePosition->Title->Sym }}
            </td>
        </tr>
        <tr>
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
        <tr>
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
        <tr style="background-color: rgb(0,31,91);color:white;" >
            <th colspan="3" class="text-left">Araç Bildirimi</th>
        </tr>
        <tr>
            <td colspan="1">Ticket No</td>
            <td colspan="2"  >
                {{ "TKT-ARC-".$ticket->id }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Talep Tarih, Saat</td>
            <td colspan="2"  >
                {{ date("d.m.Y H:i:s",strtotime($ticket->created_at)) }}
            </td>
        </tr >
        <tr>
            <td colspan="1">Bildirim Türü</td>
            <td colspan="2"  >
                {{ $ticket->NotifyKind->Name }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Plaka</td>
            <td colspan="2"  >
                {{ $ticket->CarPlate }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Araç Marka,Model</td>
            <td colspan="2"  >
                {{ $ticket->Car ? strtoupper($ticket->Car->CarBrand->Name) . ', ' . strtoupper($ticket->Car->CarBrandModel->Name) : $ticket->CarPlate . ' plakalı araç sistemde kayıtlı değil' }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Model Yılı</td>
            <td colspan="2"  >
                {{ $ticket->Car ? $ticket->Car->CarYear : $ticket->Car->CarYear . ' plakalı araç sistemde kayıtlı değil' }}
            </td>
        </tr>
        @if($ticket->TicketKind == 1 || $ticket->TicketKind == 3)
            <tr>
                <td colspan="1">Güncel KM Bilgisi</td>
                <td colspan="2"  >
                    {{ $ticket->CarKM }}
                </td>
            </tr>
            @endif
        <tr>
            <td colspan="1">Aracın Bulunduğu Bölge</td>
            <td colspan="2"  >
                {{ $ticket->Region->Name }}
            </td>
        </tr>
        <tr>
            <td colspan="1">Aracın Bulunduğu İl</td>
            <td colspan="2"  >
                {{ $ticket->City->Sym  }}
            </td>
        </tr>
        @if($ticket->TicketKind == 1)
            <tr>
                <td colspan="1">Arıza & Problem Türü</td>
                <td colspan="2"  >
                    {{ $ticket->IssueKind->Name  }}
                </td>
            </tr>
            @endif
        @if(count($ticket->MissingCategories) > 0)
            <tr>
                <td colspan="1">Eksik Kategorisi</td>
                <td colspan="2"  >
                    @foreach($ticket->MissingCategories as $item)
                        {{ \App\Model\CarMissingCategoriesModel::find($item)->Name . ', ' }}
                        @endforeach
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="1">Konu</td>
            <td colspan="2"  >
                {{ $ticket->Subject }}
            </td>
        </tr>
        <tr>
            <td colspan="3" style="background-color: rgb(0,31,91);color:white;">Açıklama</td>
        </tr>
        <tr>
            <td colspan="3">
                {!! $ticket->Description !!}
            </td>
        </tr>
    </table>
    @endsection
