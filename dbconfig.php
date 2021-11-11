<?php
@session_start();
header('Content-Type: text/html; charset=iso-8859-1');

//Change HOST, DBNAME (database name), USERNAME (database username), PASS (password), PORTNUMBER according to your server configuration
define("HOST","localhost");
define("DBNAME","expinc");
define("USERNAME","encourageindia");
define("PASS","?fbE[%D^Ton^");  
define("PORTNUMBER","3306");
define("TYPE","mysql");
define('ADMINPAGINATIONLIMIT',20);
define('PAGINATIONLIMIT',30);

$path='';
if(isset($_SERVER['HTTPS']))
{
    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else
{
    $protocol = 'http';
}
$path="appapis.iamstmartin.com/expinc/appapis/";

$path = $protocol . "://" . $path;

define("PATH",$path);
date_default_timezone_set('US/Eastern');
require_once "../lib/pdomanager.php";
?>
