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

$unread_ticket_count = module_ticket::get_unread_ticket_count();
if($unread_ticket_count>0){
    $module->page_title = _l('Tickets (%s unread)',$unread_ticket_count);
}else{
    $module->page_title = _l('TIckets');
}

// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'ticket_id',
                'owner_table' => 'ticket',
            ),
        )
    );
}

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']>0){
    $search['customer_id'] = (int)$_REQUEST['customer_id'];
}else{
    $search['customer_id'] = false;
}


$search_statuses = module_ticket::get_statuses();
$search_statuses['2,3,5'] = 'New/Replied/In Progress';
if(!isset($search['status_id']) && module_ticket::can_edit_tickets()){
    $search['status_id'] = '2,3,5';
}

$tickets = module_ticket::get_tickets($search);
if(!isset($_REQUEST['nonext'])){
    $_SESSION['_ticket_nextprev'] = array();
    foreach($tickets as $ticket){
        $_SESSION['_ticket_nextprev'][] = $ticket['ticket_id'];
    }
}



?>

<h2>
    <?php if(module_ticket::can_i('create','Tickets')){ ?>
	<span class="button">
		<?php echo create_link("Add New ticket","add",module_ticket::link_open('new')); ?>
	</span>
    <?php } ?>
	<?php echo _l('Customer Tickets'); ?>
</h2>

<form action="" method="GET">

    <input type="hidden" name="customer_id" value="<?php echo isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : '';?>">


<table class="search_bar" width="100%">
	<tr>
		<td>
			<?php echo _l('Number:');?>
		</td>
		<td>
			<input type="text" name="search[ticket_id]" value="<?php echo isset($search['ticket_id'])?htmlspecialchars($search['ticket_id']):''; ?>" size="5">
		</td>
		<td width="40">
			<?php echo _l('Subject:');?>
		</td>
		<td>
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="10">
		</td>
		<td width="20">
			<?php echo _l('Date:');?>
		</td>
		<td>
			<input type="text" name="search[date_from]" value="<?php echo isset($search['date_from'])?htmlspecialchars($search['date_from']):''; ?>" class="date_field">
            <?php _e('to');?>
			<input type="text" name="search[date_to]" value="<?php echo isset($search['date_to'])?htmlspecialchars($search['date_to']):''; ?>" class="date_field">
		</td>
		<td width="30">
			<?php echo _l('Type:');?>
		</td>
		<td>
			<?php echo print_select_box(module_ticket::get_types(),'search[type]',isset($search['type'])?$search['type']:''); ?>
		</td>
		<td width="30">
			<?php echo _l('Status:');?>
		</td>
		<td>
			<?php echo print_select_box($search_statuses,'search[status_id]',isset($search['status_id'])?$search['status_id']:''); ?>
		</td>
		 <td width="30">
			<?php echo _l('Contact:');?>
		</td>
		<td>
			<input type="text" name="search[contact]" value="<?php echo isset($search['contact'])?htmlspecialchars($search['contact']):''; ?>" size="10">
		</td>
		<td align="right">
			<?php echo create_link("Reset","reset",module_ticket::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($tickets,70);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>
    <?php echo $pagination['links'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tbl_fixed">
	<thead>
	<tr class="title">
		<th style="width:8%;"><?php echo _l('Number'); ?></th>
		<th style="width:30%"><?php echo _l('Subject'); ?></th>
		<th style="width:16%;"><?php echo _l('Date/Time'); ?></th>
		<th style="width:12%;"><?php echo _l('Type'); ?></th>
		<th style="width:9%;"><?php echo _l('Status'); ?></th>
		<!-- <th><?php echo _l(module_config::c('project_name_single','Website')); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th><?php echo _l('Customer'); ?></th>
        <?php } ?>-->
		<th style="width:10%;"><?php echo _l('Staff'); ?></th>
		<th style="width:18%;"><?php echo _l('Contact'); ?></th>
        <?php if(class_exists('module_envato',false)){ ?>
        <th style="width:10%;"><?php echo _l('Envato'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1) && module_group::groups_enabled()){ ?>
        <th width="9%"><?php echo _l('Group'); ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;

		foreach($pagination['rows'] as $ticket){
            //$ticket = module_ticket::get_ticket($ticket['ticket_id']);
            ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action" nowrap="">
                    <?php echo module_ticket::link_open($ticket['ticket_id'],true,$ticket);?> (<?php echo $ticket['message_count'];?>)
                </td>
                <td>
                    <?php
                    // todo, pass off to envato module as a hook
                    $ticket['subject'] = preg_replace('#Message sent via your Den#','',$ticket['subject']);
                    if($ticket['unread']){
                        echo '<strong>';
                        echo ' '._l('* '). ' ';
                        echo htmlspecialchars($ticket['subject']);
                        echo '</strong>';
                    }else{
                        echo htmlspecialchars($ticket['subject']);
                    }
                    ?>
                </td>
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
                <td>
                    <?php echo htmlspecialchars($ticket['type']); ?>
                </td>
                <td>
                    <?php echo htmlspecialchars(module_ticket::$ticket_statuses[$ticket['status_id']]); ?>
                </td>
                <!-- <td>
                    <?php echo module_website::link_open($ticket['website_id'],true); ?>
                </td>
                <?php if(!isset($_REQUEST['customer_id'])){ ?>
                <td>
                    <?php echo module_customer::link_open($ticket['customer_id'],true);?>
                </td>
                <?php } ?>-->
                <td>
                    <?php echo module_user::link_open($ticket['assigned_user_id'],true); ?>
                </td>
                <td>
                    <?php echo module_user::link_open($ticket['user_id'],true); ?>
                </td>
                <?php if(class_exists('module_envato',false)){ ?>
                <td>
                    <?php
                    // find out details about this envato contact
                    // their username and what items they have purchased.
                    $items = module_envato::get_items_by_ticket($ticket['ticket_id']);
                    foreach($items as $item){
                        echo '<a href="'.$item['url'].'">'.htmlspecialchars($item['name']).'</a> ';
                    }
                    ?>
                </td>
                <?php } ?>
                <?php if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1) && module_group::groups_enabled()){ ?>
                    <td><?php
                    // find the groups for this customer.
                    $groups = module_group::get_groups_search(array(
                                                                  'owner_table' => 'ticket',
                                                                  'owner_id' => $ticket['ticket_id'],
                                                              ));
                    $g=array();
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                ?></td>
                <?php } ?>
            </tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>