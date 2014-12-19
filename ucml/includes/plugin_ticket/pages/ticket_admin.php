<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:23 
  * IP Address: 127.0.0.1
  */ 

$ticket_safe = true;
$limit_time = strtotime('-'.module_config::c('ticket_turn_around_days',5).' days',time());

if(isset($_REQUEST['ticket_id'])){

    include('ticket_admin_edit.php');

    /*if(module_security::getlevel() > 1){
        ob_end_clean();
        $_REQUEST['i'] = $_REQUEST['ticket_id'];
        $_REQUEST['hash'] = module_ticket::link_public($_REQUEST['ticket_id'],true);
        $module->external_hook('public');
        exit;
        //include('includes/plugin_ticket/public/ticket_customer_view.php');
    }else{*/
	    //include("ticket_admin_edit.php");
    //}

}else{ 
	
	include("ticket_admin_list.php");
	
} 

