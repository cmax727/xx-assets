<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:23 
  * IP Address: 127.0.0.1
  */

if(!$ticket_safe){
    die('failed');
}


$ticket_id = (int)$_REQUEST['ticket_id'];
$ticket = module_ticket::get_ticket($ticket_id);

if($ticket['subject']){
    $module->page_title = _l('Ticket: '.htmlspecialchars($ticket['subject']));
}

$admins_rel = module_ticket::get_ticket_staff_rel();

// work out if this user is an "administrator" or a "customer"
// a user will have "edit" capabilities for tickets if they are an administrator
// a user will only have "view" Capabilities for tickets if they are a "customer"
// this will decide what options they have on the page (ie: assigning tickets to people)


if($ticket_id>0 && $ticket && $ticket['ticket_id']==$ticket_id){
	if(class_exists('module_security',false)){
		/*module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'edit',
		));*/
        // we want to do our own special type of form modification here
        // so we don't pass it off to "check_page" which will hide all input boxes.
        if(!module_ticket::can_i('edit','Tickets') && !module_ticket::can_i('create','Tickets')){
            set_error('Access to editing tickets denied.');
            redirect_browser(module_ticket::link_open(false));
        }
	}
}else{
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'create',
		));
	}
}

if(module_ticket::can_edit_tickets()){
    module_ticket::mark_as_read($ticket_id,true);
}
$module->pre_menu(); // so the links are re-build and the correct "unread" count is at the top.


if(!module_security::can_access_data('ticket',$ticket)){
    echo 'Ticket access denied';
    exit;
}

$ticket_messages = module_ticket::get_ticket_messages($ticket['ticket_id']);

if(!isset($logged_in_user) || !$logged_in_user){
    // we assume the user is on the public side.
    // use the creator id as the logged in id.
    $logged_in_user = module_security::get_loggedin_id();
}
$ticket_creator = $ticket['user_id'];
if($ticket_creator == $logged_in_user){
    // we are sending a reply back to the admin, from the end user.
    $to_user_id = $ticket['assigned_user_id'] ? $ticket['assigned_user_id'] : 1;
    $from_user_id = $logged_in_user;
}else{
    // we are sending a reply back to the ticket user.
    $to_user_id = $ticket['user_id'];
    $from_user_id = $logged_in_user;
}
$to_user_a = module_user::get_user($to_user_id);
$from_user_a = module_user::get_user($from_user_id);

if(isset($ticket['ticket_account_id']) && $ticket['ticket_account_id']){
    $ticket_account = module_ticket::get_ticket_account($ticket['ticket_account_id']);
}else{
    $ticket_account = false;
}


if($ticket_account && $ticket_account['email']){
    $reply_to_address = $ticket_account['email'];
    $reply_to_name = $ticket_account['name'];
}else{
    // reply to creator.
    $reply_to_address = $from_user_a['email'];
    $reply_to_name = $from_user_a['name'];
}


if($ticket_creator == $logged_in_user){
    $send_as_name = $from_user_a['name'];
    $send_as_address = $from_user_a['email'];
}else{
    $send_as_address = $reply_to_address;
    $send_as_name = $reply_to_name;
}



?>

<?php
// find the prev/next tickets.
$temp_prev = $prev_ticket = $next_ticket = false;
$temp_tickets = isset($_SESSION['_ticket_nextprev']) ? $_SESSION['_ticket_nextprev'] : array();
foreach($temp_tickets as $key=>$val){
    if($prev_ticket && !$next_ticket){
        $next_ticket = $val;
    }
    if($val==$ticket_id){
        $prev_ticket = ($temp_prev)?$temp_prev:true;
    }
    $temp_prev = $val;
}
?>
<!-- next / prev links -->
<table width="80%" align="center">
    <tbody>
    <tr>
        <td align="left">
            <?php if($prev_ticket && $prev_ticket!==true){ ?>
            <a href="<?php echo module_ticket::link_open($prev_ticket);?>" class="uibutton"><?php _e('&laquo; Prev Ticket');?></a>
            <?php } ?>
        </td>
        <td align="right">
            <?php if($next_ticket){ ?>
            <a href="<?php echo module_ticket::link_open($next_ticket);?>" class="uibutton"><?php _e('Next Ticket &raquo;');?></a>
            <?php } ?>
        </td>
    </tr>
    </tbody>
</table>
	
<form action="" method="post" id="ticket_form" enctype="multipart/form-data">
	<input type="hidden" name="_process" value="save_ticket" />
    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>" />


    <?php

    $fields = array(
    'fields' => array(
        'subject' => 'Subject',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
            '.save_task',
            '.delete',
            '.attachment_link',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php echo _l('Ticket Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Ticket Number'); ?>
								</th>
								<td>
									<?php echo module_ticket::ticket_number($ticket['ticket_id']);?>
                                    <?php
                                    if($ticket['status_id'] == 2 || $ticket['status_id'] == 3 || $ticket['status_id'] == 5){
                                        echo _l('(%s out of %s tickets)',ordinal($ticket['position']),$ticket['total_pending']);
                                    } 
                                    ?>
                                    <input type="hidden" name="status_id" value="<?php echo $ticket['status_id'];?>"
								</td>
							</tr>
                            <?php if($ticket['last_message_timestamp']){ ?>
                            <tr>
                                <th>
                                    <?php _e('Date/Time');?>
                                </th>
                                <td>
                                    <?php
                                    if($ticket['last_message_timestamp'] < $limit_time){
                                        echo '<span class="important">';
                                    }
                                    echo print_date($ticket['last_message_timestamp'],true);
                                    // how many days ago was this?
                                    echo ' ';
                                    $days = ceil((($ticket['last_message_timestamp']+1) - time())/86400);
                                    if(abs($days) == 0){
                                        _e('(today)');
                                    }else{
                                        _e(' (%s days ago)',abs($days));
                                    }
                                    if($ticket['last_message_timestamp'] < $limit_time){
                                        echo '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <th>
                                    <?php _e('Subject');?>
                                </th>
                                <td>
                                    <?php if($ticket['subject']){
                                    echo htmlspecialchars($ticket['subject']);
                                }else{ ?>
                                    <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($ticket['subject']); ?>" />
    <?php } ?>
                                </td>
                            </tr>
							<tr>
								<th>
									<?php echo _l('Assigned User'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box($admins_rel,'assigned_user_id',$ticket['assigned_user_id']);
                                        echo _h('This is anyone with ticket EDIT permissions.');
                                    }else{
                                        echo friendly_key($admins_rel,$ticket['assigned_user_id']);
                                    }
                                    ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Ticket Creator'); ?>
								</th>
								<td>
									<?php
                                    $create_user = module_user::get_user($ticket['user_id']);
                                    echo module_user::link_open($ticket['user_id'],true);
                                    echo ' ' .htmlspecialchars($create_user['email']);
                                    ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Type/Department'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box(module_ticket::get_types(),'type',$ticket['type'],'',true,false,true);
                                    }else{
                                        echo print_select_box(module_ticket::get_types(),'type',$ticket['type'],'',false);
                                    }
                                    ?>
								</td>
							</tr>
                            <?php if(module_config::c('ticket_support_accounts',1) && module_ticket::get_accounts_rel()){ ?>
							<tr>
								<th>
									<?php echo _l('Account'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box(module_ticket::get_accounts_rel(),'ticket_account_id',$ticket['ticket_account_id']);
                                    }else{
                                        echo friendly_key(module_ticket::get_accounts_rel(),$ticket['ticket_account_id']);
                                    }
                                    ?>
								</td>
							</tr>
                            <?php } ?>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box(module_ticket::get_statuses(),'status_id',$ticket['status_id']);
                                    }else{
                                        echo friendly_key(module_ticket::get_statuses(),$ticket['status_id']);
                                    }
                                    ?>
								</td>
							</tr>
						</tbody>
                        <?php
                        if(isset($ticket['ticket_id']) && $ticket['ticket_id'] && module_ticket::can_edit_tickets()){
                             module_extra::display_extras(array(
                                'owner_table' => 'ticket',
                                'owner_key' => 'ticket_id',
                                'owner_id' => $ticket['ticket_id'],
                                'layout' => 'table_row',
                                )
                            );
                        }
                        ?>
					</table>
                    <?php
                    if(isset($ticket['ticket_id']) && $ticket['ticket_id'] && module_ticket::can_edit_tickets()){
                        if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1)){
                            module_group::display_groups(array(
                                 'title' => 'Ticket Groups',
                                'owner_table' => 'ticket',
                                'owner_id' => $ticket['ticket_id'],
                                'view_link' => module_ticket::link_open($ticket['ticket_id']),

                             ));
                        }
                    }
                    ?>

                    <?php if(module_ticket::can_edit_tickets()){ ?>
                    <h3><?php echo _l('Related to'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>

							<tr>
								<th class="width1">
									<?php echo _l('Customer'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_customer::get_customers();
                                    while($row = array_shift($res)){
                                        $c[$row['customer_id']] = $row['customer_name'];
                                    }
                                    if(false && module_ticket::can_i('edit','Related to','Tickets')){
                                        echo print_select_box($c,'customer_id',$ticket['customer_id']);
                                    }else if($ticket['customer_id']){
                                        echo isset($c[$ticket['customer_id']]) ? $c[$ticket['customer_id']] : 'N/A';
                                    }
                                    ?>
								</td>
							</tr>
                            <?php if($ticket['customer_id']){ ?>
							<tr>
								<th>
									<?php echo _l('Contact'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_user::get_users(array('customer_id'=>$ticket['customer_id']));
                                    while($row = array_shift($res)){
                                        $c[$row['user_id']] = $row['name'];
                                    }
                                    if(false && module_ticket::can_i('edit','Related to')){
                                        echo print_select_box($c,'user_id',$ticket['user_id']);
                                    }else if($ticket['user_id']){
                                        echo isset($c[$ticket['user_id']]) ? $c[$ticket['user_id']] : 'N/A';
                                    }
                                    ?>
								</td>
							</tr>
                                <?php

                            $res = module_website::get_websites(array('customer_id'=>$ticket['customer_id']));
                                if(count($res)){
                            ?>
							<tr>
								<th>
									<?php echo _l(''.module_config::c('project_name_single','Website')); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    while($row = array_shift($res)){
                                        $c[$row['website_id']] = $row['name'];
                                    }
                                    echo print_select_box($c,'website_id',$ticket['website_id']);
                                    ?>
								</td>
							</tr>
                            <?php } ?>
                            <?php } ?>
                            <?php if((int)$ticket_id>0){ ?>
							<tr>
								<th>
									<?php _e('Public link');?>
								</th>
								<td>
                                    <a href="<?php echo module_ticket::link_public($ticket_id);?>" target="_blank">click here</a>
								</td>
							</tr>
                            <?php } ?>
						</tbody>
					</table>

                        <?php
                        if($ticket['user_id']){
                            $other_tickets = module_ticket::get_tickets(array('user_id'=>$ticket['user_id']));
                            if(count($other_tickets)>1){
                                ?>
                                <h3><?php _e('%s Other Support Tickets',count($other_tickets)); ?></h3>
                                <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full tbl_fixed">
                                    <tbody>
                                        <?php foreach($other_tickets as $other_ticket){ ?>
                                        <tr>
                                            <td style="width:55px; <?php echo $other_ticket['ticket_id'] == $ticket_id ? ' font-weight:bold;':'';?>">
                                                <?php echo module_ticket::link_open($other_ticket['ticket_id'],true);?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($other_ticket['subject']);?>
                                            </td>
                                            <td style="width:100px;">
                                                <?php echo htmlspecialchars(module_ticket::$ticket_statuses[$other_ticket['status_id']]); ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <?php
                            }
                        } ?>

                    <?php handle_hook('ticket_sidebar',$ticket_id); ?>

                    <?php } // end can edit ?>
                    
                    <p align="center">
                    <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save details'); ?>" class="submit_button save_button" />
					<?php if((int)$ticket_id && module_ticket::can_i('delete','Tickets')){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_ticket::link_open(false); ?>';" class="submit_button" />
                    </p>
					<?php if((int)$ticket_id && module_ticket::can_edit_tickets()){ ?>
                        <p align="center">
                        <input type="submit" name="mark_as_unread" value="<?php echo _l('Mark as unread'); ?>" class="submit_button" />
                        </p>
                    <?php } ?>


				</td>
                <td valign="top">
                    <h3><?php echo _l('Ticket Messages'); ?></h3>
                    <div id="ticket_container" style="<?php echo module_config::c('ticket_scroll',0) ? ' max-height: 400px; overflow-y:auto;' : '';?>">
                                            <?php
                                            $reply__ine_default = '----- (Please reply above this line) -----'; // incase they change it
                                            $reply__ine =   module_config::s('ticket_reply_line',$reply__ine_default);
                                            $ticket_message_count = count($ticket_messages);
                                            $ticket_message_counter = 0;
                                            foreach($ticket_messages as $ticket_message){
                                                $ticket_message_counter++;
                                                $attachments = module_ticket::get_ticket_message_attachments($ticket_message['ticket_message_id']);
                                                ?>
                                                <div class="ticket_message ticket_message_<?php
                                                    echo isset($admins_rel[$ticket_message['from_user_id']]) ? 'admin' : 'creator';
                                                    //echo $ticket['user_id'] == $ticket_message['from_user_id'] ? 'creator' : 'admin';
                                                    //echo $ticket_message['message_type_id'] == _TICKET_MESSAGE_TYPE_CREATOR ? 'creator' : 'admin';

                                                    ?>">
                                                    <div class="ticket_message_title">
                                                        <div class="ticket_message_title_summary">
                                                            <strong><?php
                                                                if(module_security::get_loggedin_id() == $ticket_message['from_user_id']){
                                                                    // this message was from me !
                                                                    echo _l('Me:');
                                                                }else{
                                                                   // this message was from someone else.
                                                                    // eg, the Customer, or the Response from admin.
                                                                    //if($ticket['user_id'] == $ticket_message['from_user_id']){
                                                                    if(!isset($admins_rel[$ticket_message['from_user_id']])){
                                                                        echo _l('Customer:');
                                                                    }else{
                                                                        echo _l('Support:');
                                                                    }
                                                                }
                                                                ?></strong>
                                                            <?php echo print_date($ticket_message['message_time']); ?>
                                                            <a href="#" onclick="jQuery(this).parent().hide(); jQuery(this).parent().parent().find('.ticket_message_title_full').show(); return false;"><?php echo _l('more &raquo;');?></a>
                                                        </div>
                                                        <div class="ticket_message_title_full">

                                                            <span>
                                                                <?php _e('Date:');?> <strong>
                                              <?php echo print_date($ticket_message['message_time'],true); ?></strong>
                                                            </span>
                                                            <span>
                                                                <?php _e('From:');?> <strong><?php
                                                                $from_temp = module_user::get_user($ticket_message['from_user_id']);
                                                                echo htmlspecialchars($from_temp['name']);?> &lt;<?php echo htmlspecialchars($from_temp['email']);?>&gt;</strong>
                                                            </span>
                                                            <span>
                                                                <?php _e('To:');?>
                                                                <strong><?php
                                                                $to_temp = array();
                                                                if($ticket_message['to_user_id']){
                                                                    $to_temp = module_user::get_user($ticket_message['to_user_id']);
                                                                }else{
                                                                    $cache = @unserialize($ticket_message['cache']);
                                                                    if($cache && isset($cache['to_email'])){
                                                                        $to_temp['email'] = $cache['to_email'];
                                                                    }
                                                                }
                                                                if(isset($to_temp['name']))echo htmlspecialchars($to_temp['name']);
                                                                if(isset($to_temp['email'])){ ?>
                                                                    &lt;<?php echo htmlspecialchars($to_temp['email']); ?>&gt;
                                                                <?php } ?>
                                                                </strong><?php
                                                                ?>
                                                            </span>
                                                        </div>
                                                            <?php
                                                            if(count($attachments)){
                                                                echo '<span>';
                                                                _e('Attachments:', 'wpetss');
                                                                foreach($attachments as $attachment){
                                                                    ?>
                                                                    <a href="<?php echo module_ticket::link_open_attachment($ticket_id,$attachment['ticket_message_attachment_id']);?>" class="attachment_link"><?php echo htmlspecialchars($attachment['file_name']);?> (<?php echo frndlyfilesize(filesize('includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id']));?>)</a>
                                                                    <?php
                                                                }
                                                                echo '</span>';
                                                            }
                                                            ?>
                                                    </div>
                                                    <div class="ticket_message_text">
                                                        <?php

                                                        // copied to ticket.php in autoresponder:
                                                        // todo: move this out to a function in ticket.php
                                                        // todo: put the utf8_encode / decode as an option for people who have troubles with it.
                                                        /*if(preg_match('#<br[^>]*>#i',$ticket_message['content'])){
                                                            $ticket_message['content'] = preg_replace("#\r?\n#",'',$ticket_message['content']);
                                                        }*/
                                                        $ticket_message['content'] = preg_replace("#<br[^>]*>#i",'',$ticket_message['content']);
                                                        $ticket_message['content'] = preg_replace('#(\r?\n\s*){2,}#',"\n\n",$ticket_message['content']);

                                                        switch(module_config::c('ticket_utf8_method',1)){
                                                            case 1:
                                                                $text = forum_text($ticket_message['content']);
                                                                break;
                                                            case 2:
                                                                $text = forum_text(utf8_encode($ticket_message['content']));
                                                                break;
                                                            case 3:
                                                                $text = forum_text(utf8_encode(utf8_decode($ticket_message['content'])));
                                                                break;
                                                        }
                                                        //$text = forum_text(utf8_encode($ticket_message['content']));
                                                        //$text = forum_text(utf8_encode(utf8_decode($ticket_message['content'])));
                                                        $lines = explode("\n",$text);
                                                        $do_we_hide = count($lines)>4 && module_config::c('ticket_hide_messages',1) && $ticket_message_counter<$ticket_message_count && $ticket_message_count!=2;

                                                        if($ticket_message['cache']=='autoreply'){
                                                            ?>
                                                            <a href="#" onclick="$('.autoreply_holder').show(); $(this).hide(); return false;"><?php _e('Show auto reply &raquo;');?></a>
                                                            <div style="display:none;" class="autoreply_holder">
                                                            <?php
                                                        }else if($do_we_hide){
                                                            ?>
                                                            <div class="ticket_message_hider">
                                                            <?php
                                                        }

                                                        //$blank_line_limit = module_config::c('ticket_message_max_blank_lines',1);
                                                        if(true){
                                                            $hide__ines = $print__ines = array();
                                                            $blank_line_count = 0;
                                                            foreach($lines as $line_number => $line){
                                                                // hide anything after
                                                                $line = trim($line);
                                                                //if(preg_replace('#[\r\n\s]*#','',$line)==='')$blank_line_count++;
                                                                //else $blank_line_count=0;

                                                                //if($blank_line_limit>0 && $blank_line_count>$blank_line_limit)continue;

                                                                if(
                                                                    count($hide__ines) ||
                                                                    preg_match('#^>#',$line) ||
                                                                    preg_match('#'.preg_quote($reply__ine,'#').'.*$#ims',$line) ||
                                                                    preg_match('#'.preg_quote($reply__ine_default,'#').'.*$#ims',$line)
                                                                ){
                                                                    if(!count($hide__ines)){
                                                                        // move the line before if it exists.
                                                                        if(isset($print__ines[$line_number-1])){
                                                                            if(trim(preg_replace('#<br[^>]*>#i','',$print__ines[$line_number-1]))){
                                                                                $hide__ines[$line_number-1] = $print__ines[$line_number-1];
                                                                            }
                                                                            unset($print__ines[$line_number-1]);
                                                                        }
                                                                        // move the line before if it exists.
                                                                        if(isset($print__ines[$line_number-2])){
                                                                            if(trim(preg_replace('#<br[^>]*>#i','',$print__ines[$line_number-2]))){
                                                                                $hide__ines[$line_number-2] = $print__ines[$line_number-2];
                                                                            }
                                                                            unset($print__ines[$line_number-2]);
                                                                        }
                                                                        // move the line before if it exists.
                                                                        if(isset($print__ines[$line_number-3]) && preg_match('#^On #',trim($print__ines[$line_number-3]))){
                                                                            if(trim(preg_replace('#<br[^>]*>#i','',$print__ines[$line_number-3]))){
                                                                                $hide__ines[$line_number-3] = $print__ines[$line_number-3];
                                                                            }
                                                                            unset($print__ines[$line_number-3]);
                                                                        }
                                                                    }
                                                                    $hide__ines [$line_number] = $line;
                                                                    unset($print__ines[$line_number]);
                                                                }else{
                                                                    // not hidden yet.
                                                                    $print__ines[$line_number] = $line;
                                                                }
                                                            }
                                                            ksort($hide__ines);
                                                            ksort($print__ines);
                                                            echo implode("\n",$print__ines);
                                                            //print_r($print__ines);
                                                            if(count($hide__ines)){
                                                                echo '<a href="#" onclick="jQuery(this).parent().find(\'div\').toggle(); return false;">'._l('- show quoted text -').'</a> ';
                                                                echo '<div style="display:none;">';
                                                                echo implode("\n",$hide__ines);
                                                                echo '</div>';
                                                                //print_r($hide__ines);
                                                            }
                                                        }else{
                                                            echo $text;
                                                        }
                                                        if($ticket_message['cache']=='autoreply'){
                                                            ?>
                                                            </div>
                                                            <?php
                                                        }else if($do_we_hide){
                                                            ?>
                                                            </div>
                                                            <div>
                                                                <span class="shower">
                                                                    <a href="#" onclick="jQuery(this).parent().parent().parent().find('.ticket_message_hider').addClass('ticket_message_hider_show'); jQuery(this).parent().parent().find('.hider').show(); jQuery(this).parent().hide();return false;"><?php _e('Show entire message &raquo;');?></a>
                                                                </span>
                                                                <span class="hider" style="display:none;">
                                                                    <a href="#" onclick="jQuery(this).parent().parent().parent().find('.ticket_message_hider').removeClass('ticket_message_hider_show'); jQuery(this).parent().parent().find('.shower').show(); jQuery(this).parent().hide(); return false;"><?php _e('&laquo; Hide message');?></a>
                                                                </span>
                                                            </div>
                                                            <?php
}
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>


                                            <?php
                                            if(true){ //$logged_in_user || is_user_logged_in()){
                                            ?>

                                            <?php if(false && count($ticket_messages)){ ?>
                                            <div id="ticket_reply_button">
                                                <input type="button" name="reply" onclick="jQuery('#ticket_reply_button').hide(); jQuery('#ticket_reply_holder').show(); jQuery('#new_ticket_message')[0].focus(); return false;" value="<?php echo _l('Reply to ticket');?>" class="submit_button">
                                            </div>
                                            <div style="display: none;" class="ticket_reply" id="ticket_reply_holder">
                                            <?php }else{ ?>
                                            <div id="ticket_reply_holder" class="ticket_reply">
                                                 <script type="text/javascript">
                                                     /*jQuery(function(){
                                                         jQuery('#new_ticket_message')[0].focus();
                                                     });*/
                                                 </script>
                                            <?php } ?>

                                                <div class="ticket_message ticket_message_<?php
                                                    echo $ticket['user_id'] == module_security::get_loggedin_id() ? 'creator' : 'admin';
                                                    ?>">
                                                    <div class="ticket_message_title" style="text-align: left;">
                                                        <?php if(module_ticket::can_edit_tickets()){ ?>
                                                        <div style="float:right; margin: -3px 5px 0 0;">
                                                            <div style="display:none;" id="canned_response">
                                                                <?php
                                                                $canned_responses = module_ticket::get_saved_responses();
                                                                echo print_select_box($canned_responses,'canned_response_id','','',true,'',true);
                                                                ?>
                                                                <input type="button" name="s" id="save_saved" value="<?php _e('Save');?>">
                                                                <input type="button" name="i" id="insert_saved" value="<?php _e('Insert');?>">
                                                                <script type="text/javascript">
                                                                    $(function(){
                                                                        $('#save_saved').click(function(){
                                                                            $.ajax({
                                                                                url: '<?php echo module_ticket::link_open($ticket_id,false);?>',
                                                                                type: 'POST',
                                                                                data: '_process=save_saved_response&saved_response_id='+$('#canned_response_id').val()+'&value='+escape($('#new_ticket_message').val()),
                                                                                dataType: 'json',
                                                                                success: function(r){
                                                                                    alert('<?php _e('Saved successfully');?>');
                                                                                }
                                                                            });
                                                                        });
                                                                        $('#insert_saved').click(function(){
                                                                            $.ajax({
                                                                                url: '<?php echo module_ticket::link_open($ticket_id,false);?>',
                                                                                data: '_process=insert_saved_response&saved_response_id='+$('#canned_response_id').val(),
                                                                                dataType: 'json',
                                                                                success: function(r){
                                                                                    $('#new_ticket_message').val(
                                                                                            $('#new_ticket_message').val() + r.value
                                                                                    );
                                                                                }
                                                                            });
                                                                        });
                                                                    });
                                                                </script>
                                                            </div>
                                                            <a href="#" onclick="$('#canned_response').show(); $(this).hide(); return false;"><?php _e('Saved Response');?></a>
                                                        </div>
                                                        <?php } ?>
                                                        <strong><?php echo _l('Enter Your Message:');?></strong>
                                                    </div>
                                                    <div class="ticket_message_text">

                                                        <textarea rows="6" cols="20" name="new_ticket_message" id="new_ticket_message"></textarea>
                                                        <table align="center">
                                                            <tbody>

                                                            <?php if(module_config::c('ticket_allow_attachment',1)){ ?>
                                                            <tr>
                                                                <td align="right">
                                                                    <?php _e('Add Attachment'); ?>
                                                                </td>
                                                                <td align="left">
                                                                    <input type="file" name="attachment[]">
                                                                </td>
                                                            </tr>
                                                            <?php } ?>

                                                            <?php if(module_ticket::can_edit_tickets()){ ?>

                                                            <tr>
                                                                <td align="right">
                                                                    <?php _e('Change status:'); ?>
                                                                </td>
                                                                <td align="left">
                                                                    <?php
                                                                    $current_status = $ticket['status_id'];
                                                                    if(count($ticket_messages)){
                                                                        if($current_status <= 2){
                                                                            //$current_status = 3; // change to replied
                                                                            $current_status = 6; // resolved
                                                                        }else{
                                                                            //$current_status = 5; // change to in progress
                                                                            $current_status = 6; // resolved
                                                                        }
                                                                    }
                                                                    echo print_select_box(module_ticket::get_statuses(),'change_status_id',$current_status);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php if($ticket['assigned_user_id'] != module_security::get_loggedin_id()){ ?>
                                                            <tr>
                                                                <td align="right">
                                                                    <?php _e('Change user:'); ?>
                                                                </td>
                                                                <td align="left"> 
                                                                    <?php
                                                                    echo print_select_box($admins_rel,'change_assigned_user_id',$ticket['assigned_user_id']);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php } ?>

                                                        <?php } ?>

                                                            <?php /* <tr>
                                                                <td align="right">
                                                                     <?php _e('Send message as:');?>
                                                                </td>
                                                                <td align="left">
                                                                    <input type="hidden" name="creator_id" value="<?php echo module_security::get_loggedin_id();?>">
                                                                    <input type="hidden" name="creator_hash" value="<?php echo module_ticket::creator_hash(module_security::get_loggedin_id());?>">
                                                                    <strong>
                                                                    <?php echo htmlspecialchars($send_as_name);?>
                                                                    &lt;<?php echo htmlspecialchars($send_as_address);?>&gt;
                                                                    </strong>
                                                                    <?php _e('Reply To:');?> <strong><?php echo htmlspecialchars($to_user_a['email']);?></strong> 
                                                                </td>
                                                            </tr> */ ?>
                                                            
                                                            </tbody>
                                                        </table>

                                                        <?php if($next_ticket){ ?>
                                                            <input type="submit" name="newmsg" value="<?php _e('Submit Message');?>" class="submit_button">
                                                            <input type="submit" name="newmsg_next" value="<?php _e('Submit Message &amp; Go To Next Ticket');?>" class="submit_button save_button">
                                                            <input type="hidden" name="next_ticket_id" value="<?php echo $next_ticket;?>">
                                                        <?php }else{ ?>
                                                            <input type="submit" name="newmsg" value="<?php _e('Submit Message');?>" class="submit_button save_button">
                                                        <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    
                </td>
			</tr>
			<tr>
				<td align="center" colspan="2">

				</td>
			</tr>
		</tbody>
	</table>


</form>

