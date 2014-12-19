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


$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
$search['customer_id'] = 0;
$users = module_user::get_users($search);
$pagination = process_pagination($users);

// grab a list of customer sites
$sites = array();
$user_statuses = module_user::get_statuses();
$roles = module_security::get_roles();
?>

<h2>
	<span class="button">
		<?php echo create_link("Add new user","add",$module->link_open('new')); ?>
	</span>
	<?php echo _l('User Administration'); ?>
</h2>

<form action="" method="post">


<table class="search_bar" width="100%">
	<tr>
		<th width="90"><?php _e('Filter By:'); ?></th>
		<td width="110">
			<?php _e('Users Name:');?>
		</td>
		<td>
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="10">
		</td>
		<td align="center">
			<?php echo create_link("Reset","reset",$module->link()); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
		<tr>
			<td align="right" colspan="4">
				<?php echo $pagination['summary'];?>
			</td>
		</tr>
	</thead>
	<thead>
        <tr class="title">
            <th><?php echo _l('Users Name'); ?></th>
            <th><?php echo _l('Email Address'); ?></th>
            <th><?php echo _l('Role / Permissions'); ?></th>
            <th><?php echo _l('Can Login'); ?></th>
        </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $user){
            $user = module_user::get_user($user['user_id']); ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <?php echo module_user::link_open($user['user_id'],true);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($user['email']); ?>
                </td>
                <td>
                    <?php
                    if($user['user_id']==1){
                        echo _l('Everything');
                    }else{
                        if(isset($user['roles']) && $user['roles']){
                            foreach($user['roles'] as $role){
                                echo $roles[$role['security_role_id']]['name'];
                            }
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php echo module_security::can_user_login($user['user_id']) ? _l('Yes') : _l('No'); ?>
                </td>
            </tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4" align="center">
				<?php echo $pagination['links'];?>
			</td>
		</tr>
	</tfoot>
</table>
</form>