<?php

function shutdown(){
	session_write_close();
	Database::Instance()->shutdown();
}

register_shutdown_function("shutdown");