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

$use_master_key = module_user::get_contact_master_key();
if(!$use_master_key){
	//throw new Exception('Sorry no Customer or Supplier selected');
}


$user_id = (int)$_REQUEST['user_id'];
$user = module_user::get_user($user_id);
if($user_id>0 && $user){
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            //'module' => $module->module_name,
            'feature' => 'edit',
            // extra:
            'module' => 'user',
            'category' => 'Customer',
            'page_name' => 'Contacts',
		));
	}
    // check if this user is the contact primary user.
    if($user['customer_id']){
        $site_data = module_customer::get_customer($user['customer_id']);
        if($site_data['primary_user_id'] == $user_id){
            $user['customer_primary'] = true;
        }
    }
}else{
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            //'module' => $module->module_name,
            'feature' => 'create',
            // extra:
            'module' => 'user',
            'category' => 'Customer',
            'page_name' => 'Contacts',
		));
	}
	module_security::sanatise_data('user',$user);
}


?>


	
<form action="" method="post">
	<input type="hidden" name="_process" value="save_contact" />
	<input type="hidden" name="<?php echo $use_master_key;?>" value="<?php echo $user[$use_master_key]; ?>" />

    <?php

    $fields = array(
    'fields' => array(
        'name' => 'Name',
        'email' => 'Email',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="50%">
					<h3><?php echo _l('Contact Details'); ?></h3>

                    <?php include('contact_admin_form.php'); ?>

				</td>
                <td valign="top">

					<?php include('user_admin_edit_login.php'); ?>
                </td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save Contact'); ?>" class="submit_button save_button" />
					<?php if((int)$user_id){ ?>
					<input type="submit" name="butt_del_contact" id="butt_del_contact" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_user::link_open_contact(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
