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
            <form class="kt-form kt-form--label-right" action="{{route("expense_view",["expense_id"=>1])}}" method="get">
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
                                            <option>Turkcell MS Marmara Projesi</option>
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
                            <div class="form-group row form-group-last">
                                <label class="col-xl-3 col-lg-3 col-form-label">Açıklama</label>
                                <div class="col-lg-9 col-xl-6">
                                    <textarea class="form-control" id="exampleTextarea" rows="3">Masraf Açıklama</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end::Section-->
                </div>
                <div class="kt-portlet__foot">
                    <div class="kt-form__actions">
                        <div class="row">
                            <div class="col-lg-3 col-xl-3">
                            </div>
                            <div class="col-lg-9 col-xl-9">
                                <button type="submit" class="btn btn-success">Kaydet</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--end::Form-->
        </div>
    </div>

    <!-- end:: Content -->
@endsection
