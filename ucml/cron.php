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

chdir(dirname(__FILE__));

require_once('includes/config.php');

// if we are runing this from a web browser then we have to have a correct hash.
if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']){
    // check hash.
    $correct_hash = md5(_UCM_FOLDER.' secret hash ');
    if($_REQUEST['hash'] != $correct_hash){
        echo 'failed - please check cron.php link in settings';
        exit;
    }
}

$_SERVER['REMOTE_ADDR'] = false;
$_SERVER['HTTP_HOST'] = false;
$_SERVER['REQUEST_URI'] = false;

$noredirect = true;
require_once("init.php");
$cron_debug = module_config::c('debug_cron_jobs',0);

foreach($plugins as $plugin_name => &$plugin){
    if(method_exists($plugin,'run_cron')){
        if($cron_debug){
            echo "Running $plugin_name cron job \n";
        }
        $plugin -> run_cron();
    }
}

module_config::save_config('cron_last_run',time());