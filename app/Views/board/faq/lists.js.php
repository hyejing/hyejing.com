<script type="text/javascript">
    let faq_list = {
        search: function () {
            $("#faq_lists").attr("action", "{C.URL_DOMAIN}/board/faq/lists");
            $("#faq_lists").submit();
        },
        regist: function () {

            //js_popup('/board/faq/regist', 'aaa', '800', '800');
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/board/faq/regist');
            myModal.show();

        },
        modify: function (idx) {
            let myModal = new bootstrap.Modal($('#layer_dialog_lg'), {
                keyboard: false
            });

            $('#section_dialog_lg').load('{C.URL_DOMAIN}/board/faq/modify?idx=' + idx);
            myModal.show();
        },
    }

    $(function () {
        let $oTableSorted = $('.table-sorted tbody');

        $oTableSorted.sortable({
            handle: '.handle',
            placeholder: 'ui-state-highlight'
        });

        /**
         * 변경된 순서를 서버에 전송
         */
        $('#sort_btn').on('click', function () {
            let oParam = {sort: []};
            let $oCurrentEl;

            $('.table-sorted tbody tr').each(function (key) {
                $oCurrentEl = $(this);
                oFaq_idx = $oCurrentEl.find('#faq_idx').text() || {};
                oOriSort_idx = $oCurrentEl.find('#faq_sort').text() || {};
                if ($oCurrentEl.find('#faq_idx').length < 1) {
                    return true;
                }

                oParam.sort.push({
                    idx: oFaq_idx,
                    ori_sort: oOriSort_idx,
                    sort: key + 1
                });
            });

            if (oParam.sort.length < 1) {
                return false;
            }

            $.ajax({
                type: 'post',
                url: '{C.URL_DOMAIN}/board/faq/SortModify',
                dataType: 'json',
                data: oParam
            }).done((oRes) => {
                if (oRes.success === true) {
                    alert(oRes.msg);
                    location.reload();
                } else {
                    alert(oRes.msg);
                    console.log(oRes.data);
                    console.log(oRes.rCode);
                }
            });
        });
    });
</script>