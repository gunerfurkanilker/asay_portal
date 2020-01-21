<div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
    <div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1" data-ktmenu-dropdown-timeout="500">
        <ul class="kt-menu__nav ">
            <li class="kt-menu__item @if($menu=="home") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("home")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-home"></i><span class="kt-menu__link-text">Ana Sayfa</span></a></li>
            <li class="kt-menu__item @if($menu=="structure") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("structure")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-map"></i><span class="kt-menu__link-text">Şirket Yapısı</span></a></li>
            <li class="kt-menu__item @if($menu=="employee_list") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("employee_list")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-profile"></i><span class="kt-menu__link-text">Çalışanlar</span></a></li>
            <li class="kt-menu__item @if($menu=="projects") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("projects")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-presentation"></i><span class="kt-menu__link-text">Projeler</span></a></li>
            <li class="kt-menu__item @if($menu=="processes") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("processes")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-network"></i><span class="kt-menu__link-text">Süreçler</span></a></li>
            <li class="kt-menu__item  kt-menu__item--submenu" aria-haspopup="true" data-ktmenu-submenu-toggle="hover"><a href="javascript:;" class="kt-menu__link kt-menu__toggle"><i class="kt-menu__link-icon flaticon2-add-square"></i><span class="kt-menu__link-text">Talep</span><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
                <div class="kt-menu__submenu "><span class="kt-menu__arrow"></span>
                    <ul class="kt-menu__subnav">
                        <li class="kt-menu__item  kt-menu__item--parent" aria-haspopup="true"><span class="kt-menu__link"><span class="kt-menu__link-text">Talep</span></span></li>
                        <li class="kt-menu__item " aria-haspopup="true"><a href="{{route("leave_add")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-file"><span></span></i><span class="kt-menu__link-text">İzin</span></a></li>
                        <li class="kt-menu__item " aria-haspopup="true"><a href="layout/general/minimized-aside.html" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-notepad"><span></span></i><span class="kt-menu__link-text">İş Avansı</span></a></li>
                        <li class="kt-menu__item " aria-haspopup="true"><a href="{{route("expense_add")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-interface-9"><span></span></i><span class="kt-menu__link-text">Harcama</span></a></li>
                        <li class="kt-menu__item " aria-haspopup="true"><a href="{{route("overtime_add")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-time"><span></span></i><span class="kt-menu__link-text">Fazla Mesai</span></a></li>
                    </ul>
                </div>
            </li>
            <li class="kt-menu__item @if($menu=="calendar") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("calendar")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon2-calendar-9"></i><span class="kt-menu__link-text">Takvim</span></a></li>
            <li class="kt-menu__item @if($menu=="settings") kt-menu__item--active @endif" aria-haspopup="true"><a href="{{route("settings")}}" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-settings"></i><span class="kt-menu__link-text">Ayarlar</span></a></li>
            <li class="kt-menu__item @if($menu=="logout") kt-menu__item--active @endif" aria-haspopup="true"><a href="/ik/logout" class="kt-menu__link "><i class="kt-menu__link-icon flaticon-logout"></i><span class="kt-menu__link-text">Çıkış</span></a></li>
        </ul>
    </div>
</div>
