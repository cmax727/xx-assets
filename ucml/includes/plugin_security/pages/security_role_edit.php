<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:12 
  * IP Address: 127.0.0.1
  */


$security_role_id = $_REQUEST['security_role_id'];
if($security_role_id && $security_role_id != 'new'){
	if(class_exists('module_security',false)){
        module_security::check_page(array(
            'category' => 'Security',
            'page_name' => 'Security Roles',
            'module' => 'security',
            'feature' => 'edit',
		));
	}
	$security_role = module_security::get_security_role($security_role_id);
	if(!$security_role){
		$security_role_id = 'new';
	}
}

if($security_role_id == 'new' || !$security_role_id){
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'category' => 'Security',
            'page_name' => 'Security Roles',
            'module' => 'security',
            'feature' => 'create',
		));
	}
	$security_role = array(
		'security_role_id' => 'new',
        'name'=>'',
	);
}

?>


	
<form action="" method="post">
	<input type="hidden" name="_process" value="save_security_role" />
	<input type="hidden" name="security_role_id" value="<?php echo $security_role_id; ?>" />

	<h3><?php echo _l('Role Details'); ?></h3>

	<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
		<tbody>
			<tr>
				<th class="width1">
					<?php echo _l('Name'); ?>
				</th>
				<td>
					<input type="text" name="name" id="name" style="width: 200px;" value="<?php echo htmlspecialchars($security_role['name']); ?>" />
				</td>
			</tr>

		</tbody>
	</table>

	<h3><?php echo _l('Permissions'); ?></h3>

<table width="100%" cellpadding="5">
    <tbody>
    <tr>
        <td valign="top" width="50%">

	<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
		<thead>
		<tr>
			<th>Category</th>
			<th>Permissions</th>
			<?php foreach(module_security::$available_permissions as $permission){ ?>
			<th><?php echo ucwords($permission);?></th>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
			<?php
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
						if(isset($security_role['permissions']) && isset($security_role['permissions'][$available_permission['security_permission_id']]) && $security_role['permissions'][$available_permission['security_permission_id']][$permission]){
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

</td>
<td width="50%" valign="top">

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
                                    if(isset($security_role['permissions']) && isset($security_role['permissions'][$available_permission['security_permission_id']]) && $security_role['permissions'][$available_permission['security_permission_id']][$permission]){
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
						if(isset($security_role['permissions']) && isset($security_role['permissions'][$available_permission['security_permission_id']]) && $security_role['permissions'][$available_permission['security_permission_id']][$permission]){
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

    </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            
            <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save Role'); ?>" class="submit_button save_button" />
            <?php if((int)$security_role_id){ ?>
            <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" onclick="return confirm('<?php echo _l('Really delete this record?'); ?>');" class="submit_button delete_button" />
            <?php } ?>
            <input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo $module->link_open_role(false); ?>';" class="submit_button" />

        </td>
    </tr>
    </tbody>
</table>



</form>

