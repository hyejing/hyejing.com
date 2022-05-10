<!-- Outer Row -->
<div class="col-12">
    <div class="d-flex justify-content-center">
        <div class="card o-hidden border-0 shadow-lg my-5 col-md-7">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">{C.CNF_TITLE} Signup</h1>
                        {? HTML.ERROR.message}
                        <div class="alert alert-danger" role="alert">{HTML.ERROR.message}</div>
                        {/}
                    </div>

                    {=form_open('signup/exec')}
                    <div class="form-group">
                        <input name="id" value="" id="id" type="text" maxlength="16" class="form-control" placeholder="ID">
                    </div>
                    <div class="form-group">
                        <input name="password" value="" id="password" type="password" maxlength="20" class="form-control" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <input name="password_re" value="" id="password_re" type="password" maxlength="20" class="form-control" placeholder="Password Confirm">
                    </div>
                    <div class="form-group">
                        <input name="name" value="" id="name" type="text" maxlength="20" class="form-control" placeholder="Name">
                    </div>
                    <div class="justify-content-md-center mt-4 mb-0">
                        <div class="row">
                            <div class="col d-grid">
                                <a href="{C.URL_DOMAIN}/login" class="btn btn-primary btn-user btn-block">Login</a>
                            </div>
                            <div class="col d-grid">
                                <button type="submit" class="btn btn-success btn-user btn-block">Join</button>
                            </div>
                        </div>
                    </div>
                    {=form_close()}
                </div>
            </div>
        </div>
    </div>
</div>

