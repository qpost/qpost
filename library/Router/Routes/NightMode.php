<?php

$app->bind("/nightmode",function(){
    Util::toggleNightMode();
    return $this->reroute("/");
});