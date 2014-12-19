<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:28 
  * IP Address: 127.0.0.1
  */ 

if(isset($_REQUEST['file_id'])){

    include("file_admin_edit.php");

}else{ 
	
	include("file_admin_list.php");
	
} 

