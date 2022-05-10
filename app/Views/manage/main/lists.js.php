<script>
var sectionMain = {
    modify: function(idx) {
        let myModal = new bootstrap.Modal($('#layer_dialog'), {
            keyboard: false
        });

        $('#section_dialog').load('{C.URL_DOMAIN}/manage/main/modify?idx=' + idx);
        myModal.show();
    },
    regist_content: function(idx) {
        let myModal = new bootstrap.Modal($('#layer_dialog'), {
            keyboard: false
        });

        $('#section_dialog').load('{C.URL_DOMAIN}/manage/main/contents/modify?idx=' + idx);
        myModal.show();
    }
}

$(function () {
    let $oDialog = $('#section_dialog');
    let $oBackground = $('.dialog-background');
    let $oTableSorted = $( '.table-sorted tbody');

    $oTableSorted.sortable({
        handle: '.handle',
        placeholder: 'ui-state-highlight'
    });

    /**
     * 변경된 순서를 서버에 전송
     */
    $('#section_sort_btn').on('click', function() {
        let oParam = {sort: []};
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
            url: '{C.URL_DOMAIN}/manage/main/SortModify',
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

    //섹션 등록 모달 open
    $('#add_btn').on('click', function(e) {
        $('.modal-backdrop').remove();
        let myModal = new bootstrap.Modal($('#layer_dialog'), {
            keyboard: false
        });

        $('#section_dialog').load('{C.URL_DOMAIN}/manage/main/regist');
        myModal.show();
    });
});

//regist main section function
function regist_main_section() {
    let oFormData = {
        'name' : $('input[name="name"]').val(),
        'type' : $('input[name="type"]:checked').val(),
        'state' : $('input[name="state"]:checked').val()
    };

    $.ajax({
        url: '{C.URL_DOMAIN}/manage/main/regist/proc',
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

//modify main section function
function modify_main_section(idx) {
    let oFormData = {
        'idx' : idx,
        'type' : $('input[name="type"]:checked').val(),
        'name' : $('input[name="name"]').val(),
        'state' : $('input[name="state"]:checked').val(),
    };

    $.ajax({
        url: '{C.URL_DOMAIN}/manage/main/modify/proc',
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
</script>