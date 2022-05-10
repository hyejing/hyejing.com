<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-danger shadow-lg rounded-lg mt-5">
                <div class="card-header">
                    <h4 class="text-center font-weight-light mt-3">알림</h4>
                </div>
                {? ERROR.FILE}
                <div class="card-body text-left">
                    <strong class="text-dark">FILE : {ERROR.FILE}</strong>
                </div>
                {/}
                {? ERROR.LINE}
                <div class="card-body text-left">
                    <strong class="text-dark">LINE : {ERROR.LINE}</strong>
                </div>
                {/}
                <div class="card-body text-left">
                    <strong class="text-danger">{ERROR.MESSAGE}</strong>
                </div>
                <div class="card-footer text-center">
                    <a href="/">메인페이지로 이동</a>
                    /
                    <a href="javascript:history.back(-1);">이전 페이지로 이동</a>
                </div>
            </div>
        </div>
    </div>
</div>
