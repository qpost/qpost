<?php

function shutdown(){
	Database::Instance()->shutdown();
}

register_shutdown_function("shutdown");