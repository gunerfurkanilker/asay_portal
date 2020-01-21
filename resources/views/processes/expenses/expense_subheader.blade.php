<div class="kt-subheader  kt-grid__item" id="kt_subheader">
    <div class="kt-container  kt-container--fluid ">
        <div class="kt-subheader__main">

            <h3 class="kt-subheader__title">{{$Title}}</h3>

            <span class="kt-subheader__separator kt-subheader__separator--v"></span>

            <a href="{{route("expense_add")}}" class="btn btn-label-warning btn-bold btn-sm btn-icon-h kt-margin-l-10">
                Yeni Masraf Ekle
            </a>
        </div>
        <div class="kt-subheader__toolbar">
            <div class="kt-subheader__wrapper">
                <a href="{{route("expense_list")}}" class="btn kt-subheader__btn-secondary @if($SubheaderMenu=="expense_list") active @endif">Masraflarım</a>

                <a href="{{route("expense_manager_list")}}" class="btn kt-subheader__btn-secondary @if($SubheaderMenu=="expense_manager_list") active @endif">Onay Bekleyenler</a>

                <a href="{{route("expense_accounting_list")}}" class="btn kt-subheader__btn-secondary @if($SubheaderMenu=="expense_accounting_list") active @endif">Muhasebe Onayı</a>
            </div>
        </div>
    </div>
</div>
<!-- begin:: Content -->
