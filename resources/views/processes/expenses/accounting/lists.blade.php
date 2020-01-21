@extends("ik.template")

@section("content")
    @include("processes.expenses.expense_subheader")
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Muhasebe Onayı Bekleyen Masraflar
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
                                <th>Başlık</th>
                                <th>Açıklama</th>
                                <th>Masraf Tipi</th>
                                <th>Proje</th>
                                <th>İşlem Tarihi</th>
                                <th>Kullanıcı</th>
                                <th>Durumu</th>
                                <th>İşlemler</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>VODAFONE SERVICEDESK TOPLANTI</td>
                                <td>2 GÜNLÜK VODAFONE SERVICEDESK TOPLANTI</td>
                                <td>
                                    Toplantı




                                </td>
                                <td></td>
                                <td>17.06.2019 09:27</td>
                                <td>Serkan Erdinç</td>
                                <td>
                                    Tamamlandı
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-warning btn-sm" href="{{route("expense_print",["expense_id"=>1])}}"><i class="fa fa-edit"></i> Yazdır</a>
                                </td>
                            </tr>
                            <tr>
                                <td>SERVICE DESK TOPLANTI</td>
                                <td>SERVICE DESK TOPLANTI</td>
                                <td>



                                    Satış ve Pazarlama

                                </td>
                                <td></td>
                                <td>22.07.2019 10:45</td>
                                <td>Serkan Erdinç</td>
                                <td>
                                    Muhasebe Onayı Bekliyor
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-primary btn-sm" href="{{route("expense_accounting_view",["expense_id"=>2])}}"><i class="fa fa-eye"></i>Gör</a>
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

    <!-- end:: Content -->
@endsection
