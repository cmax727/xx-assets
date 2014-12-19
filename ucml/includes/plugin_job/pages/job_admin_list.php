<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:54 
  * IP Address: 127.0.0.1
  */

if(!$job_safe)die('denied');

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
$jobs = module_job::get_jobs($search);


// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_job::can_i('view','Export Jobs')){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this customers?
        array(
            'name' => 'Job Export',
            'fields'=>array(
                'Job ID' => 'job_id',
                'Job Title' => 'name',
                'Hourly Rate' => 'hourly_rate',
                'Start Date' => 'date_start',
                'Due Date' => 'date_due',
                'Completed Date' => 'date_completed',
                module_config::c('project_name_single','Website').' Name' => 'website_name',
                'Customer Name' => 'customer_name',
                'Type' => 'type',
                'Status' => 'status',
                'Staff Member' => 'staff_member',
                'Tax Name' => 'total_tax_name',
                'Tax Percent' => 'total_tax_rate',
                'Renewal Date' => 'date_renew',
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'job',
                'owner_id' => 'job_id',
            ),
        )
    );
}
?>

<h2>
	<span class="button">
		<?php echo create_link("Add New job","add",module_job::link_open('new')); ?>
	</span>
    <?php if(class_exists('module_import_export',false) && module_job::can_i('view','Import Jobs')){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_job::handle_import',
                'name'=>'Jobs',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>'job',
                'fields'=>array(
                    'Job ID' => 'job_id',
                    'Job Title' => 'name',
                    'Hourly Rate' => 'hourly_rate',
                    'Start Date' => 'date_start',
                    'Due Date' => 'date_due',
                    'Completed Date' => 'date_completed',
                    module_config::c('project_name_single','Website').' Name' => 'website_name',
                    'Customer Name' => 'customer_name',
                    'Type' => 'type',
                    'Status' => 'status',
                    'Staff Member' => 'staff_member',
                    'Tax Name' => 'total_tax_name',
                    'Tax Percent' => 'total_tax_rate',
                    'Renewal Date' => 'date_renew',
                ),
                // do we attempt to import extra fields?
                'extra' => array(
                    'owner_table' => 'job',
                    'owner_id' => 'job_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Jobs","add",$link); ?>
        </span>
        <?php
    } ?>
	<?php echo _l('Customer Jobs'); ?>
</h2>

<form action="" method="post">


<table class="search_bar" width="100%">
	<tr>
		<th width="70"><?php _e('Filter By:'); ?></th>
		<td width="80">
			<?php echo _l('Job Title:');?>
		</td>
		<td>
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
		<td width="30">
			<?php echo _l('Status:');?>
		</td>
		<td>
			<?php echo print_select_box(module_job::get_statuses(),'search[status]',isset($search['status'])?$search['status']:''); ?>
		</td>
		<td align="right">
			<?php echo create_link("Reset","reset",module_job::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($jobs);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Job Title'); ?></th>
		<th><?php echo _l('Due Date'); ?></th>
		<th><?php echo _l('Completed Date'); ?></th>
		<th><?php echo _l(module_config::c('project_name_single','Website')); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th><?php echo _l('Customer'); ?></th>
        <?php } ?>
		<th><?php echo _l('Type'); ?></th>
		<th><?php echo _l('Status'); ?></th>
        <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
		<th><?php echo _l('Staff Member'); ?></th>
        <?php  } ?>
		<th><?php echo _l('Progress'); ?></th>
		<th><?php echo _l('Job Total'); ?></th>
		<th><?php echo _l('Invoice'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $job_original){
            $job = module_job::get_job($job_original['job_id']);
            ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <?php echo module_job::link_open($job['job_id'],true);?>
                </td>
                <td>
                    <?php
                    if($job['total_percent_complete']!=1 && strtotime($job['date_due']) < time()){
                        echo '<span class="error_text">';
                        echo print_date($job['date_due']);
                        echo '</span>';
                    }else{
                        echo print_date($job['date_due']);
                    }
                    ?>
                </td>
                <td>
                    <?php echo print_date($job['date_completed']);?>
                </td>
                <td>
                    <?php echo module_website::link_open($job['website_id'],true);?>
                </td>
                <?php if(!isset($_REQUEST['customer_id'])){ ?>
                <td>
                    <?php echo module_customer::link_open($job['customer_id'],true);?>
                </td>
                <?php } ?>
                <td>
                    <?php echo htmlspecialchars($job['type']);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($job['status']);?>
                </td>
                <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
                <td>
                    <?php echo htmlspecialchars($job_original['staff_member']);?>
                </td>
                <?php } ?>
                <td>
                    <span class="<?php
                        echo $job['total_percent_complete'] >= 1 ? 'success_text' : '';
                        ?>">
                        <?php echo ($job['total_percent_complete']*100).'%';?>
                    </span>
                </td>
                <td>
                    <span class="currency">
                    <?php echo dollar($job['total_amount'],true,$job['currency_id']);?>
                    </span>
                </td>
                <td>
                    <?php foreach(module_invoice::get_invoices(array('job_id'=>$job['job_id'])) as $invoice){
                        $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                        echo module_invoice::link_open($invoice['invoice_id'],true);
                        echo " ";
                        echo '<span class="';
                        if($invoice['total_amount_due']>0){
                            echo 'error_text';
                        }else{
                            echo 'success_text';
                        }
                        echo '">';
                        if($invoice['total_amount_due']>0){
                            echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);
                            echo ' '._l('due');
                        }else{
                            echo _l('All paid');
                        }
                        echo '</span>';
                        echo "<br>";
                    } ?>
                </td>
            </tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>