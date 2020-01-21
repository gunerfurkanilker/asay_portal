@extends("ik.template")

@section("content")
    @include("processes.overtime.overtime_subheader")
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__body">
                <div class="kt-widget kt-widget--user-profile-3">
                    <div class="kt-widget__top">
                        <div class="kt-widget__media">
                            <img src="assets/media/users/100_12.jpg" alt="image">
                        </div>
                        <div class="kt-widget__content">
                            <div class="kt-widget__head">
                                <div class="kt-widget__user">
                                    <a href="#" class="kt-widget__username">
                                        Serkan ERdinç
                                    </a>
                                </div>
                            </div>

                            <div class="kt-widget__subhead">
                                <a href="#"><i class="flaticon2-new-email"></i>serkan.erdinc@asay.com.tr</a>
                                <a href="#"><i class="flaticon2-calendar-3"></i>Yazılım Geliştirme Uzmanı </a>
                                <a href="#"><i class="flaticon2-placeholder"></i>Artkıy Ofis</a>
                            </div>

                            <div class="kt-widget__info">
                                <div class="kt-widget__desc">
                                    Kalan Yıllık İzin: 8 gün 4 saat
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">

                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                {{$Title}}
                            </h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">
                            <div class="kt-portlet__head-actions">
                                <a class="btn btn-brand btn-bold btn-sm" href="{{route("overtime_approval_edit",["overtime_id"=>1])}}">Düzenle</a>
                            </div>

                        </div>
                    </div>
                    <div class="kt-form kt-form--label-right">
                        <div class="kt-portlet__body">
                            <div class="form-group form-group-xs row">
                                <label class="col-4 col-form-label">İzin Başlangıç Tarihi:</label>
                                <div class="col-8">
                                    <span class="form-control-plaintext kt-font-bolder">12.01.2019 09:00</span>
                                </div>
                            </div>
                            <div class="form-group form-group-xs row">
                                <label class="col-4 col-form-label">İşe Başlayacağı Tarih:</label>
                                <div class="col-8">
                                    <span class="form-control-plaintext kt-font-bolder">15.09.2019 09:00</span>
                                </div>
                            </div>
                            <div class="form-group form-group-xs row">
                                <label class="col-4 col-form-label">Açıklama:</label>
                                <div class="col-8">
                                    <span class="form-control-plaintext kt-font-bolder">Açıklamalar</span>
                                </div>
                            </div>
                        </div>
                        <div class="kt-portlet__foot">
                            <div class="kt-section kt-section--last">
                                <a href="{{route("overtime_approval_list")}}" class="btn btn-success btn-sm btn-bold"> Onayla</a>
                                <a href="{{route("overtime_approval_list")}}" class="btn btn-danger btn-sm btn-bold">Reddet</a>
                            </div>
                        </div>
                    </div>
                </div>











            </div>

        </div>
    </div>

    <!-- end:: Content -->
@endsection
