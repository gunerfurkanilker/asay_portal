@extends("ik.template")

@section("content")
    <!-- begin:: Subheader -->
    <div class="kt-subheader   kt-grid__item" id="kt_subheader">
        <div class="kt-container  kt-container--fluid ">
            <div class="kt-subheader__main">
                <h3 class="kt-subheader__title">
                    Dashboard </h3>
                <span class="kt-subheader__separator kt-hidden"></span>
            </div>
        </div>
    </div>

    <!-- end:: Subheader -->
    <!-- begin:: Content -->
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="row">
            <div class="col-lg-6">

                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--tab">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
												<span class="kt-portlet__head-icon kt-hidden">
													<i class="la la-gear"></i>
												</span>
                            <h3 class="kt-portlet__head-title">
                                Column Chart 1
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body">
                        <div id="kt_gchart_1" style="height:500px;"></div>
                    </div>
                </div>

                <!--end::Portlet-->
            </div>
            <div class="col-lg-6">

                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--tab">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
												<span class="kt-portlet__head-icon kt-hidden">
													<i class="la la-gear"></i>
												</span>
                            <h3 class="kt-portlet__head-title">
                                Column Chart 2
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body">
                        <div id="kt_gchart_2" style="height:500px;"></div>
                    </div>
                </div>

                <!--end::Portlet-->
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">

                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--tab">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
												<span class="kt-portlet__head-icon kt-hidden">
													<i class="la la-gear"></i>
												</span>
                            <h3 class="kt-portlet__head-title">
                                Pie Chart 1
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body">
                        <div id="kt_gchart_3" style="height:500px;"></div>
                    </div>
                </div>

                <!--end::Portlet-->
            </div>
            <div class="col-lg-6">

                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--tab">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
												<span class="kt-portlet__head-icon kt-hidden">
													<i class="la la-gear"></i>
												</span>
                            <h3 class="kt-portlet__head-title">
                                Pie Chart 2
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body">
                        <div id="kt_gchart_4" style="height:500px;"></div>
                    </div>
                </div>

                <!--end::Portlet-->
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">

                <!--begin::Portlet-->
                <div class="kt-portlet kt-portlet--tab">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
												<span class="kt-portlet__head-icon kt-hidden">
													<i class="la la-gear"></i>
												</span>
                            <h3 class="kt-portlet__head-title">
                                Line Chart 1
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body">
                        <div id="kt_gchart_5" style="height:500px;"></div>
                    </div>
                </div>

                <!--end::Portlet-->
            </div>
        </div>
    </div>

    <!-- end:: Content -->
@endsection

@section("js")
    <script src="assets/js/pages/components/charts/google-charts.js" type="text/javascript"></script>
@endsection
@section("vjs")
    <script src="//www.google.com/jsapi" type="text/javascript"></script>
@endsection
