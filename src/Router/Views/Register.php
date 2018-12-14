<div class="row">
    <div class="col-lg-6 offset-lg-3">
        <?php

            if(isset($successMsg) && !is_null($successMsg)){
                echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS);
            } else {
                if(isset($errorMsg) && !is_null($errorMsg))
                    echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER);

        ?>
        <div class="card">
            <div class="card-body">
                <h3 class="text-center mb-2">Register</h3>

                <form action="/register?code=<?= $_GET["code"] ?>" method="post">
                    <?= Util::insertCSRFToken() ?>

                    <input type="text" class="form-control mb-2" name="email" placeholder="Email address"<?= isset($email) ? ' value="' . Util::sanatizeHTMLAttribute($email) . '"' : "" ?>/>

                    <input type="text" class="form-control mb-2" name="username" placeholder="Username"<?= isset($username) ? ' value="' . Util::sanatizeHTMLAttribute($username) . '"' : "" ?>/>

                    <input type="submit" class="btn btn-block btn-primary" value="Create account"/>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
</div>