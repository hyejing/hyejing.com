<script type="text/javascript">
    var regist = {
        regist: function () {
            var cash        = $('#cash').val();
            var detailText  = $('#detail_text').val();
            var title       = $('#title').val();
            var filePath    = $('#file_path').val();
            var isFree      = $('#free').val();
            var cate        = $('#cate').val();
            var sError      = '';
            if(!cash){
                sError = '가격을 입력해 주세요';
            }
            if(!detailText){
                sError = '상세내용을 입력해 주세요';
            }
            if(!title){
                sError = '제목을 입력해 주세요';
            }
            if(!filePath){
                sError = '업로드파일을 입력해 주세요';
            }
            if(isFree != 'free' && cash < 1){
                sError = '가격을 입력해 주세요';
            }

            if(!cate){
                sError = '카테고리를 선택해 주세요';
            }

            if(sError){
                alert(sError );
                return false;
            }
            var oFormData = {
                'cash': cash,
                'detailText': detailText,
                'title': title,
                'filePath': filePath,
                'cate': cate,
            };

            $.ajax({
                url: '/bbs/regist/proc',
                method: 'POST',
                data: oFormData,
                dataType: 'json',
                success: function (oRes) {
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
        selectFile: function () {
            // 파일선택
            $.ajax({
                url: 'http://localhost:9933/openFileDialog',
                method: 'GET',
                dataType: 'json',
                success: function (oRes) {
                    if (oRes.success === true) {
                        if(oRes.filePath){
                            $('#file_path').val(oRes.filePath);
                        }
                    } else {
                        alert(oRes.message);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('업로드 프로그램 설치가 필요합니다.');
                }
            });
        },
        isFree: function () {
            if($('#free').val() == 'free'){
                $('#cash').val(0);
            }
        }
    }
</script>