<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:36 
  * IP Address: 127.0.0.1
  */
define("_REWRITE_LINKS",false);

$noredirect = true;
include('init.php');


if($load_modules){
    $m = current($load_modules);
}else{
    $m = false;
}
//$m = (isset($_REQUEST['m'])) ? trim(basename($_REQUEST['m'])) : false;
$h = (isset($_REQUEST['h'])) ? trim(basename($_REQUEST['h'])) : false;

if($m && isset($plugins[$m])){
    if(method_exists($plugins[$m],'external_hook')){
        $plugins[$m] -> external_hook($h);
    }
}