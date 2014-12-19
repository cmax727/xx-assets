<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
	<tbody>
		<tr>
			<th class="width1">
				<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:03 
  * IP Address: 127.0.0.1
  */ echo _l('Customer Name'); ?>
			</th>
			<td>
				<?php echo $customer_data['customer_name'];?>
				<a href="<?php echo module_customer::link_open($customer_id);?>">&raquo;</a>
			</td>
		</tr>
	</tbody>
</table>