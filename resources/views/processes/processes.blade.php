@extends("ik.template")

@section("content")
    <!-- begin:: Content Head -->
    <div class="kt-subheader   kt-grid__item" id="kt_subheader">
        <div class="kt-container  kt-container--fluid ">
            <div class="kt-subheader__main">
                <h3 class="kt-subheader__title">
                    Süreçler
                </h3>
                <span class="kt-subheader__separator kt-subheader__separator--v"></span>
                <div class="kt-subheader__group" id="kt_subheader_search">
                    <span class="kt-subheader__desc" id="kt_subheader_total">Toplam 4 süreç</span>
                </div>
            </div>
        </div>
    </div>
    <!-- end:: Content Head -->

    <!-- begin:: Content -->
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <!--Begin::Section-->
        <div class="row">
            <div class="col-xl-3">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head kt-portlet__head--noborder">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fit-y">

                        <!--begin::Widget -->
                        <div class="kt-widget kt-widget--user-profile-4">
                            <div class="kt-widget__head">
                                <div class="kt-widget__media">
                                    <img class="kt-widget__img kt-hidden-" src="assets/media/processes/harcama.jpg" alt="image">
                                    <img class="kt-widget__img kt-hidden" src="assets/media/users/300_21.jpg" alt="image">
                                </div>
                                <div class="kt-widget__content">
                                    <div class="kt-widget__section">
                                        <a href="{{route("expense_list")}}" class="kt-widget__username">
                                            Harcama
                                        </a>
                                        <div class="kt-widget__action">
                                            <a href="{{route("expense_list")}}" class="btn btn-label-brand btn-bold btn-sm btn-upper">Süreç Detayı </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--end::Widget -->
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head kt-portlet__head--noborder">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fit-y kt-margin-b-40">

                        <!--begin::Widget -->
                        <div class="kt-widget kt-widget--user-profile-4">
                            <div class="kt-widget__head">
                                <div class="kt-widget__media">
                                    <img class="kt-widget__img kt-hidden-" src="assets/media/processes/izin.png" alt="image">
                                    <img class="kt-widget__img kt-hidden" src="assets/media/users/300_21.jpg" alt="image">
                                </div>
                                <div class="kt-widget__content">
                                    <div class="kt-widget__section">
                                        <a href="{{route("leave_list")}}" class="kt-widget__username">
                                            İzin
                                        </a>
                                        <div class="kt-widget__action">
                                            <a href="{{route("leave_list")}}" class="btn btn-label-brand btn-bold btn-sm btn-upper">Süreç Detayı</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--end::Widget -->
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head kt-portlet__head--noborder">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fit-y kt-margin-b-40">

                        <!--begin::Widget -->
                        <div class="kt-widget kt-widget--user-profile-4">
                            <div class="kt-widget__head">
                                <div class="kt-widget__media">
                                    <img class="kt-widget__img kt-hidden-" src="assets/media/processes/fazla_mesai.jpg" alt="image">
                                    <img class="kt-widget__img kt-hidden" src="assets/media/users/300_2.jpg" alt="image">
                                </div>
                                <div class="kt-widget__content">
                                    <div class="kt-widget__section">
                                        <a href="{{route("overtime_list")}}" class="kt-widget__username">
                                            Fazla Mesai
                                        </a>
                                        <div class="kt-widget__action">
                                            <a href="{{route("overtime_list")}}" class="btn btn-label-brand btn-bold btn-sm btn-upper">Süreç Detayı</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--end::Widget -->
                    </div>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head kt-portlet__head--noborder">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fit-y kt-margin-b-40">

                        <!--begin::Widget -->
                        <div class="kt-widget kt-widget--user-profile-4">
                            <div class="kt-widget__head">
                                <div class="kt-widget__media">
                                    <img class="kt-widget__img kt-hidden-" src="assets/media/processes/is_avansi.png" alt="image">
                                    <img class="kt-widget__img kt-hidden" src="assets/media/users/300_1.jpg" alt="image">
                                </div>
                                <div class="kt-widget__content">
                                    <div class="kt-widget__section">
                                        <a href="#" class="kt-widget__username">
                                            İş Avansı
                                        </a>
                                        <div class="kt-widget__action">
                                            <button type="button" class="btn btn-label-brand btn-bold btn-sm btn-upper">Süreç Detayı</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--end::Widget -->
                    </div>
                </div>
            </div>
        </div>

        <!--Begin::Section-->
        <div class="row">
            <div class="col-xl-12">

                <!--begin:: Components/Pagination/Default-->
                <div class="kt-portlet">
                    <div class="kt-portlet__body">

                        <!--begin: Pagination-->
                        <div class="kt-pagination kt-pagination--brand">
                            <ul class="kt-pagination__links">
                                <li class="kt-pagination__link--first">
                                    <a href="#"><i class="fa fa-angle-double-left kt-font-brand"></i></a>
                                </li>
                                <li class="kt-pagination__link--next">
                                    <a href="#"><i class="fa fa-angle-left kt-font-brand"></i></a>
                                </li>
                                <li>
                                    <a href="#">...</a>
                                </li>
                                <li>
                                    <a href="#">2</a>
                                </li>
                                <li>
                                    <a href="#">3</a>
                                </li>
                                <li class="kt-pagination__link--active">
                                    <a href="#">4</a>
                                </li>
                                <li>
                                    <a href="#">5</a>
                                </li>
                                <li>
                                    <a href="#">6</a>
                                </li>
                                <li>
                                    <a href="#">...</a>
                                </li>
                                <li class="kt-pagination__link--prev">
                                    <a href="#"><i class="fa fa-angle-right kt-font-brand"></i></a>
                                </li>
                                <li class="kt-pagination__link--last">
                                    <a href="#"><i class="fa fa-angle-double-right kt-font-brand"></i></a>
                                </li>
                            </ul>
                            <div class="kt-pagination__toolbar">
                                <select class="form-control kt-font-brand" style="width: 60px">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="pagination__desc">
                                    230 kayıttan 10 tanesi gösteriliyor
                                </span>
                            </div>
                        </div>

                        <!--end: Pagination-->
                    </div>
                </div>

                <!--end:: Components/Pagination/Default-->
            </div>
        </div>

        <!--End::Section-->
    </div>

    <!-- end:: Content -->
@endsection
