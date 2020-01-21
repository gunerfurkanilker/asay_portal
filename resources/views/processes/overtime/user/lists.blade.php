@extends("ik.template")

@section("content")
    @include("processes.overtime.overtime_subheader")
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Taleplerim
                    </h3>
                </div>
            </div>
            <div class="kt-portlet__body">
                <!--begin::Section-->
                <div class="kt-section">
                    <div class="kt-section__content">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Talep Eden</th>
                                    <th>Başlangıç Tarihi</th>
                                    <th>Bitiş Tarihi</th>
                                    <th>Talep Edilen Süre</th>
                                    <th>Açıklama</th>
                                    <th>Onay Durumu</th>
                                    <td>Süreç</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><a class="kt-link" href="{{route("overtime_view",["overtime_id"=>1])}}">Serkan Erdinç</a></td>
                                    <td>11.11.2019 09:00:00</td>
                                    <td>12.11.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Açıklama</td>
                                    <td><span class="kt-badge kt-badge--success kt-badge--inline kt-badge--pill kt-badge--rounded">Onaylandı</span></td>
                                    <td><a class="kt-link" href="{{route("overtime_logs",["overtime_id"=>1])}}">Süreç</a></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><a class="kt-link" href="{{route("overtime_view",["overtime_id"=>3])}}">Serkan Erdinç</a></td>
                                    <td>11.10.2019 09:00:00</td>
                                    <td>12.10.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Açıklama</td>
                                    <td><span class="kt-badge kt-badge--warning kt-badge--inline kt-badge--pill kt-badge--rounded">Yönetici Onayı Bekliyor</span></td>
                                    <td><a class="kt-link" href="{{route("overtime_logs",["overtime_id"=>1])}}">Süreç</a></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td><a class="kt-link" href="{{route("overtime_view",["overtime_id"=>4])}}">Serkan Erdinç</a></td>
                                    <td>11.09.2019 09:00:00</td>
                                    <td>12.09.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Açıklama</td>
                                    <td><span class="kt-badge kt-badge--danger kt-badge--inline kt-badge--pill kt-badge--rounded">IK Onayı Bekliyor</span></td>
                                    <td><a class="kt-link" href="{{route("overtime_logs",["overtime_id"=>1])}}">Süreç</a></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td><a class="kt-link" href="{{route("overtime_view",["overtime_id"=>5])}}">Serkan Erdinç</a></td>
                                    <td>11.09.2019 09:00:00</td>
                                    <td>12.09.2019 09:00:00</td>
                                    <td>1 Gün</td>
                                    <td>Açıklama</td>
                                    <td><span class="kt-badge kt-badge--info kt-badge--inline kt-badge--pill kt-badge--rounded">Belge Teslimi Bekliyor</span></td>
                                    <td><a class="kt-link" href="{{route("overtime_logs",["overtime_id"=>1])}}">Süreç</a></td>
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
