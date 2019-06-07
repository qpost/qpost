<?php

require __DIR__ . "/_bootstrap.php";

println("Clearing the cache...");
CacheHandler::clearCache();
println("Done.");
