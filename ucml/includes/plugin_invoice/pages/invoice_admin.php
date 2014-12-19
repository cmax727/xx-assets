<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:46 
  * IP Address: 127.0.0.1
  */ 

$invoice_safe = true;

if(isset($_REQUEST['invoice_id'])){

    if(isset($_REQUEST['print'])){
        include('invoice_admin_print.php');
    }else if(isset($_REQUEST['email'])){
        include('invoice_admin_email.php');
    }else{
        /*if(module_security::getlevel() > 1){
            include('invoice_customer_view.php');
        }else{*/
            include("invoice_admin_edit.php");
        /*}*/
    }

}else{

	include("invoice_admin_list.php");
	
} 

