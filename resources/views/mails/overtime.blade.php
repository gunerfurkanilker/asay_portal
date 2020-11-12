
@extends("mails.layout")

@section("content")
    <br><br>
    <table width="800">
        <tr style="background-color: rgb(0,32,92);color:white" >
            <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
        </tr>
        <tr >
            <td colspan="2"  >
                {{ $employee->UsageName . ' ' . $employee->LastName }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>İş Emri No</b>
            </td>
            <td  >
                <b>Görevlendirilen Personel</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $overtime->JobOrderNo  }}
            </td >
            <td  >
                {{ $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Statü</b>
            </td>
            <td  >
                <b>Yönetici</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $overtime->Status->Name }}
            </td>
            <td  >
                {{ $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName  }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Proje</b>
            </td>
            <td  >
                <b>Başlangıç Tarihi</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $overtime->Project->name }}
            </td>
            <td  >
                {{ date("d.m.Y", strtotime($overtime->BeginDate)) }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Tahmini Başlangıç Saati</b>
            </td>
            <td  >
                <b>Tahmini Bitiş Saati</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $overtime->BeginTime }}
            </td>
            <td  >
                {{ $overtime->EndTime }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Çalışma Yapılacak Saha ID</b>
            </td>
            <td  >
                <b>Çalışma Yapılacak Saha Adı</b>
            </td>
        </tr>
        <tr>
            <td>
                {{ $overtime->FieldID }}
            </td>
            <td>
                {{ $overtime->FieldName }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Araç Kullanacak Mı</b>
            </td>
            <td  >
                <b>Araç Plakası</b>
            </td>
        </tr>
        <tr>
            <td>
                {{ $usingCar }}
            </td>
            <td>
                {{ $overtime->PlateNumber  }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  colspan="2">
                <b>Açıklama</b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{ $overtime->Description }}
            </td>
        </tr>
        @if(isset($extraFields))
            <tr style="background-color: rgb(0,32,92);color:white">
                <td>
                    <b>Gerçekleşen Fazla Çalışma Tarihi</b>
                </td>
                <td colspan="2" >
                    <b>Çalışma No</b>
                </td>
            </tr>
            <tr >
                <td>
                    {{ date("d.m.Y", strtotime($overtime->WorkBeginDate)) }}
                </td>
                <td>
                    {{ $overtime->WorkNo }}
                </td>
            </tr>
            <tr style="background-color: rgb(0,32,92);color:white">
                <td>
                    <b>Çalışma Başlangıç Saati</b>
                </td>
                <td>
                    <b>Çalışma Bitiş Saati</b>
                </td>
            </tr>
            <tr >
                <td>
                    {{ $overtime->WorkBeginTime }}
                </td>
                <td>
                    {{ $overtime->WorkEndTime }}
                </td>
            </tr>
        @endif
        @if(isset($reason))
        <tr style="background-color: rgb(0,32,92);color:white">
            <td colspan="2" >
                <b>İşlem Açıklaması</b>
            </td>
        </tr>
        <tr >
            <td  colspan="2" >
                {{ $reason }}
            </td>
        </tr>
            @endif
        @if(isset($dirtyFields))
            <tr style="background-color: rgb(0,32,92);color:white">
                <td  colspan="2"  >
                    <b>Düzenleme Yapılan Alanlar</b>
                </td>
            </tr>
            @foreach($dirtyFields as $dirtyField)
                <tr>
                    <td>{{ $dirtyField->changedFieldName .' (İlk Değer) : ' . $dirtyField->oldData}}</td>
                    <td>{{ $dirtyField->changedFieldName .' (Düzenlenen Değer) : ' . $dirtyField->newData}}</td>
                </tr>
                @endforeach

            @endif
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  colspan="2"  >
                <b>Bağlantı Linki</b>
            </td>
        </tr>
        <tr >

            <td  colspan="2" >
                <a href="{{ $overtimeLink.$overtime->id }}">Fazla Çalışma Link</a>
            </td>
        </tr>
    </table>
@endsection

