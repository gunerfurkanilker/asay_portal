@extends("ik.template")

@section("content")
    @include("processes.expenses.expense_subheader")
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        {{$Title}}
                    </h3>
                </div>
            </div>
            <form class="kt-form kt-form--label-right">
                <div class="kt-portlet__body">
                    <!--begin::Section-->
                    <div class="kt-section">
                        <div class="kt-section__content">
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Başlık:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                    Masraf Başlık
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Talep Eden:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                    Serkan Erdinç
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Oluşturulma Tarihi:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                    02.01.2019 15:00:00
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Masraf Şekli:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                    İş Avansı
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Masraf Türleri:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                   İdari İşler Masrafı
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Proje Kodu:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                    1111-2222-333-4444
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-xl-3 col-lg-3 col-form-label">Açıklama:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                   Masraf Açıklama
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->
                </div>
            </form>
            <!--end::Form-->
        </div>
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        MASRAF BELGELERİ
                    </h3>
                </div>
                {{--<div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-actions">
                        <a href="{{route("expense_add_document",["expense_id"=>1])}}" class="btn btn-success btn-sm btn-bold">Yeni Belge</a>
                    </div>
                </div>--}}
            </div>
            <div class="kt-portlet__body">
                <!--begin::Section-->
                <div class="kt-section">
                    <div class="kt-section__content">
                        <table class="table">
                            <thead class="thead-light">
                            <tr>
                                <th>Belge Tipi</th>
                                <th>Belge No</th>
                                <th>Tarih</th>
                                <th>Cari</th>
                                <th>Para Birimi</th>
                                <th>Toplam Tutar</th>
                                <th>İşlemler</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Fişli</td>
                                <td>12311</td>
                                <td>2019-12-30</td>
                                <td></td>
                                <td>TRY</td>
                                <td>15.00</td>
                                <td>
                                    <a href="{{route("expense_document_pending",["expense_id"=>1])}}" class="btn btn-info btn-sm">Gör</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Faturalı</td>
                                <td>A121516</td>
                                <td>2019-12-30</td>
                                <td>Asay Group</td>
                                <td>TRY</td>
                                <td>45.00</td>
                                <td>
                                    <a href="{{route("expense_document_pending",["expense_id"=>1])}}" class="btn btn-info btn-sm">Gör</a>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="5" class="text-right font-weight-bold">Toplam Tutar</td>
                                <td>60.00</td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Section-->
            </div>
        </div>
    </div>

    <!-- end:: Content -->
@endsection
