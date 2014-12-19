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

$job_task_creation_permissions = module_job::get_job_task_creation_permissions();

$job_id = (int)$_REQUEST['job_id'];
$job = module_job::get_job($job_id);
$staff_members = module_user::get_staff_members();
$staff_member_rel = array();
foreach($staff_members as $staff_member){
    $staff_member_rel[$staff_member['user_id']] = $staff_member['name'];
}


// check permissions.
if(class_exists('module_security',false)){
    module_security::check_page(array(
        'category' => 'Job',
        'page_name' => 'Jobs',
        'module' => 'job',
        'feature' => 'create',
    ));
}

$job_tasks = array(); //module_job::get_tasks($job_id);
?>

<script type="text/javascript">
    var completed_tasks_hidden = false; // set with session variable / cookie
    var editing_task_id = false;
    function show_completed_tasks(){

    }
    function hide_completed_tasks(){

    }
    function setamount(a,task_id){
        var ee = parseFloat(a);
        if(ee>0){
            $('#'+task_id+'taskamount').val(ee * <?php echo $job['hourly_rate'];?>);
            $('#'+task_id+'complete_hour').val(ee);
        }
    }
    function canceledittask(){
        if(editing_task_id){
            $('#task_edit_'+editing_task_id).html(loading_task_html);
            editing_task_id = false;
        }
        $('.task_edit').hide();
        $('.task_preview').show();
    }
    $(function(){
        $('.task_toggle_long_description').click(function(event){
            event.preventDefault();
            $(this).parent().find('.task_long_description').slideToggle();
            return false;
        });
    });
</script>

<form action="" method="post" id="job_form">
        <input type="hidden" name="_process" value="save_job" />
        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>" />
        <input type="hidden" name="customer_id" value="<?php echo $job['customer_id']; ?>" />


            <?php

            $fields = array(
            'fields' => array(
                'name' => 'Name',
            ));
            module_form::set_required(
                $fields
            );
            //module_form::set_default_field('new_task_description');
            module_form::set_default_field('name');
            module_form::prevent_exit(array(
                'valid_exits' => array(
                    // selectors for the valid ways to exit this form.
                    '.submit_button',
                    '.save_task',
                    '.delete',
                    '.task_defaults',
                ))
            );


            ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">




					<h3><?php echo _l('Job Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Job Title'); ?>
								</th>
								<td>
									<input type="text" name="name" id="name" value="<?php echo htmlspecialchars($job['name']); ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Type'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_job::get_types(),'type',$job['type'],'',true,false,true); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Hourly Rate'); ?>
								</th>
								<td>
									<?php echo currency('<input type="text" name="hourly_rate" class="currency" value="'.$job['hourly_rate'].'">');?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_job::get_statuses(),'status',$job['status'],'',true,false,true); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Start Date'); ?>
								</th>
								<td>
									<input type="text" name="date_start" class="date_field" value="<?php echo print_date($job['date_start']);?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Due Date'); ?>
								</th>
								<td>
									<input type="text" name="date_due" class="date_field" value="<?php echo print_date($job['date_due']);?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Finished Date'); ?>
								</th>
								<td>
									<input type="text" name="date_completed" class="date_field" value="<?php echo print_date($job['date_completed']);?>">
								</td>
							</tr>
                            <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
                            <tr>
                                <th>
                                    <?php _e('Staff Member');?>
                                </th>
                                <td>
                                    <?php
                                    echo print_select_box($staff_member_rel,'user_id',$job['user_id']);
                                    _h('Assign a staff member to this job. You can also assign individual tasks to different staff members.');
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
							<tr>
								<th>
									<?php echo _l('Tax'); ?>
								</th>
								<td>
									<input type="text" name="total_tax_name" value="<?php echo htmlspecialchars($job['total_tax_name']);?>" style="width:30px;">
									@
                                    <input type="text" name="total_tax_rate" value="<?php echo htmlspecialchars($job['total_tax_rate']);?>" style="width:35px;">%

								</td>
							</tr>

                            <tr>
                                <th>
                                    <?php echo _l('Currency'); ?>
                                </th>
                                <td>
                                    <?php echo print_select_box(get_multiple('currency','','currency_id'),'currency_id',$job['currency_id'],'',false,'code'); ?>
                                </td>
                            </tr>

						</tbody>
					</table>

                    

                    <h3><?php echo _l('Advanced'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>

							<tr>
								<th class="width1">
									<?php echo _l('Assign '.module_config::c('project_name_single','Website')); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    // change between websites within this customer?
                                    // or websites all together?
                                    $res = module_website::get_websites(array('customer_id'=>(isset($_REQUEST['customer_id'])?(int)$_REQUEST['customer_id']:false)));
                                    //$res = module_website::get_websites();
                                    while($row = array_shift($res)){
                                        $c[$row['website_id']] = $row['name'];
                                    }
                                    echo print_select_box($c,'website_id',$job['website_id']);
                                    ?>
                                    <?php if($job['website_id'] && module_website::can_i('view','Websites')){ ?>
                                        <a href="<?php echo module_website::link_open($job['website_id'],false);?>"><?php _e('Open');?></a>
                                    <?php } ?>
                                    <?php _h('This will be the '.module_config::c('project_name_single','Website').' this job is assigned to - and therefor the customer. Every job should have a'.module_config::c('project_name_single','Website').' assigned. Clicking the open link will take you to the '.module_config::c('project_name_single','Website'));?>
								</td>
							</tr>
                            <tr>
                                <th>
                                    <?php echo _l('Assign Customer'); ?>
                                </th>
                                <td>
                                    <?php
                                    $c = array();
                                    $customers = module_customer::get_customers();
                                    foreach($customers as $customer){
                                        $c[$customer['customer_id']] = $customer['customer_name'];
                                    }
                                    echo print_select_box($c,'customer_id',$job['customer_id']);
                                    ?>
                                    <?php if($job['customer_id'] && module_customer::can_i('view','Customers')){ ?>
                                    <a href="<?php echo module_customer::link_open($job['customer_id'],false);?>"><?php _e('Open');?></a>
                                    <?php } ?>
                                </td>
                            </tr>

							<tr>
								<th class="width1">
									<?php echo _l('Renewal Date'); ?>
								</th>
								<td>
                                    <?php if($job['renew_job_id']){
                                        echo _l('This job was renewed on %s.',print_date($job['date_renew']));
                                        echo '<br/>';
                                        echo _l('A new job was created, please click <a href="%s">here</a> to view it.',module_job::link_open($job['renew_job_id']));
                                    }else{
                                        ?>
                                        <input type="text" name="date_renew" class="date_field" value="<?php echo print_date($job['date_renew']);?>">
                                        <?php 
                                        if($job['date_renew'] && $job['date_renew'] != '0000-00-00' && strtotime($job['date_renew']) <= strtotime('+'.module_config::c('alert_days_in_future',5).' days')){
                                            // we are allowed to generate this renewal.
                                            ?>
                                            <input type="button" name="generate_renewal_btn" value="<?php echo _l('Generate Renewal');?>" class="submit_button" onclick="$('#generate_renewal_gogo').val(1); this.form.submit();">
                                            <input type="hidden" name="generate_renewal" id="generate_renewal_gogo" value="0">

                                            <?php
                                            _h('A renewal is available for this job. Clicking this button will create a new job based on this job, and set the renewal reminder up again for the next date.');
                                        }else{
                                            _h('You will be reminded to renew this job on this date. You will be given the option to renew this job closer to the renewal date (a new button will appear).');
                                        }
                                } ?>
								</td>
                                <?php
                            $job_default_tasks = module_job::get_default_tasks();
                                if(module_config::c('job_enable_default_tasks',1) && count($job_default_tasks)){


                                ?>
                                <tr>
                                    <th>
                                        <?php _e('Task Defaults'); ?>
                                    </th>
                                    <td>
                                        <?php
                                        echo print_select_box($job_default_tasks,'default_task_list_id','','',true,'');
                                        ?>
                                        <input type="button" name="i" id="insert_saved" value="<?php _e('Insert');?>">
                                        <input type="hidden" name="default_tasks_action" id="default_tasks_action" value="0" class="task_defaults">
                                        <script type="text/javascript">
                                            $(function(){
                                                $('#insert_saved').click(function(){
                                                    // set a flag and submit our form.
                                                    $('#default_tasks_action').val('insert_default');
                                                    $('#job_form')[0].submit();
                                                });
                                            });
                                        </script>
                                        <?php _h('Here you can insert a previously saved set of default tasks.'); ?>
                                    </td>
                                </tr>
                                <?php } ?>
							</tr>

						</tbody>
					</table>

                    <p align="center">
                        <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save job'); ?>" class="submit_button save_button" />
                        <input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_job::link_open(false); ?>';" class="submit_button" />
                    </p>


				</td>
                <td valign="top">



                <?php if(module_job::can_i('view','Job Tasks')){ ?>

					<h3>
                        <span class="button" id="show_completed_tasks" style="display:none;">
                            <a href="javascript:$('.tasks_completed').show(); $('#show_completed_tasks').hide(); $('#hide_completed_tasks').show(); return false;" class="uibutton"><?php _e('Show Completed Tasks');?></a>
                        </span>
                        <span class="button" id="hide_completed_tasks">
                            <a href="javascript:$('.tasks_completed').hide(); $('#show_completed_tasks').show(); $('#hide_completed_tasks').hide(); return false;" class="uibutton"><?php _e('Hide Completed Tasks');?></a>
                        </span>
                        <?php echo _l('Job Tasks %s',($job['total_percent_complete']>0 ? _l('(%s%% completed)',$job['total_percent_complete']*100) : '')); ?>

                    </h3>

                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <?php if(module_config::c('job_show_task_numbers',1)){ ?>
                            <th width="10">#</th>
                            <?php } ?>
                            <th class="task_column task_width"><?php _e('Description');?></th>
                            <th width="10"><?php _e('Hours');?></th>
                            <th width="72"><?php _e('Amount');?></th>
                            <th width="83"><?php _e('Due Date');?></th>
                            <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
                            <th width="78"><?php _e('Staff');?></th>
                            <?php } ?>
                            <th width="32" nowrap="nowrap">%</th>
                            <th width="60"> </th>
                        </tr>
                        </thead>
                        <?php
                            if(module_security::is_page_editable() && module_job::can_i('create','Job Tasks') && $job_task_creation_permissions != _JOB_TASK_CREATION_NOT_ALLOWED){ ?>
						<tbody>
                        <tr>
                            <?php if(module_config::c('job_show_task_numbers',1)){ ?>
                                <td valign="top">&nbsp;</td>
                            <?php } ?>
                            <td valign="top">
                                <input type="text" name="job_task[new][description]" id="new_task_description" class="edit_task_description" value="">
                                <a href="#" class="task_toggle_long_description">&raquo;</a>
                                <div class="task_long_description">
                                    <textarea name="job_task[new][long_description]" class="edit_task_long_description"></textarea>
                                </div>
                            </td>
                            <td valign="top">
                                <input type="text" name="job_task[new][hours]" value="" size="3" style="width:25px;" onchange="setamount(this.value,'new');" onkeyup="setamount(this.value,'new');">
                            </td>
                            <td valign="top" nowrap="">
                                <?php echo currency('<input type="text" name="job_task[new][amount]" value="" id="newtaskamount" class="currency">');?>
                            </td>
                            <td valign="top">
                                <input type="text" name="job_task[new][date_due]" value="<?php echo print_date($job['date_due']);?>" class="date_field">
                            </td>
                            <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
                                <td valign="top">
                                    <?php echo print_select_box($staff_member_rel,'job_task[new][user_id]',
                                        isset($staff_member_rel[module_security::get_loggedin_id()]) ? module_security::get_loggedin_id() : false, 'job_task_staff_list', ''); ?>
                                </td>
                            <?php } ?>
                            <td valign="top">
                                <input type="checkbox" name="job_task[new][new_fully_completed]" value="1">
                            </td>
                            <td align="center" valign="top">
                                <input type="submit" name="save" value="<?php _e('New Task');?>" class="save_task">
                            </td>
                        </tr>
						</tbody>
                        <?php } ?>
                        <?php
                        $c=0;
                        $task_number = 0;
                        foreach($job_tasks as $task_id => $task_data){
                            $task_number++;
                            if(module_security::is_page_editable() && module_job::can_i('edit','Job Tasks')){ ?> 
                                <tbody id="task_edit_<?php echo $task_id;?>" style="display:none;" class="task_edit"></tbody>
                            <?php  } else {
                                $task_editable = false;
                            }
                            echo module_job::generate_task_preview($job_id,$job,$task_id,$task_data,$task_number);
                        } ?>
                        </table>

            <?php }  // end can i view job tasks ?>

                


                </td>
			</tr>
		</tbody>
	</table>

</form>