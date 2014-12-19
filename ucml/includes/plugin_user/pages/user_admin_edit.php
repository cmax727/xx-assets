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

if(!$user_safe)die('fail');
if(!isset($is_contact))$is_contact=false;

if($is_contact){
    $module->page_title = _l('Contact');
}

$user_id = (int)$_REQUEST['user_id'];
$user = module_user::get_user($user_id);
if(!$user){
    $user_id = 'new';
}

// work out the user type and invluce that particular file
/*$user_type_id = (int)$user['user_type_id'];
if(!$user_type_id){
    if(in_array('config',$load_modules)){
        $user_type_id = 1;

    }else{
        $user_type_id = 2;
    }
}*/
//include('user_admin_edit'.$user_type_id.'.php');
//include('user_admin_edit1.php');

if(isset($user['customer_id']) && $user['customer_id']){
    // we have a contact!
    $use_master_key = 'customer_id'; // for the "primary contact" thingey.
    // are we creating a new user?
    if(!$user_id || $user_id == 'new'){
        $user['roles']=array(
            array('security_role_id'=>module_config::c('contact_default_role',0)),
        );
    }
}else{
    $use_master_key = false; // we have a normal site user..
}

?>



<form action="" method="post">
	<input type="hidden" name="_process" value="save_user" />
	<!-- <input type="hidden" name="_redirect" value="<?php echo $module->link("",array("saved"=>true,"user_id"=>((int)$user_id)?$user_id:'')); ?>" /> -->
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="customer_id" value="<?php echo $user['customer_id']; ?>" />


    <?php

    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );

    module_form::set_required(array(
         'fields' => array(
             'name' => 'Name',
             'email' => 'Email',
             //'password' => 'Password',
             //'status_id' => 'Status',
         ),
      ));

?>
	<table width="100%" cellpadding="10">
		<tbody>
			<tr>
				<td valign="top" width="50%">
					<h3><?php echo _l(($is_contact?'Contact':'User').' Details'); ?></h3>

					<?php include('contact_admin_form.php'); ?>


                    <?php if(module_config::c('users_have_address',0)){ ?>
                        <h3><?php echo _l('Address'); ?></h3>

                        <?php
                        handle_hook("address_block",$module,"physical","user","user_id");
                    }
                    ?>


                    <?php
                    if((int)$user_id > 0){
                        //handle_hook("note_list",$module,"user","user_id",$user_id);
                        module_note::display_notes(array(
                            'title' => ($is_contact?'Contact':'User').' Notes',
                            'owner_table' => 'user',
                            'owner_id' => $user_id,
                            'view_link' => $module->link_open($user_id),
                           //'bypass_security' => true,
                            )
                        );
                        if(class_exists('module_group',false)){
    
                            module_group::display_groups(array(
                                 'title' => ($is_contact?'Contact':'User').' Groups',
                                'owner_table' => 'user',
                                'owner_id' => $user_id,
                                'view_link' => module_user::link_open($user_id),

                             ));
                        }
                    }
                    ?>


				</td>
				<td valign="top" width="50%">

					<?php include('user_admin_edit_login.php'); ?>



                </td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save User'); ?>" class="save_button submit_button" />
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="delete_button submit_button" />
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
