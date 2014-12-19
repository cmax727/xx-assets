
            <table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:23 
  * IP Address: 127.0.0.1
  */ echo _l('Ticket Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Ticket Number'); ?>
								</th>
								<td>
									<?php echo module_ticket::ticket_number($ticket['ticket_id']);?>
								</td>
							</tr>
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
                                    echo isset($admins_rel[$ticket['assigned_user_id']]) ? $admins_rel[$ticket['assigned_user_id']] : 'N/A';
                                    ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Type/Department'); ?>
								</th>
								<td>
									<?php echo h($ticket['type']); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php
                                    //echo print_select_box(module_ticket::get_statuses(),'status_id',$ticket['status_id'],'',true,false,true);
                                    $s = module_ticket::get_statuses(); echo $s[$ticket['status_id']];
                                    if($ticket['status_id'] == 2 || $ticket['status_id'] == 3 || $ticket['status_id'] == 5){
                                        if(module_config::c('ticket_show_position',1)){
                                            echo ' ';
                                            echo _l('(%s out of %s tickets)',ordinal($ticket['position']),module_ticket::ticket_count('pending'));
                                        }
                                    }
                                    ?>
								</td>
							</tr>
						</tbody>
					</table>

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
                                    //echo print_select_box($c,'customer_id',$ticket['customer_id']);
                                    echo isset($c[$ticket['customer_id']]) ? $c[$ticket['customer_id']] : 'N/A';
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
                                    //echo print_select_box($c,'user_id',$ticket['user_id']);
                                    echo isset($c[$ticket['user_id']]) ? $c[$ticket['user_id']] : 'N/A';
                                    ?>
								</td>
							</tr>
                                <?php if($ticket['website_id']){ ?>
							<tr>
								<th>
									<?php echo _l(''.module_config::c('project_name_single','Website')); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_website::get_websites(array('customer_id'=>$ticket['customer_id']));
                                    while($row = array_shift($res)){
                                        $c[$row['website_id']] = $row['name'];
                                    }
                                    //echo print_select_box($c,'website_id',$ticket['website_id']);
                                    echo isset($c[$ticket['website_id']]) ? $c[$ticket['website_id']] : 'N/A';
                                    ?>
								</td>
							</tr>
                            <?php } ?>
                            <?php } ?>
						</tbody>
					</table>
                    
                    <?php handle_hook('ticket_sidebar',$ticket_id); ?>

				</td>
                <td valign="top">
                    <h3><?php echo _l('Ticket Messages'); ?></h3>

<div id="ticket_container" style="<?php echo module_config::c('ticket_scroll',0) ? ' max-height: 400px; overflow-y:auto;' : '';?>">
                                            <?php
                                            $ticket_messages = module_ticket::get_ticket_messages($ticket_id);
                                            $reply__ine_default = '----- (Please reply above this line) -----'; // incase they change it
                                            $reply__ine =   module_config::s('ticket_reply_line',$reply__ine_default);
                                            foreach($ticket_messages as $ticket_message){
                                                $attachments = module_ticket::get_ticket_message_attachments($ticket_message['ticket_message_id']);
                                                ?>
                                                <div class="ticket_message ticket_message_<?php
                                                    //echo $ticket['user_id'] == $ticket_message['from_user_id'] ? 'creator' : 'admin';

                                                    echo !isset($admins_rel[$ticket_message['from_user_id']]) ? 'creator' : 'admin';
                                                    ?>">
                                                    <div class="ticket_message_title">
                                                        <div class="ticket_message_title_summary">
                                                            <strong><?php
                                                                if(isset($logged_in_user) && $logged_in_user == $ticket_message['from_user_id']){
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
                                                                    <a href="<?php echo module_ticket::link_open_attachment($ticket_id,$attachment['ticket_message_attachment_id']);?>"><?php echo htmlspecialchars($attachment['file_name']);?> (<?php echo frndlyfilesize(filesize('includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id']));?>)</a>
                                                                    <?php
                                                                }
                                                                echo '</span>';
                                                            }
                                                            ?>
                                                    </div>
                                                    <div class="ticket_message_text">
                                                        <?php //echo nl2br(htmlspecialchars($ticket_message['content'])); ?>
                                                        <?php
                                                        $ticket_message['content'] = preg_replace("#<br[^>]*>#",'',$ticket_message['content']);
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
                                                        if(true){
                                                            $lines = explode("\n",$text);
                                                            $hide__ines = $print__ines = array();
                                                            foreach($lines as $line_number => $line){
                                                                // hide anything after
                                                                $line = trim($line);
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
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>


                                            <?php
                                            if(true){ //$logged_in_user || is_user_logged_in()){
                                            ?>

                                            <?php if(count($ticket_messages)){ ?>
                                            <div id="ticket_reply_button">
                                                <input type="button" name="reply" onclick="jQuery('#ticket_reply_button').hide(); jQuery('#ticket_reply_holder').show(); jQuery('#new_ticket_message')[0].focus(); return false;" value="<?php echo _l('Reply to ticket');?>" class="submit_button">
                                            </div>
                                            <div style="display: none;" class="ticket_reply" id="ticket_reply_holder">
                                            <?php }else{ ?>
                                            <div id="ticket_reply_holder" class="ticket_reply">
                                            <?php } ?>

                                                <div class="ticket_message ticket_message_<?php
                                                    //echo $ticket['user_id'] == module_security::get_loggedin_id() ? 'creator' : 'admin';
                                                    echo isset($admins_rel[module_security::get_loggedin_id()]) ? 'admin' : 'creatorf';
                                                    ?>">
                                                    <div class="ticket_message_title" style="text-align: left;">
                                                        <strong><?php echo _l('Enter Your Message:');?></strong>
                                                    </div>
                                                    <div class="ticket_message_text">

                                                        <form action="" method="post" enctype="multipart/form-data">
                                                            <input type="hidden" name="_process" value="send_public_ticket">

                                                        <textarea rows="6" cols="20" name="new_ticket_message" id="new_ticket_message"></textarea>
                                                        <?php if(module_config::c('ticket_allow_attachment',1)){ ?>
                                                        <div style="line-height: 25px; padding:10px;">
                                                            <?php _e('Attachment'); ?>
                                                            <input type="file" name="attachment[]">
                                                        </div>
                                                        <?php } ?>
                                                        <div style="line-height: 25px; padding:10px;">
                                                            <input type="hidden" name="creator_id" value="<?php echo module_security::get_loggedin_id();?>">
                                                                    <input type="hidden" name="creator_hash" value="<?php echo module_ticket::creator_hash(module_security::get_loggedin_id());?>">
                                                                <?php _e('Send message as:');?>
                                                                <strong>
                                                                    <?php echo htmlspecialchars($send_as_name);?>
                                                                    &lt;<?php echo htmlspecialchars($send_as_address);?>&gt;
                                                                </strong>
                                                                <!-- <?php _e('Reply To:');?> <strong><?php echo htmlspecialchars($to_user_a['email']);?></strong> -->
                                                            <br/>
                                                            <input type="submit" name="newmsg" value="<?php _e('Send Reply Message');?>" class="submit_button save_button">
                                                        </div>

                                                        </form>
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

