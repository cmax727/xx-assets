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


$website_id = (int)$_REQUEST['website_id'];
$website = module_website::get_website($website_id);
if($website_id>0 && $website){
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'edit',
		));
	}
}else{
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'create',
		));
	}
	module_security::sanatise_data('website',$website);
}


?>


	
<form action="" method="post">
	<input type="hidden" name="_process" value="save_website" />
    <input type="hidden" name="website_id" value="<?php echo $website_id; ?>" />
    

    <?php

    $fields = array(
    'fields' => array(
        'name' => 'Name',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php echo _l(module_config::c('project_name_single','Website').' Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Name'); ?>
								</th>
								<td>
									<input type="text" name="name" id="name" value="<?php echo htmlspecialchars($website['name']); ?>" />
								</td>
							</tr>
                            
							<tr>
								<th>
									<?php echo _l('URL'); ?>
								</th>
								<td>
									http://<input type="text" name="url" id="url" style="width: 200px;" value="<?php echo htmlspecialchars($website['url']); ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_website::get_statuses(),'status',$website['status'],'',true,false,true); ?>
								</td>
							</tr>
						</tbody>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'website',
                            'owner_key' => 'website_id',
                            'owner_id' => $website['website_id'],
                            'layout' => 'table_row',
                            )
                        );
                        ?>
					</table>
                    <h3><?php echo _l('Advanced'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>

							<tr>
								<th class="width2">
									<?php echo _l('Change Customer'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_customer::get_customers();
                                    foreach($res as $row){
                                        $c[$row['customer_id']] = $row['customer_name'];
                                    }
                                    echo print_select_box($c,'customer_id',$website['customer_id']);
                                    _h('Changing a customer will also change all the current linked jobs and invoices across to this new customer.');
                                    ?>
								</td>
							</tr>
						</tbody>
					</table>
                    <?php if((int)$website_id>0){
                    if(class_exists('module_group',false)){
                    module_group::display_groups(array(
                         'title' => module_config::c('project_name_single','Website').' Groups',
						'owner_table' => 'website',
						'owner_id' => $website_id,
						'view_link' => module_website::link_open($website_id),

                     ));
                    }

                } ?>

				</td>
                <td valign="top">
                    <?php
                    if($website_id && $website_id!='new'){
                        $note_summary_owners = array();
                        // generate a list of all possible notes we can display for this website.
                        // display all the notes which are owned by all the sites we have access to

                        module_note::display_notes(array(
                            'title' => module_config::c('project_name_single','Website').' Notes',
                            'owner_table' => 'website',
                            'owner_id' => $website_id,
                            'view_link' => module_website::link_open($website_id),
                            )
                        );

                        // show the jobs linked to this website.
                        print_heading(array(
                                           'type'=>'h3',
                                           'title'=>module_config::c('project_name_single','Website').' Jobs',
                                          'button'=>array(
                                              'title'=>'New Job',
                                              'url' =>module_job::link_generate('new',array('arguments'=>array(
                                                                                             'website_id' => $website_id,
                                                                                         ))),
                                          )
                                      ));

                        $c=0;
                        ?>
                        <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                            <thead>
                            <tr>
                                <th>
                                    <?php _e('Job Title'); ?>
                                </th>
                                <th>
                                    <?php _e('Due Date'); ?>
                                </th>
                                <th>
                                    <?php _e('Complete'); ?>
                                </th>
                                <th>
                                    <?php _e('Amount'); ?>
                                </th>
                                <th>
                                    <?php _e('Invoice'); ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach(module_job::get_jobs(array('website_id'=>$website_id)) as $job){
                                    $job = module_job::get_job($job['job_id']);
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
                                            <span class="<?php
                                                echo $job['total_percent_complete'] >= 1 ? 'success_text' : '';
                                                ?>">
                                                <?php echo ($job['total_percent_complete']*100).'%';?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo dollar($job['total_amount_due']);?>
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
                                                    echo dollar($invoice['total_amount_due']);
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
                        <?php
                    }
                    ?>

                </td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save '.module_config::c('project_name_single','Website')); ?>" class="submit_button save_button" />
					<?php if((int)$website_id){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_website::link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
