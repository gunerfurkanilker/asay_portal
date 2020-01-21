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
                                <label class="col-xl-3 col-lg-3 col-form-label">Başlık</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Username" value="Masraf Başlık">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Masraf Şekli</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" id="exampleSelect1">
                                            <option>Seyahat Avansı</option>
                                            <option selected>İş Avansı</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Masraf Türleri</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" id="exampleSelect1">
                                            <option>Proje</option>
                                            <option>Toplantı</option>
                                            <option>Fırsat</option>
                                            <option selected>İdari İşler Masrafı</option>
                                            <option>Satış ve Pazarma</option>
                                            <option>Misafir Temsil Ağırlama</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Proje Kodu</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Proje Kodu" value="1111-2222-333-4444">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-xl-3 col-lg-3 col-form-label">Açıklama</label>
                                <div class="col-lg-9 col-xl-6">
                                    <textarea class="form-control" id="exampleTextarea" rows="3">Masraf Açıklama</textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-3 col-xl-3">
                                </div>
                                <div class="col-lg-9 col-xl-9">
                                    <button type="reset" class="btn btn-success">Onaya Gönder</button>
                                    <button type="reset" class="btn btn-warning">Kaydet</button>
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
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-actions">
                        <a href="{{route("expense_add_document",["expense_id"=>1])}}" class="btn btn-success btn-sm btn-bold">Yeni Belge</a>
                    </div>
                </div>
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
                                    <a href="{{route("expense_add_document",["expense_id"=>1])}}" class="btn btn-info btn-sm">Gör</a>
                                    <a href="#" class="btn btn-danger btn-sm">Sil</a>
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
                                    <a href="{{route("expense_add_document",["expense_id"=>1])}}" class="btn btn-info btn-sm">Gör</a>
                                    <a href="#" class="btn btn-danger btn-sm">Sil</a>
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
