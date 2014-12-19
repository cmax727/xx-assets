<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:03 
  * IP Address: 127.0.0.1
  */ 


$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$customers = module_customer::get_customers($search);
// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'customer_id',
                'owner_table' => 'customer',
                'name' => 'customer_name',
                'email' => 'primary_user_email'
            ),
        )
    );
}
// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_customer::can_i('view','Export Customers')){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this customers?
        array(
            'name' => 'Customer Export',
            'fields'=>array(
                'Customer ID' => 'customer_id',
                'Customer Name' => 'customer_name',
                'Credit' => 'credit',
                'Address Line 1' => 'line_1',
                'Address Line 2' => 'line_2',
                'Address Suburb' => 'suburb',
                'Address Country' => 'country',
                'Address State' => 'state',
                'Address Region' => 'region',
                'Address Post Code' => 'post_code',
                'Primary Contact' => 'primary_user_name',
                'Primary Phone' => 'primary_user_phone',
                'Primary Email' => 'primary_user_email'
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'customer',
                'owner_id' => 'customer_id',
            ),
        )
    );
}
$pagination = process_pagination($customers);

?>

<h2>
    <?php if(module_customer::can_i('create','Customers')){ ?>
	<span class="button">
		<?php echo create_link("Create New Customer","add",module_customer::link_open('new')); ?>
	</span>
    <?php
    }
    if(class_exists('module_import_export',false) && module_customer::can_i('view','Import Customers')){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_customer::handle_import',
                'name'=>'Customers',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>'customer',
                'fields'=>array(
                    'Customer ID' => 'customer_id',
                    'Customer Name' => 'customer_name',
                    'Credit' => 'credit',
                    'Address Line 1' => 'line_1',
                    'Address Line 2' => 'line_2',
                    'Address Suburb' => 'suburb',
                    'Address Country' => 'country',
                    'Address State' => 'state',
                    'Address Region' => 'region',
                    'Address Post Code' => 'post_code',
                    'Primary Contact' => 'primary_user_name',
                    'Primary Phone' => 'primary_user_phone',
                    'Primary Email' => 'primary_user_email'
                ),
                // do we try to import extra fields?
                'extra' => array(
                    'owner_table' => 'customer',
                    'owner_id' => 'customer_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Customers","add",$link); ?>
        </span>
        <?php
    }
    if(module_user::can_i('view','All Customer Contacts','Customer','customer')){
    ?>
	<span class="button">
		<?php echo create_link("View All Contacts","link",module_user::link_open_contact(false)); ?>
	</span>
    <?php } ?>
	<span class="title">
		<?php echo _l('Customers'); ?>
	</span>
</h2>


<form action="" method="post">

<table class="search_bar" width="100%">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td width="140px">
			<?php _e('Names, Phone or Email:');?>
		</td>
		<td>
			<input type="text" style="width: 240px;" name="search[generic]" class="" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>">
		</td>
		<td width="100px">
            <?php _e('Address:');?>
			:
		</td>
		<td>
			<input type="text" style="width: 180px;" name="search[address]" class="" value="<?php echo isset($search['address'])?htmlspecialchars($search['address']):''; ?>">
		</td>
		<td align="right" rowspan="2">
			<?php echo create_link("Reset","reset",module_customer::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Customer Name'); ?></th>
		<th><?php echo _l('Primary Contact'); ?></th>
		<th><?php echo _l('Phone Number'); ?></th>
		<th><?php echo _l('Email Address'); ?></th>
		<th><?php echo _l('Address'); ?></th>
        <?php if(class_exists('module_group',false)){ ?>
        <th><?php echo _l('Group'); ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($pagination['rows'] as $customer){ ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="row_action">
	            <?php echo module_customer::link_open($customer['customer_id'],true); ?>
            </td>
            <td>
				<?php
				if($customer['primary_user_id']){
					module_user::print_contact_summary($customer['primary_user_id'],'html',array('name'));
				}else{
					echo '';
				}
				?>
            </td>
            <td>
				<?php
				if($customer['primary_user_id']){
					module_user::print_contact_summary($customer['primary_user_id'],'html',array('phone'));
				}else{
					echo '';
				}
				?>
            </td>
            <td>
				<?php
				if($customer['primary_user_id']){
					module_user::print_contact_summary($customer['primary_user_id'],'html',array('email'));
				}else{
					echo '';
				}
				?>
            </td>
            <td>
                <?php echo module_address::print_address($customer['customer_id'],'customer','physical'); ?>
            </td>
            <?php if(class_exists('module_group',false)){ ?>
            <td><?php
                    // find the groups for this customer.
                    $groups = module_group::get_groups_search(array(
                                                                  'owner_table' => 'customer',
                                                                  'owner_id' => $customer['customer_id'],
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