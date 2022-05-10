<!-- Outer Row -->
<div class="col-12">
    <div class="d-flex justify-content-center">
        <div class="card o-hidden border-0 shadow-lg my-5 col-md-7">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">{C.CNF_TITLE} Login</h1>
                        {? HTML.ERROR.message}
                        <div class="alert alert-danger" role="alert">{HTML.ERROR.message}</div>
                        {/}
                    </div>

                    {=form_open('login/exec')}
                    <input type="hidden" name="ref" value="{HTML.param.ref}" />
                    <div class="form-group">
                        <input name="id" value="" id="id" type="text" maxlength="16"  class="form-control" aria-describedby="emailHelp" placeholder="ID">
                    </div>
                    <div class="form-group">
                        <input name="pw" value="" id="pw" type="password" maxlength="20"  class="form-control" placeholder="Password">
                    </div>
                    <div class="justify-content-md-center mt-4 mb-0">
                        <div class="row">
                            <div class="col d-grid">
                                <a href="{C.URL_DOMAIN}/signup" class="btn btn-success btn-user btn-block">
                                    Join
                                </a>
                            </div>
                            <div class="col d-grid">
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Login
                                </button>
                            </div>
                        </div>
                    </div>
                    {=form_close()}
                </div>
            </div>
        </div>
    </div>
</div>
