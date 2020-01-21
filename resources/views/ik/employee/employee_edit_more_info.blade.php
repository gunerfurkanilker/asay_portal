@extends("ik.template")

@section("content")

    <!-- begin:: Subheader -->
    <div class="kt-subheader   kt-grid__item" id="kt_subheader">
        <div class="kt-container  kt-container--fluid ">
            <div class="kt-subheader__main">
                <h3 class="kt-subheader__title">
                    <button class="kt-subheader__mobile-toggle kt-subheader__mobile-toggle--left" id="kt_subheader_mobile_toggle"><span></span></button>
                    Serkan Erdinç </h3>
                <span class="kt-subheader__separator kt-hidden"></span>
                <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="" class="kt-subheader__breadcrumbs-link">
                        Çalışanlar </a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="" class="kt-subheader__breadcrumbs-link">
                        Serkan Erdinç </a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="" class="kt-subheader__breadcrumbs-link">
                        Diğer Bilgiler </a>

                    <!-- <span class="kt-subheader__breadcrumbs-link kt-subheader__breadcrumbs-link--active">Active link</span> -->
                </div>
            </div>
        </div>
    </div>

    <!-- end:: Subheader -->

    <!-- begin:: Content -->
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">

        <!--Begin::App-->
        <div class="kt-grid kt-grid--desktop kt-grid--ver kt-grid--ver-desktop kt-app">

            <!--Begin:: App Aside Mobile Toggle-->
            <button class="kt-app__aside-close" id="kt_user_profile_aside_close">
                <i class="la la-close"></i>
            </button>

            <!--End:: App Aside Mobile Toggle-->

        @include("ik.employee.employee_menu")

        <!--Begin:: App Content-->
            <div class="kt-grid__item kt-grid__item--fluid kt-app__content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="kt-portlet">
                            <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title">Diğer Bilgiler</h3>
                                </div>
                            </div>
                            <form class="kt-form kt-form--label-right">
                                <div class="kt-portlet__body">
                                    <div class="kt-section kt-section--first">
                                        <div class="kt-section__body">
                                            <div class="row">
                                                <label class="col-xl-3"></label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <h3 class="kt-section__title kt-section__title-sm">Adres Bilgileri:</h3>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Adres</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <textarea class="form-control" id="exampleTextarea" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Ev Telefonu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="02321234567">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Ülke</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Türkiye</option>
                                                            <option>Test</option>
                                                            <option>Test</option>
                                                            <option>Test</option>
                                                            <option>Test</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Şehir</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>İzmir</option>
                                                            <option>Ankara</option>
                                                            <option>İstanbul</option>
                                                            <option>Uşak</option>
                                                            <option>Muğla</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Posta Kodu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="351234">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <label class="col-xl-3"></label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <h3 class="kt-section__title kt-section__title-sm">Banka Bilgileri:</h3>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Banka Adı</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="351234">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Hesap Türü</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Vadeli</option>
                                                            <option>Vadesi</option>
                                                            <option>Çek</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Hesap No</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="351234-1234567">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">IBAN</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="TR9000000000000000000000">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <label class="col-xl-3"></label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <h3 class="kt-section__title kt-section__title-sm">Acil Durum Bilgileri:</h3>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Acil Durumda Aranacak Kişi</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="351234-1234567">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Acil Durumda Aranacak Kişi Yakınlık Derecesi</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="351234-1234567">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Acil Durumda Aranacak Kişi Telefon</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="351234-1234567">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="kt-portlet__foot">
                                    <div class="kt-form__actions">
                                        <div class="row">
                                            <div class="col-lg-3 col-xl-3">
                                            </div>
                                            <div class="col-lg-9 col-xl-9">
                                                <button type="reset" class="btn btn-success">Kaydet</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <!--End:: App Content-->
        </div>

        <!--End::App-->
    </div>

    <!-- end:: Content -->

@endsection
