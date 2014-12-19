<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:54 
  * IP Address: 127.0.0.1
  */

$job_safe = true; // stop including files directly.

if(isset($_REQUEST['job_id'])){

    if((int)$_REQUEST['job_id'] > 0){
        include("job_admin_edit.php");
    }else{
        include("job_admin_create.php");
    }

}else{ 
	
	include("job_admin_list.php");
	
} 

