<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:18 
  * IP Address: 127.0.0.1
  */
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$templates = $module->get_templates($search);
?>


<h2>
	<span class="button">
		<?php echo create_link("Add New","add",module_template::link_open('new')); ?>
	</span>
	<?php echo _l('System Templates'); ?>
</h2>


<form action="" method="post">

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Template Name'); ?></th>
		<th><?php echo _l('Template Description'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($templates as $template){ ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="row_action">
	            <?php echo module_template::link_open($template['template_id'],true);?>
            </td>
			<td>
				<?php echo htmlspecialchars($template['description']); ?>
			</td>
        </tr>
	<?php } ?>
  </tbody>
</table>
</form>