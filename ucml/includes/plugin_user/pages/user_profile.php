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

$user_id = (int)$_SESSION['_user_id'];
	
if($user_id){ 
	$user = $module->get_user($user_id);
	//$user_notes = $module->get_user_notes($user_id);
	?>
	
	<h2><?php echo _l('Your Details'); ?></h2>
	
	
	<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass">
	  <tr>
	  	<td width="13%"><?php echo _l('Full Name'); ?></td>
	    <td width="37%"><?php echo ($user['name']); ?></td>
	     <td width="13%">
	     
	     </td>
	    <td width="37%"> </td>
	  </tr>
	  <tr>
	    <td><?php echo _l('Email Address'); ?></td>
	    <td><?php echo ($user['email']); ?></td>
	    <td></td>
	    <td></td>
	  </tr>
	  
	 
	  <tr>
	    <td><?php echo _l('Phone'); ?></td>
	    <td><?php echo ($user['phone']); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo _l('Fax'); ?></td>
	    <td><?php echo ($user['fax']); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo _l('Mobile'); ?></td>
	    <td><?php echo ($user['mobile']); ?></td>
	  </tr>
	 
	</table>
	
<?php  }  ?>