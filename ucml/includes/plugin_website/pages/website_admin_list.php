<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:33 
  * IP Address: 127.0.0.1
  */ 

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
$websites = module_website::get_websites($search);


// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'website_id',
                'owner_table' => 'website',
                'name' => 'name',
                'email' => ''
            ),
        )
    );
}
// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_website::can_i('view','Export '.module_config::c('project_name_plural','Websites'))){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this customers?
        array(
            'name' => module_config::c('project_name_single','Website').' Export',
            'fields'=>array(
                module_config::c('project_name_single','Website').' ID' => 'website_id',
                'Customer Name' => 'customer_name',
                'Name' => 'name',
                'URL' => 'url',
                'Status' => 'status',
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'website',
                'owner_id' => 'website_id',
            ),
        )
    );
}

?>

<h2>
	<span class="button">
		<?php echo create_link("Add New ".module_config::c('project_name_single','Website'),"add",module_website::link_open('new')); ?>
	</span>
    <?php if(class_exists('module_import_export',false) && module_website::can_i('view','Import '.module_config::c('project_name_plural','Websites'))){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_website::handle_import',
                'name'=>module_config::c('project_name_plural','Websites'),
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>'website',
                'fields'=>array(
                    module_config::c('project_name_single','Website').' ID' => 'website_id',
                    'Customer Name' => 'customer_name',
                    'Name' => 'name',
                    'URL' => 'url',
                    'Status' => 'status',
                ),
                // do we attempt to import extra fields?
                'extra' => array(
                    'owner_table' => 'website',
                    'owner_id' => 'website_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import ".module_config::c('project_name_plural','Websites'),"add",$link); ?>
        </span>
        <?php
    } ?>
	<?php echo _l('Customer '.module_config::c('project_name_plural','Websites')); ?>
</h2>

<form action="" method="post">


<table class="search_bar" width="100%">
	<tr>
        <th width="70"><?php _e('Filter By:'); ?></th>
        <td width="40">
            <?php _e('Name/URL:');?>
        </td>
        <td>
            <input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
        </td>
		<td width="30">
        <?php _e('Status:');?>
        </td>
        <td>
        <?php echo print_select_box(module_website::get_statuses(),'search[status]',isset($search['status'])?$search['status']:''); ?>
        </td>
        <td align="right">
			<?php echo create_link("Reset","reset",module_website::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($websites);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Name'); ?></th>
		<th><?php echo _l('URL'); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th><?php echo _l('Customer'); ?></th>
        <?php } ?>
		<th><?php echo _l('Status'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $website){
            ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_website::link_open($website['website_id'],true);?>
			</td>
            <td>
                <a href="http://<?php echo htmlspecialchars($website['url']);?>" target="_blank">http://<?php echo htmlspecialchars($website['url']);?></a>

            </td>
            <?php if(!isset($_REQUEST['customer_id'])){ ?>
            <td>
                <?php echo module_customer::link_open($website['customer_id'],true);?>
            </td>
            <?php } ?>
            <td>
                <?php echo htmlspecialchars($website['status']);?>
            </td>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>