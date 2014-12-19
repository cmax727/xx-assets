<tr class="task_row_<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:54 
  * IP Address: 127.0.0.1
  */ echo $task_id;?>">
    <?php if($show_task_numbers){ ?>
        <td rowspan="2" valign="top" style="padding:0.3em 0;">
            <input type="text" name="job_task[<?php echo $task_id;?>][task_order]" value="<?php echo $task_data['task_order'];?>" size="3" class="edit_task_order">
        </td>
    <?php } ?>
    <td>
        <?php if($task_editable && module_job::can_i('delete','Job Tasks')){ ?>
        <a href="#" onclick="if(confirm('<?php _e('Delete Task?');?>')){$(this).parent().find('input').val('<?php echo _TASK_DELETE_KEY;?>'); $('#job_task_form')[0].submit();} return false;" class="delete ui-state-default ui-corner-all ui-icon ui-icon-trash" style="display:inline-block; float:right;">[x]</a>
        <?php } ?>
        <input type="text" class="edit_task_description" name="job_task[<?php echo $task_id;?>][description]" value="<?php echo htmlspecialchars($task_data['description']);?>" id="task_desc_<?php echo $task_id;?>" tabindex="10">
    </td>
    <td>
        <?php if($task_editable){ ?>
        <input type="text" name="job_task[<?php echo $task_id;?>][hours]" value="<?php echo $task_data['hours'];?>" size="3" style="width:25px;"  onchange="setamount(this.value,'<?php echo $task_id;?>');" onkeyup="setamount(this.value,'<?php echo $task_id;?>');" tabindex="12">
        <?php }else{ ?>
        <?php echo $task_data['hours'];?>
        <?php } ?>
    </td>
    <td nowrap="">
        <?php if($task_editable){ ?>
            <?php echo currency('<input type="text" name="job_task['.$task_id.'][amount]" value="'.($task_data['amount']>0 ? ($task_data['amount']) : ($task_data['hours']*$job['hourly_rate'])).'" id="'.$task_id.'taskamount" class="currency" tabindex="13">');?>
        <?php }else{ ?>
            <?php echo $task_data['amount']>0 ? dollar($task_data['amount'],true,$job['currency_id']) : dollar($task_data['hours']*$job['hourly_rate'],true,$job['currency_id']);?>
        <?php } ?>
    </td>
    <td>
        <input type="text" name="job_task[<?php echo $task_id;?>][date_due]" value="<?php echo print_date($task_data['date_due']);?>" class="date_field" tabindex="14">
    </td>
    <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
        <td>
            <?php echo print_select_box($staff_member_rel,'job_task['.$task_id.'][user_id]',
        isset($staff_member_rel[$task_data['user_id']]) ? $task_data['user_id'] : false, 'job_task_staff_list', ''); ?>
        </td>
    <?php } ?>
    <td>
        &nbsp;
    </td>
    <td nowrap="nowrap" align="center">
        <input type="submit" name="ts" class="save_task" value="<?php _e('Save');?>" tabindex="20" style="float:left;">
        <a href="#" class="delete ui-state-default ui-corner-all ui-icon ui-icon-arrowreturn-1-w" style="float:right;" title="<?php _e('Cancel');?>" onclick="refresh_task_preview(<?php echo $task_id;?>,false); return false;">cancel</a>
    </td>
</tr>
<tr class="task_row_<?php echo $task_id;?>">
    <td>
       <textarea name="job_task[<?php echo $task_id;?>][long_description]" class="edit_task_long_description" tabindex="11"><?php echo htmlspecialchars($task_data['long_description']);?></textarea>
    </td>
    <td colspan="<?php echo (module_config::c('job_allow_staff_assignment',1)) ? '4' : '3';?>" valign="top">
        <?php if(module_config::c('job_task_log_all_hours',1) || $task_data['hours']>0){ ?>
            <?php echo _l('%s of %s hours have been logged:',(float)$task_data['completed'],$task_data['hours']);?>
            <input type="hidden" name="job_task[<?php echo $task_id;?>][completed]" value="<?php echo $task_data['completed'];?>">
            <br/>
            <?php
            // show a log of any existing hours against this task.
            $task_logs = module_job::get_task_log($task_id);
            foreach($task_logs as $task_log){
                echo _l('%s hrs <span class="text_shrink">%s</a> - <span class="text_shrink">%s</span>',$task_log['hours'],print_date($task_log['log_time'],true),$staff_member_rel[$task_log['create_user_id']]);
                ?> <a href="#" class="error_text" onclick="return delete_task_hours(<?php echo $task_id;?>,<?php echo $task_log['task_log_id'];?>);">x</a> <?php
                echo '<br/>';
            }
        } ?>


        <?php if((module_config::c('job_task_log_all_hours',1) || $task_data['hours']>0) && $task_editable){ ?>
        <?php _e('Log'); ?>
             <input type="text" name="job_task[<?php echo $task_id;?>][log_hours]" value="<?php ?>" size="2" style="width:35px"
                    id="complete_<?php echo $task_id;?>" tabindex="16"> <?php _e('hours');?>
        <?php } ?>

    </td>
    <td colspan="2" valign="top">
        <?php if($task_editable){ ?>
            <input type="hidden" name="job_task[<?php echo $task_id;?>][billable_t]" value="1">
            <input type="checkbox" name="job_task[<?php echo $task_id;?>][billable]" value="1" id="billable_t_<?php echo $task_id;?>" <?php echo $task_data['billable'] ? ' checked':'';?> tabindex="17"> <label for="billable_t_<?php echo $task_id;?>"><?php _e('Task is billable');?></label>
        <?php }else{
            if($task_data['billable']){
                _e('Task is billable');
            }else{
                _e('Task not billable');
            }
        }
        if($task_data['invoiced'] && $task_data['invoice_id']){
            echo '<br/>';
            echo _l('Invoice %s',module_invoice::link_open($task_data['invoice_id'],true));
        }
        ?>
        <br/>

        <?php
        if(module_config::c('job_task_log_all_hours',1) || $task_data['hours']<=0){ ?>
            <?php if($task_editable){ ?>
                <input type="hidden" name="job_task[<?php echo $task_id;?>][fully_completed_t]" value="1">
                <input type="checkbox" name="job_task[<?php echo $task_id;?>][fully_completed]" value="1"
                       id="complete_t_<?php echo $task_id;?>" <?php echo $task_data['fully_completed']>0 ? ' checked':'';?> tabindex="18">
                <label for="complete_t_<?php echo $task_id;?>" id="complete_t_label_<?php echo $task_id;?>"><?php _e('Task completed');?></label>
            <?php }else{
                if($task_data['fully_completed'] == 1){
                    _e('Task completed');
                }else{
                    _e('Task not completed');
                }
            } ?>
        <?php }
        if($job_task_creation_permissions == _JOB_TASK_CREATION_WITHOUT_APPROVAL){
            // this user can create tasks without approval, and therefore approve other peoples tasks.
            if($task_data['approval_required']){ ?>
                <input type="hidden" name="job_task[<?php echo $task_id;?>][approval_required]" value="1" id="approve_task_<?php echo $task_id;?>">
                <br/>
                <input type="button" name="approve" value="<?php _e('Approve Task');?>" onclick="$('#approve_task_<?php echo $task_id;?>').val(0); this.form.submit();" tabindex="19">
            <?php }
        }
        ?>

    </td>
</tr>