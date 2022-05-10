<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="sidebar-brand d-flex align-items-center pr-4 /*justify-content-center*/" href="/">
        <img src="/assets/images/logo_header.png" alt="">
    </a>
    <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

    <div class="input-group"></div>

    <!-- 우측버튼 -->
    <ul class="navbar-nav ml-auto ml-md-0">
		<li class="nav-item dropdown">
            {? USER.id}
            <a class="text-white dropdown-toggle" type="button" id="defaultDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                {USER.id}
            </a>

            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                {? USER.manager_level >= 4}
                <a class="dropdown-item" href="/menu/lists">메뉴관리</a>
                <div class="dropdown-divider"></div>
                {/}
                <a class="dropdown-item" href="/logout">로그아웃</a>
            </div>
            {/}
        </li>
    </ul>
</nav>