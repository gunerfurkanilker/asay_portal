<div class="kt-subheader  kt-grid__item" id="kt_subheader">
    <div class="kt-container  kt-container--fluid ">
        <div class="kt-subheader__main">

            <h3 class="kt-subheader__title">{{$Title}}</h3>

            <span class="kt-subheader__separator kt-subheader__separator--v"></span>

            <a href="{{route("overtime_add")}}" class="btn btn-label-warning btn-bold btn-sm btn-icon-h kt-margin-l-10">
                Yeni Ekle
            </a>
        </div>
        <div class="kt-subheader__toolbar">
            <div class="kt-subheader__wrapper">
                <a href="{{route("overtime_list")}}" class="btn kt-subheader__btn-secondary @if($SubheaderMenu=="overtime_list") active @endif">Taleplerim</a>
                <a href="{{route("overtime_approval_list")}}" class="btn kt-subheader__btn-secondary @if($SubheaderMenu=="overtime_approval_list") active @endif">Onay Bekleyenler</a>
            </div>
        </div>
    </div>
</div>
<!-- begin:: Content -->
