<h3><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:29 
  * IP Address: 127.0.0.1
  */ echo _l('User Security');?></h3>

    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
        <tbody>
            <?php
            if(module_user::can_i('edit','Users Permissions','Config')){
            ?>
            <tr>
                <th class="width2">
                    <?php echo _l('Role / Permissions'); ?>
                </th>
                <td>
                    <?php
                    // find all available roles, user can be part of 1 role.
                    // if the role changes, permissions are re-set to default on the right.
                    // can override permissions on a per user basis.

                    $user_roles = isset($user['roles']) && is_array($user['roles']) ? $user['roles'] : array();
                    $roles = module_security::get_roles();
                    $roles_attributes = array();
                    foreach($roles as $role){
                        $roles_attributes[$role['security_role_id']] =$role['name'];
                    }
                    $current_role = current($user_roles);
                    echo print_select_box($roles_attributes,'role_id',isset($current_role['security_role_id']) ? $current_role['security_role_id'] : false);

                    echo ' ';
                    //echo '<a href="'.module_security::link_open_role(false).'">edit</a>';
                    ?>
                    <?php if($current_role){ ?>
                            <a href="#" id="" onclick="$('#role_id').val(0); $('#permissions_editor').toggle(); $(this).hide(); return false;">
                                <?php _e('Fine Tune Permissions');?>
                            </a>
                        <?php } ?>
                    <?php _h('You can setup a list of permissions to re-use over and over again under Settings > Roles. This will control what parts of the application this user can access (if any). You can fine tune permissions on a user by user basis after selecting a role.');?>
                </td>
            </tr>
            <?php } ?>
            <!--<tr>
                <th>
                    <?php /*echo _l('User Type'); */?>
                </th>
                <td>
                    <?php /*if($user_id == 1){
                    echo _l('User (cannot change)');
                        }else{ */?>
                    <?php /*echo print_select_box(module_user::get_user_types(),'user_type_id',$user['user_type_id']); */?>
                    <?php /*} */?>
                </td>
            </tr>-->
            <tr>
                <th class="width2">
                    <?php echo _l('Username'); ?>
                </th>
                <td>
                    <?php _e('(same as email address)');?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo _l('Password'); ?>
                </th>
                <td>
                    <input type="text" name="password" id="password" value="<?php echo htmlspecialchars($user['password']); ?>" />
                    <?php _h('Giving this user a password and login permissions will let them gain access to this system. Depending on the permissions you give them will decide what parts of the system they can access.'); ?>
                </td>
            </tr>
            <!--<tr>
                <th>
                    <?php /*echo _l('Status'); */?>
                </th>
                <td>
                    <?php /*echo print_select_box(module_user::get_statuses(),'status_id',(isset($user['status_id'])?$user['status_id']:'')); */?>
                </td>
            </tr>-->
            <?php if((int)$user_id>0 && $user_id!="new"){ ?>
                <tr>
                    <th><?php echo _l('Auto Login Link'); ?></th>
                    <td>
                        <a href="<?php echo module_security::generate_auto_login_link($user_id); ?>"><?php _e('right click - copy link');?></a>
                        <?php _h('If you give this link to a user (or bookmark it for yourself) then it will log in automatically. To re-set an auto-login link simply change your password to something new.');?>
                    </td>
                </tr>
                <?php
                if(!module_security::can_user_login($user_id)){ ?>
                    <tr>
                        <td colspan="2" align="center">
                            <span class="error_text"><?php _e('(note: this user does not have login permissions yet - login will not work)');?></span>
                        </td>
                    </tr>
                <?php } ?>
                <!--<tr>
                    <th><?php /*echo _l('Login History'); */?></th>
                    <td>
                        <a href="#">Click here to view login history</a> (TODO)
                    </td>
                </tr>-->
            <?php }  ?>
        </tbody>
    </table>

    <?php
            if(module_user::can_i('edit','Fine Tune Permissions','Config')){

                $user_permissions = module_security::get_user_permissions($user_id);
                if($user_id && !$current_role){
                    // do they have any permissions at all?
                    if(!$user_permissions){
                        $current_role = true; // hack to hide permissions area on new blank users (ie: contacts).
                    }
                }
                ?>
                <div id="permissions_editor" style="<?php echo (!(int)$user_id || $current_role) ? 'display:none;' : '';?>">

                    <h3>
						<?php echo _l('Fine Tune Permissions');?>
                    </h3>

                    <?php if($user_id==1){

                        echo _l('User ID #1 has all permissions. This stops you accidently locking yourself out of the application. Please create a new user to assign permissions to.');
                    }else{ ?>

                    <p><?php _e('We recommend you use roles for permissions. It will make things easier! But if you want to fine tune permissions on a per user basis, you can do so below. This will not affect the role, it will only apply to this user.');?></p>

					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
						<thead>
						<tr>
							<th><?php _e('Category');?></th>
							<th><?php _e('Sub Category');?></th>
							<!-- <th>Access</th> -->
							<?php  foreach(module_security::$available_permissions as $permission){ ?>
							<th><?php echo _l(ucwords(str_replace('_',' ',$permission)));?></th>
							<?php } ?>
						</tr>
						</thead>
						<tbody><?php
                            $available_permissions = module_security::get_permissions();
                            $x=0;
                            $last_category=false;
                            $drop_down_permissions = array();
                            $checkbox_permissions = array();
                            foreach($available_permissions as $available_permission){
                                // start hack for special case drop down options:
                                if($available_permission['description']=='drop_down'){
                                    $drop_down_permissions[] = $available_permission;
                                    continue;
                                }
                                // start hack for special case drop down options:
                                if($available_permission['description']=='checkbox'){
                                    $checkbox_permissions[] = $available_permission;
                                    continue;
                                }
                                $available_perms = @unserialize($available_permission['available_perms']);
                                if(!$last_category || $last_category != $available_permission['category']){
                                    $x++;
                                }
                                ?>
                            <tr class="<?php echo $x%2?'odd':'even';?>">
                                <?php if(!$last_category || $last_category != $available_permission['category']){ ?>
                                <td>
                                    <?php echo $available_permission['category']; ?>
                                    &nbsp;
                                </td>
                                <?php }else{ ?>
                                <td align="right">
                                    &nbsp;
                                </td>
                                <?php } ?>
                                <td>
                                    <?php /*if(!$last_category || $last_category != $available_permission['category']){ ?>

                        <?php }else{ ?>
                        <?php } */ ?>
                                    &raquo;
                                    <?php echo $available_permission['name']; ?>
                                </td>
                                <?php foreach(module_security::$available_permissions as $permission){ ?>
                                <td align="center">
                                    <?php if(isset($available_perms[$permission])){ ?>
                                    <input type="checkbox" name="permission[<?php echo $available_permission['security_permission_id'];?>][<?php echo $permission;?>]" value="1"<?php
                                        /*if(isset($security_role['permissions']) && isset($security_role['permissions'][$available_permission['security_permission_id']]) && $security_role['permissions'][$available_permission['security_permission_id']][$permission]){
                                            echo ' checked';
                                        }*/
                                        if(isset($user_permissions[$available_permission['security_permission_id']]) && $user_permissions[$available_permission['security_permission_id']][$permission]){
                                            echo ' checked';
                                        }
                                        ?>>
                                    <?php } ?>
                                </td>
                                <?php } ?>
                            </tr>
                                <?php
                                $last_category = $available_permission['category'];
                            } ?>
						</tbody>
					</table>


                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <th>User Permission</th>
                            <th>Option</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $drop_down_permissions_grouped = array();
                            foreach($drop_down_permissions as $available_permission){
                                if(!isset($drop_down_permissions_grouped[$available_permission['category']])){
                                    $drop_down_permissions_grouped[$available_permission['category']] = array();
                                }
                                $drop_down_permissions_grouped[$available_permission['category']] [] = $available_permission;
                            }
                            $permission = 'view';
                            foreach($drop_down_permissions_grouped as $category_name => $available_permissions){
                                ?>
                            <tr class="<?php echo $x++%2?'odd':'even';?>">
                                <td>
                                    <?php echo $category_name; ?>
                                    &nbsp;
                                </td>
                                <td>
                                    <select name="permission_drop_down[<?php foreach($available_permissions as $available_permission){ echo $available_permission['security_permission_id'].'|'; } ?>]">
                                        <option value=""><?php _e('N/A');?></option>
                                        <?php foreach($available_permissions as $available_permission){ ?>
                                        <option value="<?php echo $available_permission['security_permission_id'];?>"<?php

                                            if(isset($user_permissions[$available_permission['security_permission_id']]) && $user_permissions[$available_permission['security_permission_id']][$permission]){
                                                echo ' selected';
                                            }
                                            ?>><?php echo $available_permission['name'];?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                                <?php
                            } ?>
                            <?php
                            $permission = 'view';
                            foreach($checkbox_permissions as $available_permission){
                                $available_perms = @unserialize($available_permission['available_perms']);
                                ?>
                            <tr class="<?php echo $x++%2?'odd':'even';?>">
                                <td>
                                    <?php echo $available_permission['name']; ?>
                                </td>
                                <td>
                                    <?php if(isset($available_perms[$permission])){ ?>
                                    <input type="checkbox" name="permission[<?php echo $available_permission['security_permission_id'];?>][<?php echo $permission;?>]" value="1"<?php
                                        if(isset($user_permissions[$available_permission['security_permission_id']]) && $user_permissions[$available_permission['security_permission_id']][$permission]){
                                            echo ' checked';
                                        }
                                        ?>>
                                    <?php } ?>
                                </td>
                            </tr>
                                <?php
                            } ?>
                        </tbody>
                    </table>


					<?php } ?>

                    </div>

					<?php } ?>