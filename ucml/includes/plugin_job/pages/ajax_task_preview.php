    <tr class="task_row_<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:54 
  * IP Address: 127.0.0.1
  */ echo $task_id;?> task_preview<?php echo $percentage>=1 ?' tasks_completed':'';?> <?php echo ($task_editable) ? ' task_editable' : '';?>" rel="<?php echo $task_id;?>">
        <?php if($show_task_numbers){ ?>
            <td valign="top" class="task_order"><?php echo $task_data['task_order'];?></td>
        <?php } ?>
        <td valign="top">
            <?php
            if($task_data['approval_required']){
                echo '<span style="font-style: italic;" class="error_text">'._l('(approval required)').'</span> ';
            }
            if(true){ // $task_editable ?>
                <a href="#" onclick="edittask(<?php echo $task_id;?>,0); return false;" class="<?php
                            // set color
                            if($percentage==1){
                                echo 'success_text';
                            }else if($percentage!=1 && $task_due_time < time()){
                                echo 'error_text';
                            }
                            ?>"><?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?></a>
<?php }else{ ?>
                    <span class="<?php
                            // set color
                            if($percentage==1){
                                echo 'success_text';
                            }else if($percentage!=1 && $task_due_time < time()){
                                echo 'error_text';
                            }
                            ?>"><?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?></span>
<?php }

               /*  <div style="z-index: 5; position: relative; min-height:18px; margin-bottom: -18px;"></div>
            <div class="task_percentage task_width"> */
           /* if(module_config::c('job_task_percentage',1) && ($percentage==1 || $task_data['hours']>0)){
                // work out the percentage.


                ?>
                    <div class="task_percentage_label task_width"><?php echo $percentage*100;?>%</div>
                    <div class="task_percentage_bar task_width" style="width:<?php echo round($percentage * $width);?>px;"></div>
                    <?php <div class="task_description">
                        <a href="#" onclick="edittask(<?php echo $task_id;?>,0); return false;" class="<?php
                            // set color
                            if($percentage==1){
                                echo 'success_text';
                            }else if($percentage!=1 && $task_due_time < time()){
                                echo 'error_text';
                            }
                            ?>"><?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?></a>
                    </div> ?>
            <?php }else{ ?>

            <?php } */
            /*</div>*/

            if(isset($task_data['long_description']) && $task_data['long_description'] != ''){ ?>
                <a href="#" class="task_toggle_long_description">&raquo;</a>
                <div class="task_long_description" <?php if(module_config::c('job_tasks_show_long_desc',0)){ ?> style="display:block;" <?php } ?>><?php echo forum_text(trim($task_data['long_description']));?></div>
            <?php }else{ ?>
                &nbsp;
            <?php } ?>
        </td>
        <td valign="top">
            <?php
            // are the logged hours different to the billed hours?
            // are we completed too?
            if($percentage == 1 && $task_data['completed'] < $task_data['hours']){
                echo '<span class="success_text">';
                echo $task_data['hours']>0 ? $task_data['hours'] : '-';
                echo '</span>';
            }else if($percentage == 1 && $task_data['completed'] > $task_data['hours']){
                echo '<span class="error_text">';
                echo $task_data['hours']>0 ? $task_data['hours'] : '-';
                echo '</span>';
            }else{
                echo $task_data['hours']>0 ? $task_data['hours'] : '-';
            }

            ?>
        </td>
        <td valign="top">
            <span class="currency <?php echo $task_data['billable'] ? 'success_text' : 'error_text';?>">
            <?php echo $task_data['amount']>0 ? dollar($task_data['amount'],true,$job['currency_id']) : dollar($task_data['hours']*$job['hourly_rate'],true,$job['currency_id']);?>
            </span>
        </td>
        <td valign="top">
            <?php
            if($task_data['date_due'] && $task_data['date_due'] != '0000-00-00'){

                if($percentage!=1 && $task_due_time < time()){
                    echo '<span class="error_text">';
                    echo print_date($task_data['date_due']);
                    echo '</span>';
                }else{
                    echo print_date($task_data['date_due']);
                }
            }
            ?>
        </td>
        <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
            <td valign="top">
                <?php echo isset($staff_member_rel[$task_data['user_id']]) ? $staff_member_rel[$task_data['user_id']] : ''; ?>
            </td>
        <?php } ?>
        <td valign="top">
           <span class="<?php echo $percentage >= 1 ? 'success_text' : 'error_text';?>">
                <?php echo $percentage*100;?>%
            </span>
        </td>
        <td align="center" valign="top">
            <?php if($task_data['invoiced'] && $task_data['invoice_id']){
                if(module_invoice::can_i('view','Invoices')){
                    //$invoice = module_invoice::get_invoice($task_data['invoice_id']);
                    echo module_invoice::link_open($task_data['invoice_id'],true);
                }
                /*echo " ";
                echo '<span class="';
                if($invoice['total_amount_due']>0){
                    echo 'error_text';
                }else{
                    echo 'success_text';
                }
                echo '">';
                if($invoice['total_amount_due']>0){
                    echo dollar($invoice['total_amount_due'],true,$job['currency_id']);
                    echo ' '._l('due');
                }else{
                    echo _l('All paid');
                }
                echo '</span>';*/
            }else if($task_editable){ ?>
                <a href="#" class="ui-state-default ui-corner-all ui-icon ui-icon-<?php echo $percentage == 1 ? 'pencil' : 'check';?>" title="<?php _e( $percentage == 1 ? 'Edit' : 'Complete');?>" onclick="edittask(<?php echo $task_id;?>,<?php echo ($task_data['hours']>0?($task_data['hours']-$task_data['completed']):1);?>); return false;"><?php _e('Edit');?></a>

            <?php } ?>
        </td>
    </tr>