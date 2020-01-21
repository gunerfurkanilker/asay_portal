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
                        Kişisel Bilgiler </a>

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
                                    <h3 class="kt-portlet__head-title">Kişisel Bilgiler</h3>
                                </div>
                            </div>
                            <form class="kt-form kt-form--label-right">
                                <div class="kt-portlet__body">
                                    <div class="kt-section kt-section--first">
                                        <div class="kt-section__body">
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Kimlik Numarası</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="12345678901">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Doğum Tarihi</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input type="text" class="form-control" id="kt_datepicker_1" readonly placeholder="Select date" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Medeni Hali</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Evli</option>
                                                            <option>Bekar</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Eş Çalışma Durumu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Çalışıyor</option>
                                                            <option>Çalışmıyor</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Engel Derecesi</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Yok</option>
                                                            <option>1.derece</option>
                                                            <option>2.derece</option>
                                                            <option>3.derece</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Uyruğu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Türkiye</option>
                                                            <option>ABD</option>
                                                            <option>Almanya</option>
                                                            <option>İngilitere</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Çocuk Sayısı</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>0</option>
                                                            <option>1</option>
                                                            <option>2</option>
                                                            <option>3</option>
                                                            <option>4</option>
                                                            <option>5</option>
                                                            <option>6</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Kan Grubu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>0-</option>
                                                            <option>0+</option>
                                                            <option>A-</option>
                                                            <option>A+</option>
                                                            <option>B-</option>
                                                            <option>B+</option>
                                                            <option>AB-</option>
                                                            <option>AB+</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Eğitim Durumu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>Mezun</option>
                                                            <option>Öğrenci</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Tamamlanan En Yüksek Eğitim Seviyesi</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="input-group">
                                                        <select class="form-control" id="exampleSelect1">
                                                            <option>İlkokul</option>
                                                            <option>Ortaokul</option>
                                                            <option>Lise</option>
                                                            <option>Önlisans</option>
                                                            <option>Lisans</option>
                                                            <option>Yüksek Lisans</option>
                                                            <option>Doktora</option>
                                                            <option>Yok</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-xl-3 col-lg-3 col-form-label">Son Tamamlanan Eğitim Kurumu</label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <input class="form-control" type="text" value="ODTU">
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
