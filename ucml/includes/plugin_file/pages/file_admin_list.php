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

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
if(isset($_REQUEST['job_id']) && (int)$_REQUEST['job_id']>0){
    $search['job_id'] = (int)$_REQUEST['job_id'];
    $job = module_job::get_job($search['job_id'],false);
}
$files = module_file::get_files($search);

if(isset($job) && $job){
    // little hack
    $search['job'] = $job['name'];
}

?>

<h2>
	<span class="button">
		<?php echo create_link("Add New file","add",module_file::link_open('new')); ?>
	</span>
	<?php echo _l('Customer files'); ?>
</h2>

<form action="" method="post">


<table class="search_bar" width="100%">
	<tr>
		<th width="70"><?php _e('Filter By:'); ?></th>
		<td width="150">
			<?php echo _l('File Name / Description:'); ?>
		</td>
		<td>
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
		<td width="70">
			<?php echo _l('Job:'); ?>
		</td>
		<td>
			<input type="text" name="search[job]" value="<?php echo isset($search['job'])?htmlspecialchars($search['job']):''; ?>" size="30">
		</td>
		<td align="right">
			<?php echo create_link("Reset","reset",module_file::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($files);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('File Name'); ?></th>
		<th><?php echo _l('Description'); ?></th>
        <th><?php echo _l('File Size'); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
        <th><?php echo _l('Customer'); ?></th>
        <?php } ?>
        <th><?php echo _l('Job'); ?></th>
        <th><?php echo _l('Date Added'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $file){ ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_file::link_open($file['file_id'],true);?>
			</td>
            <td>
                <?php echo nl2br(htmlspecialchars($file['description']));?>
            </td>
            <td>
                <?php
                if(file_exists($file['file_path'])){
                    echo module_file::format_bytes(filesize($file['file_path']));
                }
                ?>
            </td>
            <?php if(!isset($_REQUEST['customer_id'])){ ?>
            <td>
                <?php echo module_customer::link_open($file['customer_id'],true);?>
            </td>
            <?php } ?>
            <td>
                <?php echo module_job::link_open($file['job_id'],true);?>
            </td>
            <td>
                <?php echo _l('%s by %s',print_date($file['date_created']),module_user::link_open($file['create_user_id'],true));?>
            </td>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>