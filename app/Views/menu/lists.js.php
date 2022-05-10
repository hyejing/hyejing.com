<script type="text/javascript">
var category = {
    regist: function() {
        js_popup('{C.URL_DOMAIN}/menu/category/regist', 'category_add', '600', '500');
    },
    modify: function(idx) {
        js_popup('{C.URL_DOMAIN}/menu/category/modify?idx='+idx, 'category_add', '600', '500');
    },
}

$(document).ready(function() {
    $('input:checkbox[name=chk_all]').click(function(){
        let name = $(this).val();
        let checkBoxes = $("input[name='"+name+"']");

        if( $(this).prop('checked') ){
            checkBoxes.prop('checked', true);
        }else{
            checkBoxes.prop('checked', false);
        }
    });

    $(".btn_menu_sort").on("click", function(e) {
        let idx  = $(this).attr('data-idx'),
            sort = $(this).attr('data-sort')
        location.href = '{C.URL_DOMAIN}/menu/lists/swap?idx=' + idx + '&sort=' + sort;
    });

    // 메뉴 추가 버튼
    $('.btn_menu_regist').click(function(){
        let parent = $(this).attr('rel')
        if ( typeof(parent) == 'undefined' ) {
            parent = '';
        }
        js_popup('{C.URL_DOMAIN}/menu/regist?parent='+parent, 'menu_add', '600', '500');
    });

    // 메뉴 수정 버튼
    $('.btn_menu_modi').click(function(){
        let idx = $(this).attr('rel')
        js_popup('{C.URL_DOMAIN}/menu/modify?idx='+idx, 'menu_modi', '600', '500');
    });

    // 목록 드래그 기능
    $.fn.sortTable = function(option){
        // option
        let options = $.extend({}, $.fn.sortTable.default, option);

        return this.each(function(){
            let $this = $(this)
                    , $listNode = $this.find(".column")
                    , $listButton = $this.find(".btn_Sortable a")
                    , $etcNode = ( options.etcNode ) ? $this.find(options.etcNode) : null
                    , value = options.value
                    , resultPos = null
                    , $test = $this.find(".test");

            // 리스트별 idx 정렬
            $listNode.each(function(i){
                $(this).attr({ value : i+1 });
            });

            $test.each(function(i){
                $(this).attr({ value : i+1 });
            });

            // 마우스 다운
            $listButton.bind("mousedown", function(e){
                let that = this
                        , downPosX = e.clientX
                        , downPosY = e.clientY
                        , thisDownPosY = downPosY - $this.offset().top
                        , nodePosY = downPosY - $(this).closest(".column").offset().top
                        , resetPosY = thisDownPosY - nodePosY
                        , listPosSort = null
                        , oldListPosSort = null;

                // 선택한 리스트 Css 및 Class 적용
                $(this).closest(".column").css({ position : "absolute", left : 0, top : resetPosY, zIndex : 100 }).addClass("column_active");

                // 선택된 노드 제외한 나머지 .column 리스트
                let $columns = $this.find(".column").not(".column_active");

                // 복사 엘리먼트 생성 및
                let $createList = createListCopy({ text : $(this).closest(".column").find(".btn_menu_modi").text() });
                $this.append( $createList );
                $createList.show();

                // 리스트별 사이 체크(Index)
                listPosSort = listScopePos({ posX : resetPosY });
                // 복사된 엘리먼트 이동
                $columns.eq(listPosSort).before( $createList );

                // 마우스 이동
                $(window).bind("mousemove", function(e){
                    let movePosX = e.clientX
                            , movePosY = e.clientY
                            , thisMovePosY = movePosY - $this.offset().top;

                    // 리스트 이동 영역 체크
                    let allDragScope = allScope({ posY : thisMovePosY - nodePosY })
                    if( allDragScope ){
                        $(that).closest(".column").css({ top : thisMovePosY - nodePosY });

                        // 리스트별 사이 체크(Index)
                        listPosSort = listScopePos({ posX : thisMovePosY - nodePosY });

                        // listPosSort Index가 변경될경우만 실행
                        if( listPosSort != oldListPosSort ){
                            if( listPosSort == $columns.length ){
                                $columns.eq($columns.length-1).after( $createList );
                            } else {
                                $columns.eq(listPosSort).before( $createList );
                            }
                        }

                        oldListPosSort = listPosSort;
                    }
                });
                // 마우스 업
                $(window).bind("mouseup", function(){
                    $(window).unbind("mousemove");
                    $(this).unbind("mouseup");

                    // 복사된 엘리먼트 삭제
                    $createList.remove();

                    // 노드 비활성화
                    $(that).closest(".column").removeClass("column_active").removeAttr("style");

                    // 변경된 노드 한번 실행
                    if( listPosSort == $columns.length ){
                        $columns.eq($columns.length-1).after( $(that).closest(".column") );
                    } else {
                        $columns.eq(listPosSort).before( $(that).closest(".column") );
                    }

                    // 기존 column으로 적용
                    $listNode = $this.find(".column");
                    $listButton = $listNode.find(".text");
                    $test = $this.find(".test");

                    // 리스트별 idx 정렬
                    $listNode.each(function(i){
                        $(this).attr({ value : i+1 });
                    });

                    $test.each(function(i){
                        $(this).attr({ value : i+1 });
                    });
                });

                return false;
            });

            // 전체 영역 범위
            function allScope(m){
                let listHeight = $listNode.outerHeight()
                        , scopePosY = (listHeight * $listNode.length) + (( $etcNode && $etcNode.length ) ? $etcNode.outerHeight() : 0)
                        , totalPosY = scopePosY - $listNode.outerHeight();

                if( $etcNode && $etcNode.length ){
                    resultPos = !(m.posY < $etcNode.outerHeight() || m.posY >= totalPosY);
                } else {
                    resultPos = !(m.posY <= 0 || m.posY >= totalPosY);
                }

                return resultPos;
            }

            // 리스트와의 사이 영역
            function listScopePos(m){
                let $columnList = $this.find(".column").not(".column_active")
                        , columnHeight = $this.find(".column").outerHeight()
                        , currentPosY = m.posX
                        , listSortPos = null;

                for( let i=0; i<=$columnList.length; i++ ){
                    if( $etcNode ){
                        if( currentPosY > $etcNode.outerHeight()+((columnHeight*i)-(columnHeight/2)) && currentPosY <= $etcNode.outerHeight()+((columnHeight*i)+(columnHeight/2)) ){
                            return listSortPos = i;
                        }
                    } else {
                        if( currentPosY > (columnHeight*i)-(columnHeight/2) && currentPosY <= (columnHeight*i)+(columnHeight/2) ){
                            return listSortPos = i;
                        }
                    }
                }
            }

            // 복사될 리스트 생성
            function createListCopy(m){
                let listText = m.text;

                let columns = $("<div></div>").addClass("column column_disabled")
                .append(
                    $("<div></div>").addClass("check")
                            .append( $("<input />").attr({ type : "checkbox", name : "value" }).addClass("input_check") )
                )
                .append(
                    $("<p></p>").addClass("text")
                            .append( $("<a href='#'></a>").text(listText) )
                );

                return columns;
            }
        });
    }
    /*
        === options ===
        etcNode : "클래스 이름" ----- 이동할 순수영역을 제외한 필요없는 노드 클래스
        value : "idx" ----- 정렬했을경우 추가할 index 목록
    */
    $.fn.sortTable.default = {
        etcNode : null,
        value : "value"
    }
    $("#js_sortTable .unit").sortTable({ etcNode : ".title", value : "value" });


    // 스크롤 생성
    let wrapWidth = $(".admin_wrap_inner")
            , unitWidth = $(".unit").width() * $(".unit").length;
    wrapWidth.css( "width" , unitWidth );
    $(".unit").css("height", wrapWidth.outerHeight());
});
</script>
