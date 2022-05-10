<script type="text/javascript">
    var video = document.getElementById('video-player');
    var videoSrc = '{DATA.video.file_name}';
    //
    // First check for native browser HLS support
    //
    if(videoSrc) {
        if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = videoSrc;
            //
            // If no native HLS support, check if hls.js is supported
            //
        } else if (Hls.isSupported()) {
            var hls = new Hls();
            hls.loadSource(videoSrc);
            hls.attachMedia(video);
        }
    }else{
        video.remove();
    }

    var detail = {
        modify: function (idx) {
            var oFormData = {
                'idx': idx,
                'detailText': $('#detail_text').val(),
                'title': $('#title').val(),
            };

            $.ajax({
                url: '{C.URL_DOMAIN}/bbs/modify/proc',
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
    }
</script>