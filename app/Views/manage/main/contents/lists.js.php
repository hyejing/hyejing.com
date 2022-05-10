<script>
    var sectionContent = {
        modify_info: function(idx) {
            let myModal = new bootstrap.Modal($('#layer_dialog'), {
                keyboard: false
            });

            $('#section_dialog').load('/manage/main/contents/modify?idx=' + idx);
            myModal.show();
        },
        modify: function(idx) {
            let oFormData = {
                'idx' : idx,
                'state' : $('input[name=state]:checked').val(),
            };

            $.ajax({
                url: '/manage/main/contents/modify/proc',
                method : 'POST',
                data: oFormData,
                dataType : 'json',
                success :function(oRes){
                    if (oRes.success === true) {
                        alert(oRes.msg);
                        location.reload();
                    } else {
                        alert(oRes.msg);
                        console.log(oRes.data);
                        console.log(oRes.code);
                    }
                }
            });
        },
        regist_layer: function() {
            $('.modal-backdrop').remove();
            let myModal = new bootstrap.Modal($('#layer_dialog'), {
                keyboard: false
            });

            $('#section_dialog').load('/manage/main/contents/regist');
            myModal.show();
        },
        regist: function() {
            let oFormData = {
                'bbs_idx' : $('input[name=bbs_idx]').val(),
                'section_idx' : $('#section_idx').val(),
                'state' : $('input[name=state]:checked').val()
            };

            $.ajax({
                url: '/manage/main/contents/regist/proc',
                method : 'POST',
                data: oFormData,
                dataType : 'json',
                success :function(oRes){
                    if (oRes.success === true) {
                        alert(oRes.msg);
                        location.reload();
                    } else {
                        alert(oRes.msg);
                        console.log(oRes.data);
                        console.log(oRes.code);
                    }
                }
            });
        }
    }
    $(function() {
        let $oDialog = $('#section_dialog');
        let $oBackground = $('.dialog-background');
        let $oTableSorted = $( '.table-sorted tbody');
        $('#collapseExample3').addClass('show');

        $oTableSorted.sortable({
            handle: '.handle',
            placeholder: 'ui-state-highlight'
        });

        $('#section_sort_btn').on('click', function() {
            let oParam = {sort: [], section_idx : $('#section_idx').val()};
            let $oCurrentEl, oSection = {};

            $( '.table-sorted tbody tr').each(function(key) {
                $oCurrentEl = $(this);
                oSection_idx = $oCurrentEl.find('.edit_btn').data('json') || {};
                    if ($oCurrentEl.find('.edit_btn').length < 1) {
                    return true;
                }

                oParam.sort.push({
                    idx: oSection_idx,
                    sort: key + 1
                });
            });

            if (oParam.sort.length < 1) {
                return false;
            }

            $.ajax({
                type: 'post',
                url: '/manage/main/contents/SortModify',
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