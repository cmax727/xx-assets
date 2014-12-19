<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:29 
  * IP Address: 127.0.0.1
  */ 


if(isset($_REQUEST['user_id'])){

    if(class_exists('module_security',false)){
        if((int)$_REQUEST['user_id'] > 0){
            module_security::check_page(array(
                 'category' => 'Customer',
                 'page_name' => 'Contacts',
                'module' => 'user',
                'feature' => 'edit',
            ));
        }else{
            module_security::check_page(array(
                 'category' => 'Customer',
                 'page_name' => 'Contacts',
                'module' => 'user',
                'feature' => 'create',
            ));
        }
    }

    $is_contact = true;
    $user_safe = true;
    include("user_admin_edit.php");

}else{ 
	
	include("contact_admin_list.php");
	
} 

