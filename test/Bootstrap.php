<?php
$path = __DIR__ . '/../vendor/autoload.php';
if(file_exists($path)) {
	include_once $path;
}
unset($path);