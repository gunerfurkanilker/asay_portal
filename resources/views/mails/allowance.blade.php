@extends("mails.layoutAllowance")

@section("content")
    <br><br>
    <table width="800">
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Başlık</b>
            </td>
            <td  >
                <b>Avans Talep Eden</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $allowance->Name }}
            </td >
            <td  >
                {{ $allowance->CreatedBy->UsageName . ' ' . $allowance->CreatedBy->LastName }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Avans Tipi</b>
            </td>
            <td  >
                <b>Avans Türü</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $allowance->ExpenseType == 1 ? 'İş Avansı' : 'Seyahat Avansı'  }}
            </td >
            <td  >
                {{ $allowance->Project->name }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Proje</b>
            </td>
            <td  >
                <b>Avans Kategorisi</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $allowance->Project->name }}
            </td>
            <td  >
                {{ $allowance->Category->name  }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>İş Emri Kodu</b>
            </td>
            <td  >
                <b>Avans Gereklilik Tarihi</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $allowance->Code }}
            </td>
            <td  >
                {{ date("d.m.Y", strtotime($allowance->RequirementDate)) }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  colspan="2">
                <b>Avans Tutarı</b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{ $allowance->Amount }}
            </td>
        </tr>
        @if($allowance->ExpenseType == 2)
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Seyahat Aracı</b>
            </td>
            <td  >
                <b>Gidilecek Yer</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $allowance->TravelBy == 1 ? 'Uçak' : 'Araba' }}
            </td>
            <td  >
                {{ $allowance->TravelTo }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Gidiş Tarihi</b>
            </td>
            <td  >
                <b>Gün Sayısı</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ date("d.m.Y", strtotime($allowance->TravelDate)) }}
            </td>
            <td  >
                {{ $allowance->TravelDay }}
            </td>
        </tr>
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  >
                <b>Konaklanacak Gece Sayısı</b>
            </td>
            <td  >
                <b>Konaklanacak Yer</b>
            </td>
        </tr>
        <tr>
            <td  >
                {{ $allowance->TravelNight }}
            </td>
            <td  >
                {{ $allowance->TravelAccommodation }}
            </td>
        </tr>
        @endif
        <tr style="background-color: rgb(0,32,92);color:white">
            <td  colspan="2">
                <b>Açıklama</b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{ $allowance->Description }}
            </td>
        </tr>
    </table>
@endsection
