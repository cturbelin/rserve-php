<?php
require(__DIR__."/../vendor/autoload.php");

$cfg_file = __DIR__."/config.php";
if(file_exists($cfg_file)) {
    require $cfg_file;
}
