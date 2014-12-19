<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:23 
  * IP Address: 127.0.0.1
  */ _e('Ticket :');?> <?php echo module_ticket::ticket_number($ticket_id);?></title>

<link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/styles.css?ver=5" type="text/css" />
<link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/desktop.css?ver=5" type="text/css" />
<link type="text/css" href="<?php echo _BASE_HREF;?>css/smoothness/jquery-ui-1.8.2.custom.css" rel="stylesheet" />
<?php module_config::print_css();?>


<script language="javascript" type="text/javascript">
var ajax_search_ini = '<?php echo _l('Quick Search:'); ?>';
var ajax_search_xhr = false;
<?php
switch(strtolower(module_config::s('date_format','d/m/Y'))){
    case 'd/m/y':
        $js_cal_format = 'dd/mm/yy';
        break;
    case 'y/m/d':
        $js_cal_format = 'yy/mm/dd';
        break;
    case 'm/d/y':
        $js_cal_format = 'mm/dd/yy';
        break;
    default:
        $js_cal_format = 'yy-mm-dd';
}
?>
var js_cal_format = '<?php echo $js_cal_format;?>';
</script>
<script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-ui-1.8.6.custom.min.js"></script>
<script type="text/javascript" src="<?php echo _BASE_HREF;?>js/timepicker.js"></script>
<script type="text/javascript" src="<?php echo _BASE_HREF;?>js/cookie.js"></script>
<script type="text/javascript" src="<?php echo _BASE_HREF;?>js/javascript.js?ver=2"></script>
<?php module_config::print_js();?>

<script type="text/javascript">
$(function(){
	init_interface();
});
</script>
</head>
<body bgcolor="#FFF">


<div id="holder" style="background: #FFF">
<div id="page_middle_flex">

<div class="content">
<div id="ticket_new_holder_public">


<form action="" method="post" id="ticket_form" enctype="multipart/form-data">
	<input type="hidden" name="_process" value="save_public_ticket" />
	<input type="hidden" name="tac" value="<?php echo $ticket_account['ticket_account_id'];?>" />

    <?php
    module_form::set_required(array(
                                  'fields' => array(
                                      'name' => _l('Your Name'),
                                      'email' => _l('Your Email'),
                                      'type' => _l('Department'),
                                      'subject' => _l('Subject'),
                                      'new_ticket_message' => _l('Your Message'),
                                  ),
                                  'emails' => array(
                                      'email' => _l('Your Email'),
                                  ),
                              ));
    ?>

	<table cellpadding="10" class="wpetss" width="100%">
		<tbody>
        <tr>
            <td class="public_header">
                <?php echo module_config::c('ticket_public_header','Submit a support ticket'); ?>
            </td>
        </tr>
        <tr>
            <td class="public_welcome">
                <?php echo module_config::c('ticket_public_welcome','Hello, here you can request a support ticket. <br/> Simply fill in the fields below and press "Submit Ticket".'); ?>
            </td>
        </tr>
			<tr>
				<td valign="top" style="padding:10px;">
					<h3><?php echo _l('Your Details'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Your Name'); ?>
								</th>
								<td>
									<input type="text" name="name" value="" style="width:90%">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Your Email'); ?>
								</th>
								<td>
									<input type="text" name="email" value="" style="width:90%">
								</td>
							</tr>
						</tbody>
					</table>


                    <?php handle_hook('ticket_create',$ticket_id); ?>


					<h3><?php echo _l('Ticket Details'); ?></h3>

					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<!--<tr>
								<th>
									<?php /*echo _l('Department'); */?>
                                    <input type="hidden" name="dtbakerss" id="dtbakerss" value="true">
								</th>
								<td>
									<?php
/*                                    $types = module_ticket::get_types();
                                    echo print_select_box($types,'type',$ticket_account['default_type']);
                                    */?>
								</td>
							</tr>-->
                            <tr>
                                <th class="width1">
                                    <?php _e('Subject');?>
                                </th>
                                <td>
                                    <input type="text" name="subject" id="subject" value="" style="width:90%" />
                                </td>
                            </tr>
                            <?php if(module_config::c('ticket_allow_attachment',1)){ ?>
                            <tr>
                                <th>
                                    <?php _e('Attachment');?>
                                </th>
                                <td>
                                    <input type="file" name="attachment[]">
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
								<th>
									<?php echo _l('Your Message'); ?>
								</th>
								<td>
									<textarea rows="10" cols="20" name="new_ticket_message" style="width:90%"></textarea>
                                </td>
                            </tr>
                            <?php if(module_config::c('ticket_show_position',1)){ ?>
                            <tr>
								<th class="width1">
									<?php echo _l('Position'); ?>
								</th>
								<td>
									<?php //echo sprintf(_l('%s out of %s tickets'),ordinal($ticket['position']),$ticket['total_pending']); ?>
									<?php echo ordinal($ticket['position']); ?> (this is how many tickets are infront of you)
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
								<th class="width1">
									<?php echo _l('Reply'); ?>
								</th>
								<td>
									<?php echo _l('We will reply between %s and %s days',module_config::c('ticket_turn_around_days_min',2),module_config::c('ticket_turn_around_days',5)); ?>
                                </td>
                            </tr>
						</tbody>
					</table>

                    <p align="center">
                        <em><small><?php echo _l('* required fields');?> </small></em> <br/>
                    <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Submit Ticket'); ?>" class="submit_button save_button" />
                    </p>
				</td>
			</tr>
		</tbody>
	</table>


</form>
</div>
    