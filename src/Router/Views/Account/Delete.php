<?php

use qpost\Util\Util;

$user = Util::getCurrentUser();

?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card my-3">
            <div class="card-header">
                Deleting your account
            </div>

            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="<?= $user->getAvatarURL() ?>" class="rounded" style="width: 150px"/>
                    
                    <div class="text-muted mt-2">
                        @<?= $user->getUsername() ?>
                    </div>
                </div>

                <p class="mb-0">
                    At qpost we allow you complete control of your data and as such, leave it up to you to decide if you want to keep your data on our servers or not.<br/><br/>
                    Please make sure you are absolutely certain that you want to delete your account as this process is completely <u>irreversible</u>.

                    <br/><br/>

                    This will delete all of your posts, favorites, replies, followers and anything else you have ever created on qpost.

                    <?php if($user->isGigadriveLinked()){ ?>
                    <br/><br/>

                    <b>
                        This process will <u>NOT</u> delete your Gigadrive account. Check the Gigadrive website in order to completely get rid of your data.
                    </b>
                    <?php } ?>
                </p>
            </div>

            <div class="card-footer">
                <form action="<?= $app->routeUrl("/delete") ?>" method="post" class="float-right">
                    <?= Util::insertCSRFToken() ?>

                    <input type="hidden" name="confirmation" value="true"/>

                    <a href="<?= $app->routeUrl("/") ?>" class="btn btn-secondary btn-sm">Cancel</a>

                    <button type="submit" class="btn btn-danger btn-sm">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>