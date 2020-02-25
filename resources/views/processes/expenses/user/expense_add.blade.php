@extends("ik.template")

@section("content")
    @include("processes.expenses.expense_subheader")
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        {{$Title}}
                    </h3>
                </div>
            </div>
            <form class="kt-form kt-form--label-right" action="{{route("expense_add")}}" method="post" id="ExpenseForm">
                @csrf
                <div class="kt-portlet__body">
                    <!--begin::Section-->
                    <div class="kt-section">
                        <div class="kt-section__content">
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Başlık</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="NAME">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Masraf Şekli</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="MASRAF_SEKLI">
                                            <option value="Seyahat Avansı">Seyahat Avansı</option>
                                            <option value="İş Avansı">İş Avansı</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Masraf Türleri</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" id="masraf_pturu" name="EXPENSE_TYPE">
                                            <option value="project">Proje</option>
                                            <option value="toplanti">Toplantı</option>
                                            <option value="firsat">Fırsat</option>
                                            <option value="BTXİDRİSL">İdari İşler Masrafı</option>
                                            <option value="BTXSATPAZ">Satış ve Pazarma</option>
                                            <option value="BTXMİSTEMS">Misafir Temsil Ağırlama</option>
                                            <option value="BTXMSTURKCELL">Turkcell MS Marmara Projesi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row project toplanti firsat mpturu">
                                <label class="col-xl-3 col-lg-3 col-form-label mplabel">Kodu Giriniz</label>
                                <div class="col-lg-5 col-xl-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control"  id="EXPENSE_TYPE_VALUE" name="EXPENSE_TYPE_VALUE" value="">
                                    </div>
                                </div>
                                <label class="col-lg-4 col-xl-3 col-form-label text-left kodtanim"></label>
                            </div>
                            <div class="form-group row form-group-last">
                                <label class="col-xl-3 col-lg-3 col-form-label">Açıklama</label>
                                <div class="col-lg-9 col-xl-6">
                                    <textarea class="form-control" name="CONTENT" rows="3">Masraf Açıklama</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end::Section-->
                </div>
                <div class="kt-portlet__foot">
                    <div class="kt-form__actions">
                        <div class="row">
                            <div class="col-lg-3 col-xl-3">
                            </div>
                            <div class="col-lg-9 col-xl-9">
                                <input type="hidden" name="expense_id" value="">
                                <button type="submit" class="btn btn-success">Kaydet</button>
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

@section('js')
    <script>
        $("#ExpenseForm").submit(function(){
            if($("#masraf_pturu").val()=='project' && $(".kodtanim").html()=='<font color="red"><b>Proje Kodu CRM de Açılması Gerekli</b></font>')
            {
                alert("Proje kodu CRM'de bulunamamıştır. CRM'de ilgili proje kodunu açtırıp tekrar deneyiniz.");
                return false;
            }
        });
    </script>
    <script>
        $("input,textarea").keyup(function(){
            var letters = { "i": "İ", "ş": "Ş", "ğ": "Ğ", "ü": "Ü", "ö": "Ö", "ç": "Ç", "ı": "I" };
            this.value = this.value.replace(/(([iışğüçö]))/g, function(letter){ return letters[letter]; })
            this.value = this.value.toUpperCase();
        });
    </script>
    <script>
        $(this).ready(function(){
            $(".mpturu").hide();
            $("#masraf_pturu").change(function(){
                $(".mpturu").hide();
                $("#EXPENSE_TYPE_VALUE").val("");
                $("."+$("#masraf_pturu").val()).show();
                if($("#masraf_pturu :selected").val()=="BTXİDRİSL" || $("#masraf_pturu :selected").val()=="BTXSATPAZ" || $("#masraf_pturu :selected").val()=="BTXMİSTEMS" || $("#masraf_pturu :selected").val()=="BTXMSTURKCELL")
                {
                    $(".mpturu").hide();
                }
                else
                {
                    $(".mplabel").html($("#masraf_pturu :selected").text()+" Kodu Giriniz");
                }
            });
        });
        $("#masraf_pturu").ready(function(){
            $(".mpturu").hide();
            $("."+$("#masraf_pturu").val()).show();
            if($("#masraf_pturu :selected").val()=="BTXİDRİSL" || $("#masraf_pturu :selected").val()=="BTXSATPAZ" || $("#masraf_pturu :selected").val()=="BTXMİSTEMS" || $("#masraf_pturu :selected").val()=="BTXMSTURKCELL")
            {
                $(".mpturu").hide();
            }
            else
            {
                $(".mplabel").html($("#masraf_pturu :selected").text()+" Kodu Giriniz");
            }
        });
    </script>

    <script>
        $("#EXPENSE_TYPE_VALUE").change(function(){
            if($("#masraf_pturu").val()=="project")
            {
                $.ajax({
                    type: "GET",
                    url: "{{$api_url}}processes/expense/getCrmProjectCode",
                    data: {
                        ProjeKodu: $("#EXPENSE_TYPE_VALUE").val(),
                        token: "{{session("user")->token}}"
                    },
                    success: function(response){
                        var tt = "";
                        var gz = "";
                        if(response!=null)
                        {
                            tt = "<font color=green><b>Proje Bulundu. </b></font>"+response.data[0].salesorderidname;
                        }
                        else
                        {
                            var tt = "<font color=red><b>Proje Kodu CRM de Açılması Gerekli</b></font>";
                        }
                        $(".kodtanim").html(tt);
                    }
                });
            }
        });
    </script>
@endsection
