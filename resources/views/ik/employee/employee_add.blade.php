@extends("ik.template")

@section("content")
    <!-- begin:: Subheader -->
    <div class="kt-subheader   kt-grid__item" id="kt_subheader">
        <div class="kt-container  kt-container--fluid ">
            <div class="kt-subheader__main">
                <h3 class="kt-subheader__title">
                    Yeni Çalışan Ekle </h3>
                <span class="kt-subheader__separator kt-hidden"></span>
            </div>
        </div>
    </div>

    <!-- end:: Subheader -->
    <!-- begin:: Content Kimlik Bilgileri-->
    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Kimlik Bilgileri
                    </h3>
                </div>
            </div>
            <form class="kt-form kt-form--label-right" action="{{route("leave_list")}}" method="get">
                <div class="kt-portlet__body">
                    <!--begin::Section-->
                    <div class="kt-section">
                        <div class="kt-section__content">
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Uyruğu</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="nationality">
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality->Id }}">{{ $nationality->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">T.C Kimlik No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="tcno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Kimlik Seri No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="tcno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Adı</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="firstname" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Soyadı</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="lastname" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Doğum Tarihi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="birthdate" type="date" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Cinsiyeti</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="nationality">
                                            @foreach ($genders as $gender)
                                                <option value="{{ $gender->Id }}">{{ $gender->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Son Geçerlilik Tarihi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="lasteffectivedate" type="date" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Anne Adı</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="parentmom" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Baba Adı</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="parentfather" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Doğum Yeri</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="birthplace" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Nüfusa Kayıtlı Olduğu İl</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="city">
                                            <option value="" selected>-- İl Seçiniz --</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->Id }}">{{ $city->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Nüfusa Kayıtlı Olduğu İlçe</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="nationality" disabled>
                                            <option name="district" value="" selected>-- Lütfen Önce İl Seçiniz. --
                                            </option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->Id }}">{{ $city->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Nüfusa Kayıtlı Olduğu Mahalle /
                                    Köy</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="neighborhood" type="text" class="form-control"
                                               placeholder="Lütfen İlçe Seçiniz." disabled/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Cilt No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="coverno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Sayfa No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="pageno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Kütük No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="registerno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Nüfus Cüzdanı Veriliş Tarihi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="idgivendate" type="date" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->
                </div>
            </form>
            <!--end::Form-->
        </div>
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Kişisel Bilgiler
                    </h3>
                </div>
            </div>
            <form class="kt-form kt-form--label-right" action="{{route("leave_list")}}" method="get">
                <div class="kt-portlet__body">
                    <!--begin::Section-->
                    <div class="kt-section">
                        <div class="kt-section__content">
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Mobil Telefon (Kişisel)</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="owntelno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Ev Telefonu (Kişisel)</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="ownhometelno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">E-Posta (Kişisel)</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="ownemail" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">KEP Adresi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="remmail" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Adres</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <textarea name="address" type="text" class="form-control" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Yaşadığı Ülke</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="districtlivein">
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->Id }}" @if($country->id == 1) selected @endif>{{ $country->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">İl</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="citylivein">
                                            <option name="district" value="" selected disabled>-- İl Seçiniz. --</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->Id }}">{{ $city->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">İlçe</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="districtlivein" disabled>
                                            <option name="district" value="" selected>-- Lütfen Önce İl Seçiniz. --
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Posta Kodu</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="zipcode" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Eğitim Durumu</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="educationstatus">
                                            <option name="district" value="" selected disabled>--Seçiniz. --</option>
                                            @foreach ($educationstatus as $status)
                                                <option value="{{ $status->Id }}">{{ $status->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Son Tamamlanan Eğitim Kurumu</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="lastgraduate" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Tamamlanan En Yüksek Eğitim Seviyesi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <select class="form-control" name="educationlevel">
                                            <option value="" selected disabled>--Seçiniz. --</option>
                                            @foreach ($educationlevels as $educationlevel)
                                                <option value="{{ $educationlevel->Id }}">{{ $educationlevel->Sym }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Nüfusa Kayıtlı Olduğu Mahalle /
                                    Köy</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="neighborhood" type="text" class="form-control"
                                               placeholder="Lütfen İlçe Seçiniz." disabled/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Cilt No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="coverno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Sayfa No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="pageno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Kütük No</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="registerno" type="text" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-xl-3 col-lg-3 col-form-label">Nüfus Cüzdanı Veriliş Tarihi</label>
                                <div class="col-lg-9 col-xl-6">
                                    <div class="input-group">
                                        <input name="idgivendate" type="date" class="form-control"/>
                                    </div>
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
                                <button type="submit" class="btn btn-brand">Kaydet</button>
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
