@extends("ik.template")

@section("content")
    @include("processes.leave.leave_subheader")
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
                            <div class="form-group row mb-3">
                                <label class="col-xl-3 col-lg-3 col-form-label">İzin Başlangıç Tarihi:</label>
                                <div class="col-lg-9 col-xl-6 mt-lg-2 mt-xl-2">
                                    12.01.2019 09:00
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-xl-3 col-lg-3 col-form-label">İşe Başlayacağı Tarihi:</label>
                                <div class="col-lg-9 mt-lg-2 mt-xl-2">
                                    15.01.2019 09:00
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-xl-3 col-lg-3 col-form-label">İzin Türü:</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                   Yıllık İzin
                                </div>
                            </div>
                            <div class="form-group row mb-3 form-group-last">
                                <label class="col-xl-3 col-lg-3 col-form-label">İzin Sebebi:</label>
                                <div class="col-lg-9 col-xl-6 mt-2 mt-lg-2 mt-xl-2">
                                    İzin Açıklamaları
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end::Section-->
                </div>
                <div class="kt-portlet__foot">
                    <div class="kt-form__actions">
                        <div class="row">
                            <div class="col-lg-9 ml-lg-auto">
                                <a href="{{route("leave_list")}}" class="btn btn-danger">İptal</a>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- end:: Content -->
@endsection
