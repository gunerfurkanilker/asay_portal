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
            <form class="kt-form kt-form--label-right" action="{{route("leave_list")}}" method="get">
                <div class="kt-portlet__body">
                    <!--begin::Section-->
                    <div class="kt-section">
                        <div class="kt-section__content">
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">İzin Başlangıç Tarihi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kt_datepicker_1" readonly placeholder="Select date" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">İşe Başlayacağı Tarihi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kt_datepicker_1" readonly placeholder="Select date" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">İzin Türü</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" id="exampleSelect1">
                                            <option selected>Yıllık İzin</option>
                                            <option>Doğum İzni</option>
                                            <option>Babalık İzni</option>
                                            <option>İstirahat Raporu</option>
                                            <option>Ücretsiz İzin</option>
                                            <option>Evlilik İzni</option>
                                            <option>Sebebsiz olarak işe gelmeme</option>
                                            <option>1.Derece Yakın Vefat İzni</option>
                                            <option>Diğer</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row form-group-last">
                                <label class="col-xl-3 col-lg-3 col-form-label">İzin Sebebi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <textarea class="form-control" id="exampleTextarea" rows="3"></textarea>
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
                                <button type="submit" class="btn btn-brand">Süreci Başlat</button>
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
<!--End:: App Aside-->
@section("js")
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
