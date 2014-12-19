<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:03 
  * IP Address: 127.0.0.1
  */

if(!$note_edit_safe)die('failed');


?>

<input type="hidden" name="rel_data" id="form_rel_data" value="<?php echo $note['rel_data'];?>">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass">
	<tbody>
		<tr>
			<th>
				<?php echo _l('Date'); ?>
			</th>
			<td>
				<input type="text" name="note_time" id="form_note_time" class="date_time_field" value="<?php echo print_date($note['note_time']); ?>" />
			</td>
		</tr>
        <?php if (module_config::c('allow_note_reminders',1)){ ?>
		<tr>
			<th>
				<?php echo _l('Reminder'); ?>
			</th>
			<td>
				<input type="checkbox" name="reminder" id="form_reminder" value="1" <?php echo $note['reminder'] ? ' checked':''; ?> />
                <?php _e('for'); ?>
				<?php
                // we use the same staff listing we have in jobs.
                $staff_members = module_user::get_staff_members();
                $staff_member_rel = array();
                foreach($staff_members as $staff_member){
                    $staff_member_rel[$staff_member['user_id']] = $staff_member['name'];
                }
                echo print_select_box($staff_member_rel,'user_id',$note['user_id'],'form_user_id',_l('Everyone'));
                ?>
                <?php _h('Sets a dashboard reminder for the above date. This will appear on the selected users dashboard.<br><br>Untick to remove reminder.');?>
			</td>
		</tr>
        <?php } ?>
		<tr>
			<th>
				<?php echo _l('Note'); ?>
			</th>
			<td>
				 <textarea rows="5" cols="40" name="note" id="form_note_data"><?php echo htmlspecialchars($note['note']);?></textarea>
			</td>
		</tr>
		<?php if($note_id && $note_id!='new'){ ?>
		<tr>
			<th>
				<?php echo _l('Creator'); ?>
			</th>
			<td>
				<?php $user_data = module_user::get_user($note['create_user_id']);
				echo $user_data['name'];
				echo ' on ';
				echo print_date($note['date_created'],true);
				?>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo _l('Updated'); ?>
			</th>
			<td>
				<?php
				if($note['update_user_id']){
					$user_data = module_user::get_user($note['update_user_id']);
					echo $user_data['name'];
					echo ' on ';
					echo print_date($note['date_updated'],true);
				}else{
					echo 'never';
				}
				?>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

