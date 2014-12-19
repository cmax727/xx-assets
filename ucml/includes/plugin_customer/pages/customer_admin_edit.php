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

$customer_id = (int)$_REQUEST['customer_id'];
$customer = array();

$customer = module_customer::get_customer($customer_id);

// check permissions.
if(class_exists('module_security',false)){
    if($customer_id>0 && $customer['customer_id']==$customer_id){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Customer',
            'page_name' => 'Customers',
            'module' => 'customer',
            'feature' => 'Edit',
		));
    }else{
		module_security::check_page(array(
			'category' => 'Customer',
            'page_name' => 'Customers',
            'module' => 'customer',
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('customer',$customer);
}

?>
<form action="" method="post" id="customer_form">
	<input type="hidden" name="_process" value="save_customer" />
	<input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />

    <?php
    module_form::set_required(array(
        'fields' => array(
            'customer_name' => 'Name',
            'name' => 'Contact Name',
        ))
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );
    ?>

	<table cellpadding="10" width="100%">
		<tr>
			<td width="50%" valign="top">

				<h3><?php echo _l('Customer Information'); ?></h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
					<tbody>
						<tr>
							<th class="width1">
								<?php echo _l('Name'); ?>
							</th>
							<td>
								<input type="text" name="customer_name" id="customer_name" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" />
							</td>
						</tr>
                        <?php if($customer_id && $customer_id!='new'){ ?>
						<tr>
							<th>
								<?php echo _l('Logo'); ?>
							</th>
							<td>
								 <?php
                                 module_file::display_files(array(
                                    //'title' => 'Certificate Files',
                                    'owner_table' => 'customer',
                                    'owner_id' => $customer_id,
                                    'layout' => 'list',
                                    )
                                );
                                ?>
							</td>
						</tr>
                        <?php } ?>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'customer',
                            'owner_key' => 'customer_id',
                            'owner_id' => $customer_id,
                            'layout' => 'table_row',
                            )
                        );
                        ?>
					</tbody>
				</table>


                <h3><?php echo _l('Primary Contact Details'); ?></h3>

				<?php
				// we use the "user" module to find the user details
				// for the currently selected primary contact id
				if($customer['primary_user_id']){
					module_user::print_contact_summary($customer['primary_user_id'],'new');
				}else{
					// hack to create new contact details.
                    module_user::print_contact_summary(false,'new');
				}
				?>

				<h3><?php echo _l('Address'); ?></h3>

				<?php
				handle_hook("address_block",$module,"physical","customer","customer_id");
				?>

				
                <?php if(module_customer::can_i('edit','Customer Credit')){ ?>

                    <h3><?php echo _l('Advanced'); ?></h3>

                    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
                        <tbody>
                            <tr>
                                <th class="width1">
                                    <?php echo _l('Credit'); ?>
                                </th>
                                <td>
                                    <?php echo currency('<input type="text" name="credit" value="'.htmlspecialchars($customer['credit']).'" class="currency" />'); ?>
                                    <?php _h('If the customer is given a credit here you will have an option to apply this credit to an invoice. If a customer over pays an invoice you will be prompted to add that overpayment as credit onto their account.');?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                <?php } ?>


			</td>




			<td width="50%" valign="top">
				<?php
				if($customer_id && $customer_id!='new'){


                    if(class_exists('module_group',false)){
                    module_group::display_groups(array(
                         'title' => 'Customer Groups',
						'owner_table' => 'customer',
						'owner_id' => $customer_id,
						'view_link' => $module->link_open($customer_id),

                    ));
                    }

                    
					$note_summary_owners = array();
					// generate a list of all possible notes we can display for this customer.
					// display all the notes which are owned by all the sites we have access to

					// display all the notes which are owned by all the users we have access to
					foreach(module_user::get_users(array('customer_id'=>$customer_id)) as $val){
						$note_summary_owners['user'][] = $val['user_id'];
					}
                    foreach(module_website::get_websites(array('customer_id'=>$customer_id)) as $val){
						$note_summary_owners['website'][] = $val['website_id'];
					}
                    foreach(module_job::get_jobs(array('customer_id'=>$customer_id)) as $val){
						$note_summary_owners['job'][] = $val['job_id'];
                        foreach(module_invoice::get_invoices(array('job_id'=>$val['job_id'])) as $val){
                            $note_summary_owners['invoice'][] = $val['invoice_id'];
                        }
					}
					/*foreach($module_quote->get_quotes(array('customer_id'=>$customer_id)) as $quote){
						if(!isset($note_summary_owners['quote'])){
							$note_summary_owners['quote']=array();
						}
						$note_summary_owners['quote'][] = $quote['quote_id'];
					}*/
					module_note::display_notes(array(
						'title' => 'All Customer Notes',
						'owner_table' => 'customer',
						'owner_id' => $customer_id,
						'view_link' => $module->link_open($customer_id),
						'display_summary' => true,
						'summary_owners' => $note_summary_owners
						)
					);


				}
				?>



			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
				<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
				<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>"
                       onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />

			</td>
		</tr>
	</table>

</form>

