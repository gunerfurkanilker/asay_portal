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
            <form class="kt-form kt-form--label-right">
                <div class="kt-portlet__body">
                    <!--begin::Section-->
                    <div class="kt-section">
                        <div class="kt-section__content">
                            <div class="form-group row border-bottom pb-3">
                                <label class="col-xl-3 col-lg-3 col-form-label">Belge Türü</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="BELGE_TYPE" id="belge_type">
                                            <option selected value="Fişli">Fişli</option>
                                            <option value="Faturalı">Fatura</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row" id="DivCari">
                                <label for="cari" class="col-xl-3 col-lg-3 col-form-label">Firma Ara</label>
                                <div class="col-lg-9 col-xl-6">
                                    <input id="cari" type="text" name="cari" placeholder="" value="" size="30" class="form-control">
                                    <input type="hidden" id="NetsisCariKod" name="NetsisCariKod" value=""/>
                                </div>
                            </div>
                            <div class="form-group row" id="DivCariYok">
                                <label for="cari_yok" class="col-xl-3 col-lg-3 col-form-label">Cariyi Bulamadım</label>
                                <div class="col-lg-9 col-xl-6 mt-2">
                                    <input type="checkbox" name="CARI_TIP" id="cari_yok" value="1">
                                </div>
                            </div>



                            <div class="cari_bilgileri border-bottom mb-3">
                                <div class="form-group row">
                                    <label for="CariIsim" class="col-xl-3 col-lg-3 col-form-label">Firma Ünvanı</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariIsim" type="text" name="CariIsim" placeholder="" value="" size="30" class="form-control">
                                        <input type="hidden"  name="CariID" value=""/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariUlkeKodu" class="col-xl-3 col-lg-3 col-form-label">Ülke(TR)</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariUlkeKodu" type="text" name="CariUlkeKodu" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariIl" class="col-xl-3 col-lg-3 col-form-label">İl</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariIl" type="text" name="CariIl" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariIlce" class="col-xl-3 col-lg-3 col-form-label">İlçe</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariIlce" type="text" name="CariIlce" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariAdres" class="col-xl-3 col-lg-3 col-form-label">Adres</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <textarea id="CariAdres" name="CariAdres" cols="40" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariVergiDairesi" class="col-xl-3 col-lg-3 col-form-label">Vergi Dairesi</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariVergiDairesi" type="text" name="CariVergiDairesi" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariVergiNo" class="col-xl-3 col-lg-3 col-form-label">Vergi Numarası/T.C. Kimlik No</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariVergiNo" type="text" name="CariVergiNo" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariTelefon" class="col-xl-3 col-lg-3 col-form-label">Telefon</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariTelefon" type="text" name="CariTelefon" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="CariFax" class="col-xl-3 col-lg-3 col-form-label">Fax</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input id="CariFax" type="text" name="CariFax" placeholder="" value="" size="30" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="belge_bilgileri">
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Belge No</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Username" value="123123">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Tarih</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="kt_datepicker_1" readonly placeholder="Select date" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Para Birimi</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group">
                                            <select class="form-control" id="exampleSelect1">
                                                <option selected>Türk Lirası</option>
                                                <option>USD</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Belge Resmi</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group">
                                            <input type="file" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->

                    <div class="kt-section__content">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="7" class="">Belge Tipi "Fişli" girişlerde masraf kalemlerinizi tek satır olarak giriş yapabilirsiniz.</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-center">MASRAF SATIRLARI</th>
                                <th style="text-align: right"><button type="button" class="btn btn-success btn-sm addRow">Yeni Satır</button></th>
                            </tr>
                            <tr>
                                <th>Gider Hesabı</th>
                                <th>Açıklama</th>
                                <th>Adet</th>
                                <th>Birim Fiyat</th>
                                <th>Kdv</th>
                                <th>KDVli Tutar</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody id="MasrafTable">
                                <tr>
                                    <td>
                                        <select class="form-control" name="GIDER_HESABI[]">
                                            <option value="">Test</option>
                                            <option value="">Test2</option>
                                            <option value="">Test3</option>
                                        </select>
                                    </td>
                                    <td><input required="" name="ACIKLAMA[]" size="50" value="" type="text" class="form-control"></td>
                                    <td class="adet"><input required="" name="ADET[]" size="25" value="1" type="text" class="form-control  text-right adet_input"></td>
                                    <td class="birim_fiyat"><input name="BIRIM_FIYAT[]" size="25" value="0.00" type="text" class="form-control para text-right birim_fiyat_input"></td>
                                    <td class="kdvoran">
                                        <select required="" name="KDVORAN[]" class="form-control text-right kdvoran_input">
                                            <option value="0">0</option>
                                            <option value="8">8</option>
                                            <option value="18">18</option>
                                        </select>
                                    </td>
                                    <td class="mtd"><input required="" readonly name="TUTAR[]" size="25" value="0.00" type="text" class="form-control money text-right"></td>
                                    <td>
                                        <input type="hidden" value="1" name="ELEMENT_ID"/>
                                        <button type="button" class="btn btn-danger btn-sm removeRow">Sil</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="originalTbody">
                                <tr class="original">
                                    <td>
                                        <select class="form-control" name="GIDER_HESABI[]">
                                            <option value="">Test</option>
                                            <option value="">Test2</option>
                                            <option value="">Test3</option>
                                        </select>
                                    </td>
                                    <td><input required="" name="ACIKLAMA[]" size="50" value="" type="text" class="form-control"></td>
                                    <td class="adet"><input required="" name="ADET[]" size="25" value="1" type="text" class="form-control  text-right adet_input"></td>
                                    <td class="birim_fiyat"><input name="BIRIM_FIYAT[]" size="25" value="0.00" type="text" class="form-control para text-right birim_fiyat_input"></td>
                                    <td class="kdvoran">
                                        <select required="" name="KDVORAN[]" class="form-control text-right kdvoran_input">
                                            <option value="0">0</option>
                                            <option value="8">8</option>
                                            <option value="18">18</option>
                                        </select>
                                    </td>
                                    <td class="mtd"><input required="" readonly name="TUTAR[]" size="25" value="0.00" type="text" class="form-control money text-right"></td>
                                    <td>
                                        <input type="hidden" value="1" name="ELEMENT_ID"/>
                                        <button type="button" class="btn btn-danger btn-sm removeRow">Sil</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="MasrafToplamlar">

                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
            <!--end::Form-->
        </div>
    </div>

    <!-- end:: Content -->
@endsection

@section("js")
    <script>
        $("#FormDocument").submit(function(){
            if(($("#belge_type").val()=="Faturalı") && (($("#cari_yok").prop("checked")==true && $("#CariIsim").val()=="") || ($("#cari_yok").prop("checked")==false && ($("#NetsisCariKod").val()=="" || $("#NetsisCariKod").val()==0)))){
                alert("Cari Bilgilerini Giriniz");
                return false;
            }
        });
    </script>
    <script>
        $("input,textarea").not( ".money" ).keyup(function(){
            var letters = { "i": "İ", "ş": "Ş", "ğ": "Ğ", "ü": "Ü", "ö": "Ö", "ç": "Ç", "ı": "I" };
            this.value = this.value.replace(/(([iışğüçö]))/g, function(letter){ return letters[letter]; });
            this.value = this.value.toUpperCase();
        });
    </script>
    <script>
        $(function(){
            $("#cari").change(function(){
                if($("#cari").val()=="")
                {
                    $("#NetsisCariKod").val("");
                    $("#cari_yok").prop("checked",true);
                }

                if($("#cari_yok").prop("checked")==true)
                {
                    $.ajax({
                        url: "/asay/expense/include/cari.php",
                        data: "CARIKOD="+$("#NetsisCariKod").val(),
                        dataType: "jsonp",
                        type:"get",
                        contentType: "application/x-www-form-urlencoded;charset=iso-8859-9",
                        success: function(data){
                            $("#CariIsim").val(data.CariIsim);
                            $("#CariUlkeKodu").val(data.CariUlkeKodu);
                            $("#CariIl").val(data.CariIl);
                            $("#CariIlce").val(data.CariIlce);
                            $("#CariAdres").val(data.CariAdres);
                            $("#CariVergiDairesi").val(data.CariVergiDairesi);
                            $("#CariVergiNo").val(data.CariVergiNo);
                            $("#CariTelefon").val(data.CariTelefon);
                            $("#CariFax").val(data.CariFax);
                            $("#NetsisCariKod").val(data.ID);
                        }
                    });
                    $(".cari_bilgileri").show();
                    $("#cari_yok").prop("checked",true);
                    $(".tamamla").remove();
                }
                else
                {
                    $(".cari_bilgileri").hide();
                    $("#cari_yok").prop("checked",false);
                }
            });
            $("#cari").autocomplete({
                source: function( request, response ) {
                    $.ajax({
                        url: "/asay/expense/include/cariler.php",
                        dataType: "jsonp",
                        type:"get",
                        contentType: "application/x-www-form-urlencoded;charset=iso-8859-9",
                        data: {
                            term: request.term
                        },
                        success: function(data){
                            response(data);
                        }
                    });
                },
                search: function() {
                    $("#NetsisCariKod").val("");
                    $("#cari_yok").prop("checked",true);
                    $("#cari_yok").change();
                },
                minLength: 3,
                select: function(event, ui){
                    $("#NetsisCariKod").val(ui.item.id);
                    $("#cari_yok").prop("checked",false);
                    $("#cari_yok").change();
                }
            });
        });
    </script>

    <script>

        $( ".tt" ).each(function(index) {
            $(".tt:eq("+index+")").attr("id","tarih"+index);
            $(".tt:eq("+index+")").attr("onclick","BX.calendar({node:this, field:'tarih"+index+"', form: 'form_', bTime: false, currentTime: '1532083042', bHideTime: false});");
        });

        var handler = function() {
            var ttutar=0;
            $("#MasrafToplamlar").html("");
            $("#MasrafTable>tr>.mtd").each(function(index) {
                ttutar = ttutar + parseFloat($(".money:eq("+index+")").val());
                $("#MasrafToplamlar").html("<tr><td colspan=5 class='text-right'>"+$(".ctd>.ct:eq(0) option:selected").text()+" Toplamı</td><td class='text-right masraftoplam'>"+ttutar+"</td><td></td></tr>");
            });
            var masraf = parseFloat($(".masraftoplam").html());
            $(".masraftoplam").html(masraf.toFixed(2));
        }

        $(".removeRow").click(function(){
            $(this).parent().parent().remove();
            $( ".tt" ).each(function(index) {
                $(".tt:eq("+index+")").attr("id","tarih"+index);
                $(".tt:eq("+index+")").attr("onclick","BX.calendar({node:this, field:'tarih"+index+"', form: 'form_', bTime: false, currentTime: '1532083042', bHideTime: false});");
            });

            $(this).ready(handler());
        });

        $(this).ready(handler());
        $(".ct,.money").bind("change",handler);
        var BelgeTypeChange = function() {
            if($("#belge_type").val()=="Fişli")
            {
                if($("#MasrafTable>tr>.mtd").length>1)
                {
                    alert("Masraf Kalemleri bir(1)'den fazla olduğu için Fişli seçimi yapamazsınız.");
                    $("#belge_type").val("Faturalı");
                }
                $("#DivCari").hide();
                $("#DivCariYok").hide();
            }
            else
            {
                $("#DivCari").show();
                $("#DivCariYok").show();
            }
        }
        $("#belge_type").change(BelgeTypeChange);
        $(this).ready(BelgeTypeChange());

        $(".addRow").click(function(){
            if($("#belge_type").val()=="Fişli")
            {
                return false;
            }
            var $row = $('.original').clone();
            $row.removeClass("original");
            $row.removeClass("hide");
            //$row.children().children("#tarih0").attr("id","tarih"+$('input[name="tarih[]"]').length);
            $("#MasrafTable").append($row);
            $("#MasrafTable").unbind();
            $( ".tt" ).each(function(index) {
                $(".tt:eq("+index+")").attr("id","tarih"+index);
                $(".tt:eq("+index+")").attr("id","tarih"+index);
                $(".tt:eq("+index+")").attr("onclick","BX.calendar({node:this, field:'tarih"+index+"', form: 'form_', bTime: false, currentTime: '1532083042', bHideTime: false});");
            });
            $(".removeRow").click(function(){
                $(this).parent().parent().remove();
                $( ".tt" ).each(function(index) {
                    $(".tt:eq("+index+")").attr("id","tarih"+index);
                    $(".tt:eq("+index+")").attr("onclick","BX.calendar({node:this, field:'tarih"+index+"', form: 'form_', bTime: false, currentTime: '1532083042', bHideTime: false});");
                });

                $(this).ready(handler());
            });
            $(this).ready(handler());

            $(".birim_fiyat_input,.adet_input,.kdvoran_input").unbind("change");
            $(".ct,.money").unbind();
            $(".para").unbind();
            $(".ct,.money").bind("change",handler);

            $("input,textarea").not( ".money" ).unbind();
            $("input,textarea").not( ".money" ).keyup(function(){
                var letters = { "i": "İ", "ş": "Ş", "ğ": "Ğ", "ü": "Ü", "ö": "Ö", "ç": "Ç", "ı": "I" };
                this.value = this.value.replace(/(([iışğüçö]))/g, function(letter){ return letters[letter]; })
                this.value = this.value.toUpperCase();
            });
            $(".birim_fiyat_input,.adet_input,.kdvoran_input").bind("change",tutarguncelle);
        });
    </script>

    <script>
        var tutarguncelle = function() {
            var adet 	= $(this).parent().parent().children(".adet").children().val();
            var kdvoran = $(this).parent().parent().children(".kdvoran").children().val();
            var birim_fiyat = $(this).parent().parent().children(".birim_fiyat").children().val();

            var tutar 	= (adet*birim_fiyat)*((kdvoran/100)+1);
            tutar = tutar.toFixed(2);
            $(this).parent().parent().children(".mtd").children().val(tutar);
            handler();
        }
        $(".birim_fiyat_input,.adet_input,.kdvoran_input").change(tutarguncelle);
    </script>
    <script>
        $(this).ready(function(){
            if($("#cari_yok").prop("checked")==true)
                $(".cari_bilgileri").show();
            else
                $(".cari_bilgileri").hide();
            $("#cari_yok").change(function(){
                if($(this).prop("checked")==true)
                    $(".cari_bilgileri").show();
                else
                    $(".cari_bilgileri").hide();
            });
        });

    </script>
@endsection

@section("vjs")
    <script
        src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
        integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
        crossorigin="anonymous"></script>
@endsection

@section("css")
    <style>
        .original,.OriginalPhoto {
            display:none;
        }
    </style>
@endsection
