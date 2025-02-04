<?php
//var_dump(ini_get("log_errors"));
//ar_dump(ini_get("error_log"));
ini_set("log_errors", 1);
ini_set("error_log", "/home/pi/localserver/server/error.log");

error_log("Testing error logging!"); // This should appear in the log file

?>

