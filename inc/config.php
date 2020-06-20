<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
ini_set('magic_quotes_gpc', 0);
ini_set('display_errors', 1);
error_reporting(E_ALL);
$microtime = microtime(true); 

$title = "Shoutcast Multi Server Statistics";
$version = 0.2;
$lisense = "CC-BY-NC-SA 4.0";
$refresh = 300/10;
$timeout = 1;
$theme = true;
$demo = false;
$limit = 250;

$panel_class = 'default';
$display_footer = true;
$display_ribbon = true;
$github_url = "https://github.com/djphil/shoutstats";
?>
