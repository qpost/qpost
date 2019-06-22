<?php

use qpost\Database\Database;

function shutdown(){
	session_write_close();
	Database::Instance()->shutdown();
}

register_shutdown_function("shutdown");