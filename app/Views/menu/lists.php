<div class="container-fluid p-3">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="font-weight-bold text-primary float-left">{HTML.title}</h6>
                    <button type="button" onclick="category.regist();" class="btn btn-primary btn-sm float-right">카테고리 <i class="fa-solid fa-plus"></i></button>
                </div>
            </div>
            <br />
            <div class="admin_wrap form-inline">
                <div id="js_sortTable" class="admin_wrap_inner" style="border-bottom: 1px solid #eee;">
                    {@ LIST}
                    {=form_open('menu/lists/sort')}
                    <div class="unit">
                        <p class="title">
                            {? .index_ > 0}
                            <span class="btn_menu_sort pointer" data-idx="{.idx}" data-sort="prev"><i class="fa-solid fa-circle-chevron-left"></i></span>
                            {/}
                            <span onclick="category.modify({.idx});" class="pointer">[ {.name} ]</span>
                            {? .index_ + 1 < .size_}
                            <span class="btn_menu_sort pointer" data-idx="{.idx}" data-sort="next"><i class="fa-solid fa-circle-chevron-right"></i></span>
                            {/}
                        </p>
                        {@ .sub}
                        <div class="column">
                            <input type="hidden" name="category_idx" value="{.idx}">
                            <input class="test" type="hidden" name="sort[{..idx}]">
                            <div class="check">
                                <input type="checkbox" class="input_check" name="chk_idx[]" id="{..idx}" value="{..idx}" {? ..state != 0}checked{/} onclick="javascript: return false;">
                            </div>
                            <p class="text" title="{..name}">
                                <a href="#" class="btn_menu_modi" rel="{..idx}">{..name}</a></p>
                            <div class="btn_Sortable"><a href="#"><span></span></a></div>
                        </div>
                        {/}
                        <div class="p-1">
                            <button class="btn btn-primary btn-sm btn_menu_regist" rel="{=.idx}" type="button" title="메뉴 추가"><i class="fa-solid fa-plus"></i></button>
                            {? sub.size_ > 0}
                            <button class="btn btn-success btn-sm" title="정렬 저장"><i class="fa-solid fa-floppy-disk"></i></button>
                            {/}
                        </div>
                    </div>
                    {=form_close()}
                    {/}
                </div>
            </div>
        </div>
    </div>
</div>
