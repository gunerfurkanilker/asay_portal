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
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-actions">
                        <a class="btn btn-outline-secondary btn-bold btn-sm @if ($type === "all") active @endif" href="{{route("leave_approval_list",["type"=>"all"])}}">
                            Tümü
                        </a>
                        <a class="btn btn-outline-secondary btn-bold btn-sm @if ($type === "manager") active @endif" href="{{route("leave_approval_list",["type"=>"manager"])}}">
                            Yönetici Onayı
                        </a>
                        <a class="btn btn-outline-secondary btn-bold btn-sm @if ($type === "hr") active @endif" href="{{route("leave_approval_list",["type"=>"hr"])}}">
                            IK Onayı
                        </a>
                        <a class="btn btn-outline-secondary btn-bold btn-sm @if ($type === "document") active @endif" href="{{route("leave_approval_list",["type"=>"document"])}}">
                            Belge Onayı
                        </a>
                        <a class="btn btn-outline-secondary btn-bold btn-sm @if ($type === "success") active @endif" href="{{route("leave_approval_list",["type"=>"success"])}}">
                            Tamamlandı
                        </a>
                    </div>

                </div>
            </div>
            <div class="kt-portlet__body">
                <!--begin::Section-->
                <div class="kt-section">
                    <div class="kt-section__content">
                        <div class="col-xl-3 offset-xl-9">
                            <!--begin:: Widgets/Stats2-3 -->
                            <div class="kt-widget1">
                                <div class="kt-widget1__item">
                                    <div class="kt-widget1__info">
                                        <h3 class="kt-widget1__title">Kalan Yıllık İzin</h3>
                                    </div>
                                    <span class="kt-widget1__number kt-font-success">8 Gün 5 Saat</span>
                                </div>
                            </div>
                            <!--end:: Widgets/Stats2-3 -->
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Talep Eden</th>
                                    <th>İzin Başlangıç Tarihi</th>
                                    <th>İŞe Başlayacağı Tarihi</th>
                                    <th>Talep Edilen Süre</th>
                                    <th>İzin Türü</th>
                                    <th>Onay Durumu</th>
                                    <th>İzin Sebebi</th>
                                    <td>İzin Süreçleri</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr @if ($type != "success" && $type != "all") hidden @endif>
                                    <td>1</td>
                                    <td><a class="kt-link" href="{{route("leave_approval_view",["leave_id"=>1])}}">Serkan Erdinç</a></td>
                                    <td>11.11.2019 09:00:00</td>
                                    <td>12.11.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Yıllık İzin</td>
                                    <td><span class="kt-badge kt-badge--success kt-badge--inline kt-badge--pill kt-badge--rounded">Onaylandı</span></td>
                                    <td>İzinin neden alındığı</td>
                                    <td><a class="kt-link" href="{{route("leave_logs",["leave_id"=>1])}}">Süreç</a></td>
                                </tr>
                                <tr @if ($type != "manager" && $type != "all") hidden @endif>
                                    <td>2</td>
                                    <td><a class="kt-link" href="{{route("leave_approval_view",["leave_id"=>1])}}">Serkan Erdinç</a></td>
                                    <td>11.10.2019 09:00:00</td>
                                    <td>12.10.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Yıllık İzin</td>
                                    <td><span class="kt-badge kt-badge--warning kt-badge--inline kt-badge--pill kt-badge--rounded">Yönetici Onayı Bekliyor</span></td>
                                    <td>İzinin neden alındığı</td>
                                    <td><a class="kt-link" href="{{route("leave_logs",["leave_id"=>1])}}">Süreç</a></td>
                                </tr>
                                <tr @if ($type != "hr" && $type != "all") hidden @endif>
                                    <td>3</td>
                                    <td><a class="kt-link" href="{{route("leave_approval_view",["leave_id"=>1])}}">Serkan Erdinç</a></td>
                                    <td>11.09.2019 09:00:00</td>
                                    <td>12.09.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Yıllık İzin</td>
                                    <td><span class="kt-badge kt-badge--danger kt-badge--inline kt-badge--pill kt-badge--rounded">IK Onayı Bekliyor</span></td>
                                    <td>İzinin neden alındığı</td>
                                    <td><a class="kt-link" href="{{route("leave_logs",["leave_id"=>1])}}">Süreç</a></td>
                                </tr>
                                <tr @if ($type != "document" && $type != "all") hidden @endif>
                                    <td>4</td>
                                    <td><a class="kt-link" href="{{route("leave_approval_view",["leave_id"=>1])}}">Serkan Erdinç</a></td>
                                    <td>11.09.2019 09:00:00</td>
                                    <td>12.09.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Yıllık İzin</td>
                                    <td><span class="kt-badge kt-badge--info kt-badge--inline kt-badge--pill kt-badge--rounded">Belge Teslimi Bekliyor</span></td>
                                    <td>İzinin neden alındığı</td>
                                    <td><a class="kt-link" href="{{route("leave_logs",["leave_id"=>1])}}">Süreç</a></td>
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

    <!-- end:: Content -->
@endsection
