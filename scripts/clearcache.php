<?php

require __DIR__ . "/_bootstrap.php";

use qpost\Cache\CacheHandler;

println("Clearing the cache...");
CacheHandler::clearCache();
println("Done.");
