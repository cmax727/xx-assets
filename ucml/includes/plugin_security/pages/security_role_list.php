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

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
$roles = $module->get_roles($search);

?>

<h2>
	<span class="button">
		<?php echo create_link("Add New Role","add",module_security::link_open_role('new')); ?>
	</span>
	<?php echo _l('Security Roles'); ?>
</h2>

<form action="" method="post">


<?php
$pagination = process_pagination($roles);
$colspan = 1;
?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
		<tr>
			<td align="right" colspan="<?php echo $colspan;?>">
				<?php echo $pagination['summary'];?>
			</td>
		</tr>
	</thead>
	<thead>
	<tr class="title">
		<th><?php echo _l('Name'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $role){ ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo $module->link_open_role($role['security_role_id'],true);?>
			</td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="<?php echo $colspan;?>" align="center">
				<?php echo $pagination['links'];?>
			</td>
		</tr>
	</tfoot>
</table>
</form>