@extends("ik.template")

@section("content")
    @include("processes.overtime.overtime_subheader")
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
                        <a class="btn btn-outline-danger btn-bold btn-sm" href="{{route("overtime_list")}}"><i class="fa fa-undo"></i>Geri</a>
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
                                <th>Tarih</th>
                                <th>İşlem</th>
                                <th>Not</th>
                                <th>Durum</th>
                                <th>İşlemi Yapan</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>12.01.2019 15:00</td>
                                    <td>Fazla Mesai Girişi</td>
                                    <td>Onayı Yapıldı</td>
                                    <td>Giriş Başarılı</td>
                                    <td>Serkan Erdinç</td>
                                </tr>
                                <tr>
                                    <td>12.01.2019 15:00</td>
                                    <td>Fazla Mesai Girişi</td>
                                    <td>Onayı Yapıldı</td>
                                    <td>Giriş Başarılı</td>
                                    <td>Serkan Erdinç</td>
                                </tr>
                                <tr>
                                    <td>12.01.2019 15:00</td>
                                    <td>Fazla Mesai Girişi</td>
                                    <td>Onayı Yapıldı</td>
                                    <td>Giriş Başarılı</td>
                                    <td>Serkan Erdinç</td>
                                </tr>
                                <tr>
                                    <td>12.01.2019 15:00</td>
                                    <td>Fazla Mesai Girişi</td>
                                    <td>Onayı Yapıldı</td>
                                    <td>Giriş Başarılı</td>
                                    <td>Serkan Erdinç</td>
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
