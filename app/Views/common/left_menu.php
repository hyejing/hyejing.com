<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
	<div class="sb-sidenav-menu">
		<div class="nav">
			{@ LEFTMENU}
			<a class="nav-link collapsed" href="#collapseExample{.key_}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseExample{.key_}">
				{=.nName}
				<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
			</a>

			<div class="collapse multi-collapse" id="collapseExample{.key_}">
				<nav class="sb-sidenav-menu-nested nav">
					{@ .sub}
                        {? USER.manager_level >= ..nProtect}
					<div><a href="{..nLink}" class="text-decoration-none {? ..nLink==MENU.uri_string}text-warning{:}text-secondary{/}">{..nName}</a></div>
                        {/}
					{/}
				</nav>
			</div>
			{/}
		</div>
	</div>
    {@ LEFTMENU}
    {@ .sub}{? ..nLink==MENU.uri_string}
    <input type="hidden" name="sel_admin_leftmenu" value="collapseExample{.key_}" />
    {/}{/}
    {/}
</nav>

