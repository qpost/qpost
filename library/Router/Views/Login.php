<div class="row">
    <div class="col-lg-6 offset-lg-3">
        <?php

            if(isset($errorMsg) && !is_null($errorMsg))
                echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER);

        ?>
        <div class="card">
            <div class="card-body">
                <h3 class="text-center mb-2">Log in</h3>

                <form action="/login" method="post">
                    <?= Util::insertCSRFToken() ?>
                    <input type="text" class="form-control mb-2" name="email" placeholder="Email address or username"<?= isset($_POST["email"]) ? ' value="' . Util::sanatizeHTMLAttribute($_POST["email"]) . '"' : "" ?>/>
                    <input type="password" class="form-control mb-2" name="password" placeholder="Password"/>
                    <input type="submit" class="btn btn-block btn-primary" value="Log in"/>

                    <hr/>
                    
                    <a href="/" class="btn btn-block btn-light mb-2" data-no-instant>Create an account</a>
                    <a href="/login/gigadrive" class="btn btn-block btn-success" data-no-instant>Sign in with Gigadrive</a>
                </form>
            </div>
        </div>
    </div>
</div>