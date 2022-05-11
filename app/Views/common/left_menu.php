<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            {@ LEFTMENU}
            <a class="nav-link collapsed" href="#collapseExample{.key_}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseExample{.key_}">
                {.name}
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>

            <div class="collapse multi-collapse" id="collapseExample{.key_}">
                <nav class="sb-sidenav-menu-nested nav">
                    {@ .sub}
                    {? USER.level >= ..protect}
                    <div class="mb-2">
                        <a href="{..link}" class="text-decoration-none {? ..link==MENU.uri_string}text-warning{:}text-secondary{/}">{..name}</a>
                    </div>
                    {/}
                    {/}
                </nav>
            </div>
            {/}
        </div>
    </div>
    {@ LEFTMENU}
    {@ .sub}{? ..link==MENU.uri_string}
    <input type="hidden" name="sel_admin_leftmenu" value="collapseExample{.key_}" />
    {/}{/}
    {/}
</nav>

