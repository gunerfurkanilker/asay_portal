@extends('mails.layout-isg-expire')

@section('content')
    <p>Sayın Yetkili,</p>
    <p>{{ $mailContext }}</p>
    <table width="800">
        <tr>
            <th>Eğitimi Alan Personel</th>
            <th>Eğitim Adı</th>
            <th>Eğitim Başlangıç Tarihi</th>
            <th>Son Geçerlilik Tarihi</th>
            <th>Eğitimi Sağlayan Kurum</th>
            <th>Eğitim Statüsi</th>
        </tr>
        @foreach($trainings as $training)
            <tr
            >
                @if($training->Employee && $training->Training)
                <td>{{ $training->Employee->UsageName . ' ' . $training->Employee->LastName  }}</td>
                <td>{{ $training->Training->Category->Name }}</td>
                <td>{{ date("d.m.y",strtotime($training->StartDate)) }}</td>
                <td>{{ date("d.m.y",strtotime($training->ExpireDate)) }}</td>
                <td>{{ $training->Training->Company->Name }}</td>
                <td>{{ $training->Status->Name  }}</td>
                    @endif
            </tr>
            @endforeach

    @endsection
