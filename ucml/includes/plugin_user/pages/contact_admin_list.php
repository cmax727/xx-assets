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

$module->page_title = _l('Customer Contacts');

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();

$use_master_key = module_user::get_contact_master_key();
if(!$use_master_key){
	throw new Exception('Sorry no Customer or Supplier selected');
}else if(isset($_REQUEST[$use_master_key])){
	$search[$use_master_key] = $_REQUEST[$use_master_key];
}
if(!isset($search[$use_master_key]) || !$search[$use_master_key]){
    // we are just showing a list of all customer contacts.
    $show_customer_details = true;
    // check they have permissions to view all customer contacts.
    if(class_exists('module_security',false)){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Customer',
            'page_name' => 'All Customer Contacts',
            'module' => 'customer',
            'feature' => 'view',
		));
    }
	//throw new Exception('Please create a user correctly');
}else{
    $show_customer_details = false;
}
$users = module_user::get_contacts($search);

if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'user_id',
                'owner_table' => 'user',
                'name' => 'name',
                'email' => 'email'
            ),
        )
    );
}

?>

<h2>
    <?php if(isset($search[$use_master_key]) && $search[$use_master_key]){ ?>
	<span class="button">
		<?php echo create_link("Add New Contact","add",module_user::link_generate('new',array('type'=>'contact'))); ?>
	</span>
    <?php } ?>
	<?php echo _l( ($show_customer_details ? 'All ' : '') . 'Customer Contacts'); ?>
</h2>

<form action="" method="GET">
    <?php if($use_master_key && isset($search[$use_master_key])){ ?>
    <input type="hidden" name="<?php echo $use_master_key;?>" value="<?php echo $search[$use_master_key];?>">
    <?php } ?>


<table class="search_bar" width="100%">
	<tr>
		<th width="70"><?php _e('Filter By:'); ?></th>
		<td width="225">
			<?php _e('Contact Name, Email or Phone Number:');?>
		</td>
		<td>
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
		<td align="right">
			<?php echo create_link("Reset","reset",module_user::link_open_contact(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($users);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Name'); ?></th>
        <th><?php echo _l('Phone Number'); ?></th>
        <th><?php echo _l('Email Address'); ?></th>
        <?php if($show_customer_details){ ?>
        <th><?php echo _l('Customer'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_group',false)){ ?>
        <th><?php echo _l('Group'); ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $user){ ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_user::link_open_contact($user['user_id'],true,$user);?>
				<?php
                if($user['is_primary'] == $user['user_id']){
                    echo ' *';
                }
				?>
			</td>
			<td>
				<?php echo $user['phone']; ?><?php echo ($user['phone']&&$user['mobile'])?",":""; ?> <?php echo $user['mobile']; ?>
			</td>
			<td>
				<a href="mailto:<?php echo $user['email']; ?>"><?php echo $user['email']; ?></a>
			</td>
            <?php if($show_customer_details){ ?>
                <td>
                    <?php echo module_customer::link_open($user['customer_id'],true,$user); ?>
                </td>
            <?php } ?>
            <?php if(class_exists('module_group',false)){ ?>
            <td><?php
                    // find the groups for this customer.
                    $groups = module_group::get_groups_search(array(
                                                                  'owner_table' => 'user',
                                                                  'owner_id' => $user['user_id'],
                                                              ));
                    $g=array();
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                ?></td>
            <?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>