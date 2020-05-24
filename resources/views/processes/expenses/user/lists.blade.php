@extends("ik.template")

@section("content")
    @include("processes.expenses.expense_subheader")
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Masraflarım
                    </h3>
                </div>
            </div>
            <div class="kt-portlet__body">
                <!--begin::Section-->
                <div class="kt-section">
                    <div class="kt-section__content">
                        <div class="col-xl-4 offset-xl-8">
                            <!--begin:: Widgets/Stats2-3 -->
                            <div class="kt-widget1">
                                <div class="kt-widget1__item">
                                    <div class="kt-widget1__info">
                                        <h3 class="kt-widget1__title">İş Avansı Hesabı</h3>
                                    </div>
                                    <span class="kt-widget1__number kt-font-success">{{ isset($request->Tutar->Is) ? $request->Tutar->Is : 0}}</span>
                                </div>

                                <div class="kt-widget1__item">
                                    <div class="kt-widget1__info">
                                        <h3 class="kt-widget1__title">Seyahat Avansı Hesabı</h3>
                                    </div>
                                    <span class="kt-widget1__number kt-font-danger">{{isset($request->Tutar->Seyahat) ? $request->Tutar->Seyahat : 0}}</span>
                                </div>
                            </div>
                            <!--end:: Widgets/Stats2-3 -->
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Başlık</th>
                                    <th style="width: 30%">Açıklama</th>
                                    <th>Masraf Tipi</th>
                                    <th>Tutar</th>
                                    <th>Kodu</th>
                                    <th>Yönetici</th>
                                    <th>İşlem Tarihi</th>
                                    <th>Durumu</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($request->expenses as $expense)
                                    <tr>
                                        <td>{{$expense->NAME}}</td>
                                        <td>{{$expense->CONTENT}}</td>
                                        <td>
                                            @if ($expense->EXPENSE_TYPE=="0")
                                               Genel/Diğer
                                            @elseif ($expense->EXPENSE_TYPE=="toplanti")
                                                Toplantı
                                            @elseif ($expense->EXPENSE_TYPE=="firsat")
                                                Fırsat
                                            @elseif ($expense->EXPENSE_TYPE=="project")
                                                Proje
                                            @elseif ($expense->EXPENSE_TYPE=="BTXİDRİSL")
                                                İdari İşler Masrafı
                                            @elseif ($expense->EXPENSE_TYPE=="BTXSATPAZ")
                                                Satış ve Pazarlama
                                            @elseif ($expense->EXPENSE_TYPE=="BTXMİSTEMS")
                                                Misafir Temsil Ağırlama
                                            @elseif ($expense->EXPENSE_TYPE=="BTXMSTURKCELL")
                                                Turkcell MS Marmara Projesi
                                            @endif
                                        </td>
                                        <td>{{$expense->TUTAR}}</td>
                                        <td>{{$expense->EXPENSE_TYPE_VALUE}}</td>
                                        <td>{{$user->manager}}</td>
                                        <td>{{$expense->DATE_CREATE}}</td>
                                        <td>
                                            @if ($expense->STATUS==0)
                                                Kaydedildi
                                            @elseif ($expense->STATUS==1)
                                                Yönetici Onayı Bekliyor
                                            @elseif ($expense->STATUS==2)
                                                Muhasebe Onayı Bekliyor
                                            @elseif ($expense->STATUS==3)
                                                Tamamlandı
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div>
                                            @if ($expense->STATUS==0)
                                                <a class="btn btn-warning btn-sm" href="{{route("expense_view",["expense_id"=>$expense->ID])}}"><i class="fa fa-edit"></i> Düzenle</a>
                                                <a class="btn btn-danger btn-sm" href="#"><i class="fa fa-trash"></i> Sil</a>
                                            @elseif ($expense->STATUS==1)
                                                <a class="btn btn-info btn-sm" href="#"><i class="fa fa-undo"></i> Geri Al</a>
                                                <a class="btn btn-primary btn-sm" href="{{route("expense_print",["expense_id"=>$expense->ID])}}"><i class="fa fa-eye"></i> Yazdır</a>
                                            @elseif ($expense->STATUS==2)
                                                <a class="btn btn-primary btn-sm" href="{{route("expense_print",["expense_id"=>$expense->ID])}}"><i class="fa fa-eye"></i> Yazdır</a>
                                            @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Section-->
            </div>
            <!--end::Form-->
        </div>
    </div>

    <!-- end:: Content -->
@endsection
