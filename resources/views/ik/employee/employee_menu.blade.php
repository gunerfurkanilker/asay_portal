<!--Begin:: App Aside-->
<div class="kt-grid__item kt-app__toggle kt-app__aside" id="kt_user_profile_aside">

    <!--begin:: Widgets/Applications/User/Profile1-->
    <div class="kt-portlet kt-portlet--height-fluid-">
        <div class="kt-portlet__head  kt-portlet__head--noborder">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title"></h3>
            </div>
        </div>
        <div class="kt-portlet__body kt-portlet__body--fit-y">

            <!--begin::Widget -->
            <div class="kt-widget kt-widget--user-profile-1">
                <div class="kt-widget__head">
                    <div class="kt-widget__media">
                        <img src="assets/media/users/100_13.jpg" alt="image">
                    </div>
                    <div class="kt-widget__content">
                        <div class="kt-widget__section">
                            <a href="#" class="kt-widget__username">
                                Serkan Erdinç
                                <i class="flaticon2-correct kt-font-success"></i>
                            </a>
                            <span class="kt-widget__subtitle">
                                Yazılım Geliştirme Uzmanı
                            </span>
                        </div>
                    </div>
                </div>
                <div class="kt-widget__body">
                    <div class="kt-widget__content">
                        <div class="kt-widget__info">
                            <span class="kt-widget__label">Email:</span>
                            <a href="#" class="kt-widget__data">serkan.erdinc@asay.com.tr</a>
                        </div>
                        <div class="kt-widget__info">
                            <span class="kt-widget__label">Telefon:</span>
                            <a href="#" class="kt-widget__data">05498225387</a>
                        </div>
                        <div class="kt-widget__info">
                            <span class="kt-widget__label">Lokasyon:</span>
                            <span class="kt-widget__data">İzmir/Gaziemir</span>
                        </div>
                    </div>
                    <div class="kt-widget__items">
                        <a href="{{route("employee_edit",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-menu-2"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Genel
                                </span>
                            </span>
                        </a>
                        <a href="{{route("employee_edit_career",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_career") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-cup"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Kariyer
                                </span>
                            </span>
                        </a>
                        <a href="{{route("employee_edit_personal_info",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_personal_info") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span>
                                    <i class="flaticon2-user"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Kişisel Bilgiler
                                </span>
                            </span>
                        </a>
                        <a href="{{route("employee_edit_more_info",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_more_info") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-layers"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Diğer Bilgiler
                                </span>
                            </span>
                        </a>
                        <a href="{{route("employee_edit_user_groups",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_user_groups") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-layers"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Kullanıcı Grupları
                                </span>
                            </span>
                        </a>
                        <a href="{{route("employee_edit_trainings",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_trainings") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-box"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Eğitimler
                                </span>
                            </span>
                        </a>
                       {{-- <a href="{{route("employee_edit_isg_trainings",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_isg_trainings") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-box"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    ISG Eğitimler
                                </span>
                            </span>
                        </a>--}}
                        <a href="{{route("employee_edit_assets",["id"=>1])}}" class="kt-widget__item @if(Route::current()->getName()=="employee_edit_assets") kt-widget__item--active @endif">
                            <span class="kt-widget__section">
                                <span class="kt-widget__icon">
                                    <i class="flaticon2-delivery-package"></i>
                                </span>
                                <span class="kt-widget__desc">
                                    Zimmetler
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!--end::Widget -->
        </div>
    </div>

    <!--end:: Widgets/Applications/User/Profile1-->
</div>

<!--End:: App Aside-->
@section("js")
    <script src="assets/js/pages/dashboard.js" type="text/javascript"></script>
    <script src="assets/js/pages/custom/user/profile.js" type="text/javascript"></script>
    <script>
        var arrows;
        if (KTUtil.isRTL()) {
            arrows = {
                leftArrow: '<i class="la la-angle-right"></i>',
                rightArrow: '<i class="la la-angle-left"></i>'
            }
        } else {
            arrows = {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            }
        }

        // minimum setup
        $('#kt_datepicker_1, #kt_datepicker_1_validate').datepicker({
            rtl: KTUtil.isRTL(),
            todayHighlight: true,
            orientation: "bottom left",
            templates: arrows
        });
    </script>
@endsection
