<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:46 
  * IP Address: 127.0.0.1
  */
if(!$invoice_safe)die('failed');

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
$invoices = module_invoice::get_invoices($search);

?>

<h2>
	<!--<span class="button">
		<?php /*echo create_link("Add New invoice","add",module_invoice::link_open('new')); */?>
	</span>-->
	<?php echo _l('Invoices'); ?>
</h2>

<form action="" method="post">


<table class="search_bar" width="100%">
	<tr>
		<th width="70"><?php _e('Filter By:'); ?></th>
		<td width="180">
			<?php echo _l('Invoice Number:');?>
		</td>
		<td>
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
		<td width="30">
			<?php echo _l('Status:');?>
		</td>
		<td>
			<?php echo print_select_box(module_invoice::get_statuses(),'search[status]',isset($search['status'])?$search['status']:''); ?>
		</td>
		<td align="right">
			<?php echo create_link("Reset","reset",module_invoice::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($invoices);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Invoice Number'); ?></th>
		<th><?php echo _l('Status'); ?></th>
		<th><?php echo _l('Due Date'); ?></th>
		<th><?php echo _l('Sent Date'); ?></th>
		<th><?php echo _l('Paid Date'); ?></th>
		<th><?php echo _l('Website'); ?></th>
		<th><?php echo _l('Job'); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th><?php echo _l('Customer'); ?></th>
        <?php } ?>
		<th><?php echo _l('Invoice Total'); ?></th>
		<th><?php echo _l('Amount Due'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $invoice){
            $invoice = module_invoice::get_invoice($invoice['invoice_id']);
            ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <?php echo module_invoice::link_open($invoice['invoice_id'],true);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($invoice['status']); ?>
                </td>
                <td>
                    <?php
                    if((!$invoice['date_paid']||$invoice['date_paid']=='0000-00-00') && strtotime($invoice['date_due']) < time()){
                        echo '<span class="error_text">';
                        echo print_date($invoice['date_due']);
                        echo '</span>';
                    }else{
                        echo print_date($invoice['date_due']);
                    }
                    ?>
                </td>
                <td>
                    <?php if($invoice['date_sent'] && $invoice['date_sent'] != '0000-00-00'){ ?>
                        <?php echo print_date($invoice['date_sent']);?>
                    <?php }else{ ?>
                        <span class="error_text"><?php _e('Not sent');?></span>
                    <?php } ?>
                </td>
                <td>
                    <?php if($invoice['date_paid'] && $invoice['date_paid'] != '0000-00-00'){ ?>
                        <?php echo print_date($invoice['date_paid']);?>
                    <?php }else if(($invoice['date_due'] && $invoice['date_due']!='0000-00-00') && (!$invoice['date_paid'] || $invoice['date_paid'] == '0000-00-00') && strtotime($invoice['date_due']) < time()){ ?>
                        <span class="error_text" style="font-weight: bold; text-decoration: underline;"><?php _e('Overdue');?></span>
                    <?php }else{ ?>
                        <span class="error_text"><?php _e('Not paid');?></span>
                    <?php } ?>
                </td>
                <td>
                    <?php
                    foreach($invoice['website_ids'] as $website_id){
                        if((int)$website_id>0){
                            echo module_website::link_open($website_id,true);
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php
                    foreach($invoice['job_ids'] as $job_id){
                        if((int)$job_id>0){
                            echo module_job::link_open($job_id,true);
                        }
                    }
                    ?>
                </td>
                <?php if(!isset($_REQUEST['customer_id'])){ ?>
                <td>
                    <?php echo module_customer::link_open($invoice['customer_id'],true);?>
                </td>
                <?php } ?>
                <td>
                    <?php echo dollar($invoice['total_amount'],true,$invoice['currency_id']);?>
                </td>
                <td>
                    <?php echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);?>
                    <?php if($invoice['total_amount_credit'] > 0){
                ?>
                <span class="success_text"><?php echo _l('Credit: %s',dollar($invoice['total_amount_credit'],true,$invoice['currency_id']));?></span>
                <?php
            } ?>
                </td>
            </tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>