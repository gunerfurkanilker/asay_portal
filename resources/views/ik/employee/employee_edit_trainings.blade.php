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
                        Eğitimler </a>

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
                                    <h3 class="kt-portlet__head-title">
                                        Eğitimler
                                    </h3>
                                </div>
                                <div class="kt-portlet__head-toolbar">
                                    <div class="kt-portlet__head-actions">
                                        <a href="/metronic/preview/demo12/custom/apps/user/add-user.html" class="btn btn-label-brand btn-bold">Yeni Ekle</a>
                                    </div>
                                </div>
                            </div>
                            <div class="kt-portlet__body">

                                <!--begin::Section-->
                                <div class="kt-section">
                                    <div class="kt-section__content">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Adı</th>
                                                    <th>Alınan Yer</th>
                                                    <th>Tamamlanma Tarihi</th>
                                                    <th>Bitiş Tarihi</th>
                                                    <th>Başarı Puanı</th>
                                                    <th>Durumu</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Temel ISG Eğitimi</th>
                                                    <td>Tetkik OSGB</td>
                                                    <td>23.09.2019</td>
                                                    <td>23.09.2020</td>
                                                    <td>85</td>
                                                    <td>Tamamlandı</td>
                                                    <td>
                                                        <a href="#" class="btn btn-clean btn-sm btn-icon btn-icon-md">
                                                            <i class="flaticon-more-1"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Sağlık Raporu</th>
                                                    <td>Biolab</td>
                                                    <td>23.09.2019</td>
                                                    <td>23.09.2020</td>
                                                    <td>100</td>
                                                    <td>Tamamlandı</td>
                                                    <td>
                                                        <a href="#" class="btn btn-clean btn-sm btn-icon btn-icon-md">
                                                            <i class="flaticon-more-1"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!--end::Section-->
                            </div>

                            <!--end::Form-->
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
