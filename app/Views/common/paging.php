<!--{? HTML.pagination.type == 'link'}-->
<!--{? HTML.pagination.view == 'true'}-->
<div class="col-md-12 d-flex flex-row-reverse my-3">
    <ul class="pagination" style="font-size: 12px;">
        <!--{? HTML.pagination.first == 'true'}-->
        <li class="page-item">
            <a href="{HTML.pagination.current_url}{? HTML.pagination.link=='?'}page=1{:}{HTML.pagination.link}&page=1{/}" class="page-link rounded-circle mx-1"><i class="fas fa-angle-double-left"></i></a>
        </li>
        <!--{/}-->
        <!--{? HTML.pagination.prev == 'true'}-->
        <li class="page-item">
            <a href="{HTML.pagination.current_url}{? HTML.pagination.link=='?'}page={HTML.pagination.prev_page}{:}{HTML.pagination.link}&page={HTML.pagination.prev_page}{/}" class="page-link rounded-circle mx-1"><i class="fas fa-angle-left"></i></a>
        </li>
        <!--{/}-->

        <!--{@ HTML.pagination.pages}-->
        <!--{? HTML.pagination.now_page == .value_ }-->
        <li class="page-item active">
            <a href="{HTML.pagination.current_url}{? HTML.pagination.link=='?'}page={.value_}{:}{HTML.pagination.link}&page={.value_}{/}" class="page-link rounded-circle mx-1"> {.value_} </a>
        </li>
        <!--{:}-->
        <li class="page-item">
            <a href="{HTML.pagination.current_url}{? HTML.pagination.link=='?'}page={.value_}{:}{HTML.pagination.link}&page={.value_}{/}" class="page-link rounded-circle mx-1">{.value_} </a>
        </li>
        <!--{/}-->
        <!--{/}-->

        <!--{? HTML.pagination.next == 'true'}-->
        <li class="page-item">
            <a href="{HTML.pagination.current_url}{? HTML.pagination.link=='?'}page={HTML.pagination.next_page}{:}{HTML.pagination.link}&page={HTML.pagination.next_page}{/}" class="page-link rounded-circle mx-1"><i class="fas fa-angle-right"></i></a>
        </li>
        <!--{/}-->
        <!--{? HTML.pagination.last == 'true'}-->
        <li class="page-item">
            <a href="{HTML.pagination.current_url}{? HTML.pagination.link=='?'}page={HTML.pagination.last_page}{:}{HTML.pagination.link}&page={HTML.pagination.last_page}{/}" class="page-link rounded-circle mx-1"><i class="fas fa-angle-double-right"></i></a>
        </li>
        <!--{/}-->
    </ul>
</div>
<!--{/}-->
<!--{:}-->
<!--{? HTML.pagination.view == 'true'}-->
<!-- ### paging ### -->
<div class="col-md-12 d-flex flex-row-reverse my-3">
    <ul class="pagination" style="font-size: 12px;">
        <!--{? HTML.pagination.first == 'true'}-->
        <li class="page-item">
            <a href="javascript:;" onclick="{HTML.pagination.func_name}('1');" class="page-link rounded-circle mx-1"><i class="fas fa-angle-double-left"></i></a>
        </li>
        <!--{/}-->
        <!--{? HTML.pagination.prev == 'true'}-->
        <li class="page-item">
            <a href="javascript:;" onclick="{HTML.pagination.func_name}('{HTML.pagination.prev_page}');" class="page-link rounded-circle mx-1"><i class="fas fa-angle-left"></i></a>
        </li>
        <!--{/}-->

        <!--{@ HTML.pagination.pages}-->
        <!--{? HTML.pagination.now_page == .value_ }-->
        <li class="page-item active">
            <a href="javascript:;" onclick="{HTML.pagination.func_name}('{.value_}');" class="page-link rounded-circle mx-1">{.value_}</a>
        </li>
        <!--{:}-->
        <li class="page-item">
            <a href="javascript:;" onclick="{HTML.pagination.func_name}('{.value_}');" class="page-link rounded-circle mx-1">{.value_}</a>
        </li>
        <!--{/}-->
        <!--{/}-->

        <!--{? HTML.pagination.next == 'true'}-->
        <li class="page-item">
            <a href="javascript:;" onclick="{HTML.pagination.func_name}('{HTML.pagination.next_page}');" class="page-link rounded-circle mx-1"><i class="fas fa-angle-right"></i></a>
        </li>
        <!--{/}-->
        <!--{? HTML.pagination.last == 'true'}-->
        <li class="page-item">
            <a href="javascript:;" onclick="{HTML.pagination.func_name}('{HTML.pagination.last_page}');" class="page-link rounded-circle mx-1"><i class="fas fa-angle-double-right"></i></a>
        </li>
        <!--{/}-->
    </ul>
</div>
<!--{/}-->
<!--{/}-->