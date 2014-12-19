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


define('_TICKET_ACCESS_ALL','All support tickets');
define('_TICKET_ACCESS_ASSIGNED','Only assigned tickets');
define('_TICKET_ACCESS_CREATED','Only tickets I created');

define('_TICKET_MESSAGE_TYPE_CREATOR',1);
define('_TICKET_MESSAGE_TYPE_ADMIN',0);

class module_ticket extends module_base{

	public $links;
	public $ticket_types;

    public $version = 2.16;

    public static $ticket_statuses = array();
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->ticket_types = array();
		$this->module_name = "ticket";
		$this->module_position = 20;

        self::$ticket_statuses = array(
            1 => _l('Unassigned'),
            2 => _l('New'),
            3 => _l('Replied'),
            5 => _l('In Progress'),
            6 => _l('Resolved'),
            7 => _l('Canceled'),
        );


        $this->ajax_search_keys = array(
            _DB_PREFIX.'ticket' => array(
                'plugin' => 'ticket',
                'search_fields' => array(
                    'ticket_id',
                    'subject',
                ),
                'key' => 'ticket_id',
                'title' => _l('Ticket: '),
            ),
        );

        module_config::register_css('ticket','tickets.css');
        module_config::register_js('ticket','tickets.js');


        module_template::init_template('ticket_container', '<span style="font-size:10px; color:#666666;">{REPLY_LINE}</span>
<span style="font-size:10px; color:#666666;">Your ticket has been updated, please see the message below:</span>


{MESSAGE}


<span style="font-size:10px; color:#666666;">Ticket Number: <strong>{TICKET_NUMBER}</strong></span>
<span style="font-size:10px; color:#666666;">Ticket Status: <strong>{TICKET_STATUS}</strong></span>
<span style="font-size:10px; color:#666666;">Your position in the support queue: <strong>{POSITION_CURRENT} out of {POSITION_ALL}</strong>.</span>
<span style="font-size:10px; color:#666666;">Estimated time for a reply: <strong>within {DAYS} days</strong></span>
<span style="font-size:10px; color:#666666;">You can view the status of your support query at any time by following this link:</span>
<span style="font-size:10px; color:#666666;"><a href="{URL}" style="color:#666666;">View Ticket {TICKET_NUMBER} History Online</a></span>

','The email sent along with all ticket replies.','text');

        module_template::init_template('ticket_admin_email','{MESSAGE}


<span style="font-size:12px; color:#666666; font-weight: bold;">Ticket Details:</span>
<span style="font-size:10px; color:#666666;">Number of messages: <strong>{MESSAGE_COUNT}</strong></span>
<span style="font-size:10px; color:#666666;">Ticket Number: <strong>{TICKET_NUMBER}</strong></span>
<span style="font-size:10px; color:#666666;">Ticket Status: <strong>{TICKET_STATUS}</strong></span>
<span style="font-size:10px; color:#666666;">Position in the support queue: <strong>{POSITION_CURRENT} out of {POSITION_ALL}</strong>.</span>
<span style="font-size:10px; color:#666666;">Estimated time for a reply: <strong>within {DAYS} days</strong></span>
<span style="font-size:10px; color:#666666;">View the ticket: <strong>{URL_ADMIN}</strong></span>
        ','Sent as an email to the administrator when a new ticket is created.','text');

        module_template::init_template('ticket_autoreply','Hello,

Thank you for your email. We will reply shortly.

        ','Sent as an email after a support ticket is received.','code');



	}

    public function pre_menu(){


        if($this->is_installed() && $this->can_i('view','Tickets')){

            /* module_security::has_feature_access(array(
                    'name' => 'Settings',
                    'module' => 'config',
                    'category' => 'Config',
                    'view' => 1,
                    'description' => 'view',
            ))*/
            if($this->can_i('edit','Ticket Settings')){
                $this->links['ticket_settings'] = array(
                    "name"=>"Ticket",
                    "p"=>"ticket_settings",
                    'args'=>array('ticket_account_id'=>false,'ticket_id'=>false),
                    "icon"=>"icon.png",
                    'holder_module' => 'config', // which parent module this link will sit under.
                    'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }

            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                $link_name = _l('Tickets');
                if(module_config::c('ticket_show_summary',1) && self::can_edit_tickets()){
                    // how many tickets?
                    // cache results for 30 seconds.
                    $ticket_count = module_cache::time_get('ticket_customer_count');
                    if($ticket_count===false){
                        $tickets = $this->get_tickets(array('customer_id'=>$_REQUEST['customer_id'],'status_id'=>-1));
                        $ticket_count = count($tickets);
                        module_cache::time_save('ticket_customer_count',$ticket_count);
                    }
                    if($ticket_count>0){
                        $link_name .= " <span class='menu_label'>".$ticket_count."</span> ";
                    }
                }

                $this->links['ticket_customer'] = array(
                    "name"=>$link_name,
                    "p"=>"ticket_admin",
                    'args'=>array('ticket_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }


            $link_name = _l('Tickets');
            if(module_config::c('ticket_show_summary',1) && self::can_edit_tickets()){
                $ticket_count = self::get_unread_ticket_count();
                if($ticket_count > 0){
                    $link_name .= " <span class='menu_label'>".$ticket_count."</span> ";
                }
            }
            $this->links['ticket_main'] = array(
                "name"=>$link_name,
                "p"=>"ticket_admin",
                'args'=>array('ticket_id'=>false),
            );
        }
    }

    public static function can_edit_tickets(){
        return self::can_i('edit','Tickets');
    }
    public static function creator_hash($creator_id){
        return md5('secret key! '._UCM_FOLDER.$creator_id);
    }
	public function handle_hook($hook,&$calling_module=false){
		switch($hook){
			case "home_alerts":
				$alerts = array();
                if(module_ticket::can_edit_tickets()){
                    if(module_config::c('ticket_alerts',1)){
                        // find any tickets that are past the due date and dont have a finished date.
                        $sql = "SELECT * FROM `"._DB_PREFIX."ticket` p ";
                        $sql .= " WHERE p.status_id <= 2 AND p.date_updated <= '".date('Y-m-d',strtotime('-'.module_config::c('ticket_turn_around_days',5).' days'))."'";
                        $tickets = qa($sql);
                        foreach($tickets as $ticket){
                            $alert_res = process_alert($ticket['date_updated'], _l('Ticket Not Completed'), module_config::c('ticket_turn_around_days',5));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($ticket['ticket_id']);
                                $alert_res['name'] = $ticket['subject'];
                                $alerts[] = $alert_res;
                            }
                        }
                    }
				}
				return $alerts;
				break;
        }
    }

    public static function link_generate($ticket_id=false,$options=array(),$link_options=array()){

        $key = 'ticket_id';
        if($ticket_id === false && $link_options){
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
            if(!${$key} && isset($_REQUEST[$key])){
                ${$key} = $_REQUEST[$key];
            }
        }
        $bubble_to_module = false;
        if(!isset($options['type']))$options['type']='ticket';
        if(!isset($options['page']))$options['page']='ticket_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['ticket_id'] = $ticket_id;
        $options['module'] = 'ticket';
        // what text should we display in this link?
        if($options['page']=='ticket_settings'){
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_ticket_account($ticket_id);
                $options['data'] = $data;
            }
            $options['text'] = $options['data']['name'];
        }else{
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_ticket($ticket_id);
                $options['data'] = $data;
            }
            $options['text'] = $ticket_id ? self::ticket_number($ticket_id) : 'N/A';
        }
        array_unshift($link_options,$options);
        if(self::can_i('edit','Ticket Settings')){

            if($options['page']=='ticket_settings'){
                $bubble_to_module = array(
                    'module' => 'config',
                    'argument' => 'ticket_account_id',
                );
            }else if($options['data']['customer_id']>0){

                if(!module_security::has_feature_access(array(
                    'name' => 'Customers',
                    'module' => 'customer',
                    'category' => 'Customer',
                    'view' => 1,
                    'description' => 'view',
                ))){
                    /*if(!isset($options['full']) || !$options['full']){
                        return '#';
                    }else{
                        return isset($options['text']) ? $options['text'] : 'N/A';
                    }*/
                }else{
                    $bubble_to_module = array(
                        'module' => 'customer',
                        'argument' => 'customer_id',
                    );
                }
            }
        }
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);

        }
    }

	public static function link_open($ticket_id,$full=false,$ticket_data=array()){
        return self::link_generate($ticket_id,array('full'=>$full,'data'=>$ticket_data));
    }
	public static function link_open_account($ticket_account_id,$full=false){
        return self::link_generate($ticket_account_id,array('page'=>'ticket_settings','full'=>$full,'arguments'=>array('ticket_account_id'=>$ticket_account_id)));
    }


    public static function link_public($ticket_id,$h=false){
        if($h){
            return md5('s3cret7hash for tickets '._UCM_FOLDER.' '.$ticket_id);
        }
        return full_link(_EXTERNAL_TUNNEL.'?m=ticket&h=public&i='.$ticket_id.'&hash='.self::link_public($ticket_id,true));
        /*
        // return an auto login link for the end user.
        $ticket_data = self::get_ticket($ticket_id);
        if($ticket_data['user_id']){
            $auto_login_link = 'auto_login='.module_security::get_auto_login_string($ticket_data['user_id']);
        }else{
            $auto_login_link = '';
        }
        $link_options = array();
        $options['page'] = 'ticket_admin';
        $options['arguments'] = array();
        $options['arguments']['ticket_id'] = $ticket_id;
        $options['module'] = 'ticket';
        $options['data'] = $ticket_data;
        array_unshift($link_options,$options);
        $link = link_generate($link_options);
        $link .= strpos($link,'?') === false ? '?' : '&';
        $link .= $auto_login_link;
        return $link;
        */
    }
    public static function link_open_attachment($ticket_id,$ticket_message_attachment_id,$h=false){
        if($h){
            return md5('s3cret7hash for ticket attacments '._UCM_FOLDER.' '.$ticket_id.'-'.$ticket_message_attachment_id);
        }
        return full_link(_EXTERNAL_TUNNEL.'?m=ticket&h=attachment&t='.$ticket_id.'&tma='.$ticket_message_attachment_id.'&hash='.self::link_open_attachment($ticket_id,$ticket_message_attachment_id,true));
    }
    public static function link_public_new(){
        return full_link(_EXTERNAL_TUNNEL.'?m=ticket&h=public_new');
    }

    public function external_hook($hook){
            switch($hook){
                case 'attachment':

                    $ticket_id = (isset($_REQUEST['t'])) ? (int)$_REQUEST['t'] : false;
                    $ticket_message_attachment_id = (isset($_REQUEST['tma'])) ? (int)$_REQUEST['tma'] : false;
                    $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                    if($ticket_id && $ticket_message_attachment_id && $hash){
                        $correct_hash = $this->link_open_attachment($ticket_id,$ticket_message_attachment_id,true);
                        if($correct_hash == $hash){
                            $attach = get_single('ticket_message_attachment','ticket_message_attachment_id',$ticket_message_attachment_id);
                            if(file_exists('includes/plugin_ticket/attachments/'.$attach['ticket_message_attachment_id'])){
                                header("Content-type: application/octet-stream");
                                header('Content-Disposition: attachment; filename="'.$attach['file_name'].'";');
                                readfile('includes/plugin_ticket/attachments/'.$attach['ticket_message_attachment_id']);
                            }else{
                                echo 'File no longer exists';
                            }
                            exit;
                        }
                    }
                    break;
                case 'status':
                    ob_start();
                    ?>

                    <table class="wpetss wpetss_status">
                        <tbody>
                        <tr>
                            <th><?php _e('New/Pending Tickets');?></th>
                            <td>
                                <?php
                                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` WHERE status_id = 1 OR status_id = 2";
                                $res = array_shift(qa($sql));
                                echo $res['c'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('In Progress Tickets');?></th>
                            <td>
                                <?php
                                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` WHERE status_id = 3 OR status_id = 5";
                                $res = array_shift(qa($sql));
                                echo $res['c'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Resolved Tickets');?></th>
                            <td>
                                <?php
                                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` WHERE status_id >= 6";
                                $res = array_shift(qa($sql));
                                echo $res['c'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Estimated Turn Around');?></th>
                            <td>
                                <?php echo _l('We will reply within %s and %s days',module_config::c('ticket_turn_around_days_min',2),module_config::c('ticket_turn_around_days',5)); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <?php
                    echo preg_replace('/\s+/',' ',ob_get_clean());
                    exit;
                    break;
                case 'public_new':

                    $ticket_id = 'new';
                    $ticket_account_id = module_config::c('ticket_default_account_id',0); //todo: set from a hashed variable.
                    if($ticket_account_id){
                        $ticket_account = self::get_ticket_account($ticket_account_id);
                    }else{
                        $ticket_account_id = 0;
                        $ticket_account = false;
                    }
                    if(!$ticket_account || $ticket_account['ticket_account_id']!=$ticket_account_id){
                        // dont support accounts yet. work out the default customer id etc.. from settings.
                        $ticket_account = array(
                            'default_customer_id' => module_config::c('ticket_default_customer_id',1),
                            'default_user_id' => module_config::c('ticket_default_user_id',1),
                            'default_type' => module_config::c('ticket_type_default','Support'),
                        );
                    }

                    if(isset($_REQUEST['_process']) && $_REQUEST['_process'] == 'save_public_ticket'){
                        // user is saving the ticket.
                        // process it!
                        if(isset($_POST['new_ticket_message']) && strlen($_POST['new_ticket_message']) > 1){

                            // this allows input variables to be added to our $_POST
                            // like extra fields etc.. from envato module.
                            handle_hook('ticket_create_post',$ticket_id);

                            // we're posting from a public account.
                            // check required fields.
                            if(!trim($_POST['subject'])){
                                return false;
                            }
                            // check this user has a valid email address, find/create a user in the ticket user table.
                            // see if this email address exists in the wp user table, and link that user there.
                            $email = trim(strtolower($_POST['email']));
                            $name = trim($_POST['name']);
                            if(strpos($email,'@')){ //todo - validate email.
                                $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($email)."'";
                                $from_user = qa1($sql);
                                if($from_user){
                                    $from_user_id = $from_user['user_id'];
                                    // woo!! found a user. assign this customer to the ticket.
                                    if($from_user['customer_id']){
                                        $ticket_account['default_customer_id'] = $from_user['customer_id'];
                                    }
                                }else{
                                    // create a user under this account customer.
                                    $default_customer_id = 0;
                                    if($ticket_account && $ticket_account['default_customer_id']){
                                        $default_customer_id = $ticket_account['default_customer_id'];
                                    }
                                    // create a new support user! go go!
                                    $from_user = array(
                                        'name' => $name ? $name : $email,
                                        'customer_id' => $default_customer_id,
                                        'email' => $email,
                                        'status_id' => 1,
                                        'password' => substr(md5(time().mt_rand(0,600)),3,7),
                                    );
                                    global $plugins;
                                    $from_user_id = $plugins['user']->create_user($from_user);
                                    // todo: set the default role for this user
                                    // based on the settings
                                    /*}else{
                                        echo 'Failed - no from accoutn set';
                                        return;
                                    }*/
                                }

                                if(!$from_user_id){
                                    echo 'Failed - cannot find the from user id';
                                    echo $email . ' to support<hr>';
                                    return;
                                }

                                $ticket_id = $this->save_ticket('new',array(
                                                            'user_id' => $from_user_id,
                                                             'assigned_user_id' => $ticket_account['default_user_id'],
                                                             'type' => (isset($_POST['type'])) ? $_POST['type'] : $ticket_account['default_type'],
                                                             'customer_id' => $ticket_account['default_customer_id'],
                                                             'status_id' => 2,
                                                             'ticket_account_id' => $ticket_account_id,
                                                             'unread' => 1,
                                                            'subject' => $_POST['subject'],
                                                            'new_ticket_message' => $_POST['new_ticket_message'],
                                                         ));
                                redirect_browser($this->link_public($ticket_id));

                            }

                        }
                    }

                    $ticket = self::get_ticket($ticket_id);
                    include('public/ticket_customer_new.php');

                    break;
                case 'public':

                    $ticket_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                    $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                    if($ticket_id && $hash){
                        $correct_hash = $this->link_public($ticket_id,true);
                        if($correct_hash == $hash){
                            // all good to print a receipt for this payment.
                            $ticket = $this->get_ticket($ticket_id);

                            if(isset($_POST['_process']) && $_POST['_process'] == 'send_public_ticket'){
                                // user is saving the ticket.
                                // process it!
                                if(isset($_POST['new_ticket_message']) && strlen($_POST['new_ticket_message']) > 1){
                                    // post a new reply to this message.
                                    // who are we replying to?
                                    // it's either a reply from the admin, or from the user via the web interface.
                                    $ticket_creator = $ticket['user_id'];
                                    $to_user_id = $ticket['assigned_user_id'] ? $ticket['assigned_user_id'] : 1;
                                    $this->send_reply($ticket_id,$_POST['new_ticket_message'], $ticket_creator, $to_user_id, 'end_user');

                                    /*$new_status_id = $ticket['status_id'];
                                    if($ticket['status_id']>=6){
                                        // it's cancelled or resolved.
                                    }*/
                                    $new_status_id = 5;
                                    update_insert("ticket_id",$ticket_id,"ticket",array('unread'=>1,'status_id'=>$new_status_id));
                                }
                                redirect_browser($this->link_public($ticket_id));
                            }


                            if($ticket&& $ticket['ticket_id'] == $ticket_id){


                                $admins_rel = self::get_ticket_staff_rel();
                                /*if(!isset($logged_in_user) || !$logged_in_user){
                                    // we assume the user is on the public side.
                                    // use the creator id as the logged in id.
                                    $logged_in_user = module_security::get_loggedin_id();
                                }*/
                                // public hack, we are the ticket responder.
                                $logged_in_user = $ticket['user_id'];

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

                                $admins_rel = self::get_ticket_staff_rel();

                                ob_start();
                                include('public/ticket_customer_view.php');
                                $html = ob_get_clean();

                                module_template::init_template('external_ticket_public_view','{TICKET_HTML}', 'Used when displaying the external view of a ticket to the customer.','code');
                                $template = module_template::get_template_by_key('external_ticket_public_view');
                                $template->assign_values(array(
                                                             'ticket_html' => $html,
                                                         ));
                                $template->page_title = _l('Ticket: %s',module_ticket::ticket_number($ticket['ticket_id']));

                                echo $template->render('pretty_html');
                                exit;

                            }else{
                                _e('Permission Denied. Please logout and try again.');
                            }
                        }
                    }
                    break;
            }
        }



    public static function ticket_number($id){
        $id=(int)$id;
        if(!$id)return _l('New');
        return str_pad($id,6,'0',STR_PAD_LEFT);
    }


    public static function ticket_count($type,$time = false){
        $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` t WHERE t.status_id < 6";
        if($time){
            $sql .= " AND t.last_message_timestamp <= ".(int)$time."";
        }
        $res = qa1($sql);
        return $res['c'];
    }



	public function process(){
		$errors=array();
        if('save_saved_response' == $_REQUEST['_process']){

            $data = array(
                'value' => $_REQUEST['value'],
            );
            $saved_response_id = (int)$_REQUEST['saved_response_id'];
            if((string)$saved_response_id != (string)$_REQUEST['saved_response_id']){
                // we are saving a new response, not overwriting an old one.
                $data['name'] = $_REQUEST['saved_response_id'];
                $saved_response_id = 'new';
            }else{
                // overwriting an old one.
            }
            $this->save_saved_response($saved_response_id,$data);
            // saved via ajax
            exit;

        }else if('insert_saved_response' == $_REQUEST['_process']){

            ob_end_clean();
            $response = $this->get_saved_response($_REQUEST['saved_response_id']);
            echo json_encode($response);
            exit;

        }else if('save_ticket_account' == $_REQUEST['_process']){

            $ticket_account_id = update_insert('ticket_account_id',$_REQUEST['ticket_account_id'],'ticket_account',$_POST);
            if(isset($_REQUEST['butt_save_test'])){
                ?> <a href="<?php echo $this->link_open_account($ticket_account_id);?>">Return to account settings</a><br><br> <?php
                self::import_email($ticket_account_id,false,true);
                exit;
            }
            set_message('Ticket account saved successfully');
            redirect_browser($this->link_open_account($ticket_account_id));

        }else if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['ticket_id']){
            $data = self::get_ticket($_REQUEST['ticket_id']);
            if(module_form::confirm_delete('ticket_id',"Really delete ticket: ".$this->ticket_number($data['ticket_id']),self::link_open($_REQUEST['ticket_id']))){
                $this->delete_ticket($_REQUEST['ticket_id']);
                set_message("Ticket deleted successfully");
                redirect_browser($this->link_open(false));
            }
		}else if("save_ticket" == $_REQUEST['_process']){
            $this->_handle_save_ticket();


		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_tickets($search=array()){


        // work out what customers this user can access?
        $ticket_access = self::get_ticket_data_access();

        $sql = "SELECT t.*, COUNT(tm.ticket_message_id) AS message_count ";
        $from = " FROM `"._DB_PREFIX."ticket` t ";
        $from .= " LEFT JOIN `"._DB_PREFIX."ticket_message` tm ON t.ticket_id = tm.ticket_id ";
		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " t.subject LIKE '%$str%' ";
			$where .= ' ) ';
		}
		if(isset($search['date_from']) && $search['date_from']){
			$str = strtotime(input_date($search['date_from']));
			$where .= " AND ( ";
			$where .= " t.last_message_timestamp >= '$str' ";
			$where .= ' ) ';
		}
		if(isset($search['date_to']) && $search['date_to']){
			$str = strtotime(input_date($search['date_to']));
			$where .= " AND ( ";
			$where .= " t.last_message_timestamp <= '$str' ";
			$where .= ' ) ';
		}
        if(isset($search['ticket_id'])){
            $search['ticket_id'] = trim(ltrim($search['ticket_id'],'0'));
        }
        /*if(isset($search['status_id']) && $search['status_id'] == -1){
            $where .= ' AND ( t.`status_id` = 2 OR t.`status_id` = 3 OR t.`status_id` = 5 ) ';
            unset($search['status_id']);
        }*/

        if(isset($search['status_id']) && strpos($search['status_id'],',') !== false){
            $where .= ' AND ( ';
            foreach(explode(',',$search['status_id']) as $s){
                $s = (int)trim($s);
                if(!$s)continue;
                $where .= ' t.`status_id` = '.$s.' OR ';
            }
            $where = rtrim($where,' OR ');
            $where .= ' ) ';
            unset($search['status_id']);
        }
        if(isset($search['contact']) && $search['contact']){
            $from .= " LEFT JOIN `"._DB_PREFIX."user` u ON t.user_id = u.user_id ";
            $where .= " AND ( u.email LIKE '%".mysql_real_escape_string($search['contact'])."%' )";
        }
        if(isset($search['status_id']) && !$search['status_id']){
            unset($search['status_id']);//hack
        }
		foreach(array('user_id','assigned_user_id','customer_id','website_id','ticket_id','status_id','unread') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND t.`$key` = '$str'";
            }
        }
        switch($ticket_access){
            case _TICKET_ACCESS_ALL:

                break;
            case _TICKET_ACCESS_ASSIGNED:
                // we only want tickets assigned to me.
                $where .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                break;
            case _TICKET_ACCESS_CREATED:
                // we only want tickets i created.
                $where .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                break;
        }
		$group_order = ' GROUP BY t.ticket_id ORDER BY t.last_message_timestamp ASC'; // t.unread DESC,
		$sql = $sql . $from . $where . $group_order;
		$result = qa($sql);
		//module_security::filter_data_set("ticket",$result);
		return $result;
		//return get_multiple("ticket",$search,"ticket_id","fuzzy","last_message_timestamp DESC");

	}
    public static function get_ticket_messages($ticket_id){
		return get_multiple("ticket_message",array('ticket_id'=>$ticket_id),"ticket_message_id","exact","ticket_message_id",true);

	}
    public static function get_ticket_message($ticket_message_id){
		return get_single('ticket_message','ticket_message_id',$ticket_message_id);
	}
    public static function get_ticket_message_attachments($ticket_message_id){
		return get_multiple("ticket_message_attachment",array('ticket_message_id'=>$ticket_message_id),"ticket_message_attachment_id","exact","ticket_message_attachment_id");

	}
    public static function get_accounts(){
		return get_multiple("ticket_account",false,"ticket_account_id");

	}
    public static function get_accounts_rel(){
		$res = array();
        foreach(self::get_accounts() as $row){
            $res[$row['ticket_account_id']] = $row['name'];
        }
        return $res;
	}
    public static function get_ticket_staff(){
        $admins = module_user::get_users_by_permission(
            array(
                'category' => 'Ticket',
                'name' => 'Tickets',
                'module' => 'ticket',
                'edit' => 1,
            )

        );
        return $admins;
    }
    public static function get_ticket_staff_rel(){
        $admins = self::get_ticket_staff();
        $admins_rel = array();
        foreach($admins as $admin){
            $admins_rel[$admin['user_id']] = $admin['name'];
        }
        return $admins_rel;
    }
    public static function get_ticket_account($ticket_account_id){
		$ticket_account_id = (int)$ticket_account_id;
        $ticket_account = false;
        if($ticket_account_id>0){
		    $ticket_account = get_single("ticket_account","ticket_account_id",$ticket_account_id);
        }
        return $ticket_account;
	}
	public static function get_ticket($ticket_id){


        $ticket_access = self::get_ticket_data_access();

        $ticket_id = (int)$ticket_id;
        $ticket = false;
        if($ticket_id>0){
		    //$ticket = get_single("ticket","ticket_id",$ticket_id);
            $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t WHERE t.ticket_id = $ticket_id ";
            switch($ticket_access){
                case _TICKET_ACCESS_ALL:

                    break;
                case _TICKET_ACCESS_ASSIGNED:
                    // we only want tickets assigned to me.
                    $sql .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
                case _TICKET_ACCESS_CREATED:
                    // we only want tickets I created.
                    $sql .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
            }
            $ticket = qa1($sql, false);
        }
        if(!$ticket){
            $customer_id = 0;
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id']){
                //
                $customer_id = (int)$_REQUEST['customer_id'];
                // find default website id to use.
                if(isset($_REQUEST['website_id'])){
                    $website_id = (int)$_REQUEST['website_id'];
                }else{

                }
            }
            $ticket = array(
                'ticket_id' => 'new',
                'customer_id' => $customer_id,
                'website_id' => (isset($_REQUEST['website_id'])? $_REQUEST['website_id'] : 0),
                'subject' => '',
                'date_completed' => '',
                'status_id'  => 2, // new
                'user_id'  => module_security::get_loggedin_id(),
                'assigned_user_id'  => module_config::c('ticket_default_user_id',1), // who is the default assigned user?
                'ticket_account_id'  => module_config::c('ticket_default_account_id',0), // default pop3 account for pro users.
                'last_message_timestamp'  => 0,
                'last_ticket_message_id'  => 0,
                'message_count'  => 0,
                'position'  => self::ticket_count('pending') + 1,
                'type'  => module_config::s('ticket_type_default','Support'),
                'total_pending' => self::ticket_count('pending') + 1,
            );

        }else{
            // find the position of this ticket
            // the position is determined by the number of pending tickets
            // that have a last_message_timestamp earlier than this ticket.
            $ticket['position'] = self::ticket_count('pending',$ticket['last_message_timestamp']);
            $ticket['total_pending'] = self::ticket_count('pending');
            $messages = self::get_ticket_messages($ticket_id);
            $ticket['message_count'] = count($messages);
            end($messages);
            $last_message = current($messages);
            $ticket['last_ticket_message_id'] = $last_message['ticket_message_id'];
        }
		return $ticket;
	}

    public static function mark_as_read($ticket_id,$credential_check=false){
        $ticket_id=(int)$ticket_id;
        if($ticket_id>0){
            /*if($credential_check){
                $admins_rel = self::get_ticket_staff_rel();
                // we check what the last message is.
                $messages = self::get_ticket_messages($ticket_id);
                end($messages);
                $last_message = current($messages);
                // if the last message is from an admin:
                if($last_message['']);
                // FUCK. this isn't going to work.
                // will do it later.
            }*/
            update_insert("ticket_id",$ticket_id,"ticket",array('unread'=>0));
        }
    }
    public static function mark_as_unread($ticket_id){
        $ticket_id=(int)$ticket_id;
        if($ticket_id>0){
            update_insert("ticket_id",$ticket_id,"ticket",array('unread'=>1));
        }
    }
	public function save_ticket($ticket_id,$data){
        if(isset($data['website_id']) && $data['website_id']){
            $website = module_website::get_website($data['website_id']);
            $data['customer_id'] = $website['customer_id'];
        }
        if(isset($data['user_id']) && $data['user_id']){
            $user = module_user::get_user($data['user_id']);
            $data['customer_id'] = $user['customer_id'];
        }
        if(isset($data['change_assigned_user_id']) && (int)$data['change_assigned_user_id']>0){
            $data['assigned_user_id'] = $data['change_assigned_user_id'];
        }
		$ticket_id = update_insert("ticket_id",$ticket_id,"ticket",$data);
        if($ticket_id){

            if(isset($data['new_ticket_message']) && strlen($data['new_ticket_message']) > 1){
                // post a new reply to this message.
                // who are we replying to?


                $ticket_data = $this->get_ticket($ticket_id);

                if(isset($data['change_status_id']) && $data['change_status_id']){
                    update_insert("ticket_id",$ticket_id,"ticket",array('status_id'=>$data['change_status_id']));
                }else if ($ticket_data['status_id']==6 || $ticket_data['status_id'] == 7){
                    $data['change_status_id'] = 5; // change to in progress.
                }


                // it's either a reply from the admin, or from the user via the web interface.
                $ticket_data = $this->get_ticket($ticket_id);
                $logged_in_user = module_security::get_loggedin_id();
                if(!$logged_in_user){
                    $logged_in_user = $ticket_data['user_id'];
                }

                if(!$ticket_data['user_id']){
                    update_insert('ticket_id',$ticket_id,'ticket',array('user_id' => module_security::get_loggedin_id()));
                    $ticket_data['user_id'] = module_security::get_loggedin_id();
                }
                $ticket_creator = $ticket_data['user_id'];
                if($ticket_creator == $logged_in_user){
                    // we are sending a reply back to the admin, from the end user.
                    self::mark_as_unread($ticket_id);
                    $ticket_message_id = $this->send_reply($ticket_id,$data['new_ticket_message'],$ticket_creator, $ticket_data['assigned_user_id'] ? $ticket_data['assigned_user_id'] : 1, 'end_user');
                }else{
                    // we are sending a reply back to the ticket user.
                    // admin is allowed to change the status of a message.
                    $from_user_id = $ticket_data['assigned_user_id'] ? $ticket_data['assigned_user_id'] : module_security::get_loggedin_id();
                    //echo "From $from_user_id to $ticket_creator ";exit;
                    $ticket_message_id = $this->send_reply($ticket_id,$data['new_ticket_message'], $from_user_id, $ticket_creator, 'admin');
                }
            }

            if(isset($data['change_status_id']) && $data['change_status_id']){
                // we only update this status if the sent reply or send reply and next buttons are clicked.
                if(isset($_REQUEST['newmsg']) || isset($_REQUEST['newmsg_next'])){
                    update_insert("ticket_id",$ticket_id,"ticket",array('status_id'=>$data['change_status_id']));
                }
            }

        }
        module_extra::save_extras('ticket','ticket_id',$ticket_id);
		return $ticket_id;
	}

	public static function delete_ticket($ticket_id){
		$ticket_id=(int)$ticket_id;
		$sql = "DELETE FROM "._DB_PREFIX."ticket WHERE ticket_id = '".$ticket_id."' LIMIT 1";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."ticket_message WHERE ticket_id = '".$ticket_id."'";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."ticket_message_attachment WHERE ticket_id = '".$ticket_id."'";
		$res = query($sql);

//		module_note::note_delete("ticket",$ticket_id);
//        module_extra::delete_extras('ticket','ticket_id',$ticket_id);
	}
    public function login_link($ticket_id){
        return module_security::generate_auto_login_link($ticket_id);
    }

    public static function get_statuses(){
        return self::$ticket_statuses;
    }
    public static function get_types(){
        $sql = "SELECT `type` FROM `"._DB_PREFIX."ticket` GROUP BY `type` ORDER BY `type`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['type']] = $r['type'];
        }
        return $statuses;
    }


    public static function send_reply($ticket_id,$message,$from_user_id,$to_user_id, $reply_type = 'admin' , $internal_from = ''){


        // we also check if this message contains anything, or anything above the "reply line"
        // this is a hack to stop the autoreply loop that seems to happen when sending an email as yourself from  your envato profile.

        // stip out the text before our "--reply above this line-- bit.
        // copied code from ticket_admin_edit.php
        /*$reply__ine_default = '----- (Please reply above this line) -----'; // incase they change it
        $reply__ine =   module_config::s('ticket_reply_line',$reply__ine_default);
        $text = preg_replace("#<br[^>]*>#",'',$message);
        // convert to single text.
        $text = preg_replace('#\s+#imsU',' ',$text);
        if(
            preg_match('#^\s*'.preg_quote($reply__ine,'#').'.*#ims',$text) ||
            preg_match('#^\s*'.preg_quote($reply__ine_default,'#').'.*#ims',$text)
        ){
            // no content. don't send email
            //mail('dtbaker@gmail.com','ticket reply '.$ticket_id,'sending reply for text:\''.$text."' \n\n\n Original:\n".$message);
            return false;
        }*/

        // $message is in text format, need to nl2br it before printing.

        $ticket_number = self::ticket_number($ticket_id);
        $ticket_details = self::get_ticket($ticket_id);


        $to_user_a = module_user::get_user($to_user_id);
        $from_user_a = module_user::get_user($from_user_id);

        // the from details need to match the ticket account details.
        if($ticket_details['ticket_account_id']){
            $ticket_account = self::get_ticket_account($ticket_details['ticket_account_id']);
        }else{
            $ticket_account = false;
        }
        if($ticket_account && $ticket_account['email']){
            // want the user to reply to our ticketing system.
            $reply_to_address = $ticket_account['email'];
            $reply_to_name = $ticket_account['name'];
        }else{
            // reply to creator of the email.
            $reply_to_address = $from_user_a['email'];
            $reply_to_name = $from_user_a['name'];
        }


        $ticket_message_id = update_insert('ticket_message_id','new','ticket_message',array(
                                             'ticket_id' => $ticket_id,
                                             'content' => $message,
                                             'message_time' => time(),
                                             'from_user_id' => $from_user_id,
                                             'to_user_id' => $to_user_id,
                                            'message_type_id' => ($reply_type == 'admin' ? _TICKET_MESSAGE_TYPE_ADMIN : _TICKET_MESSAGE_TYPE_CREATOR),
                                     ));
        if(!$ticket_message_id)return false;

        // handle any attachemnts.

        // are there any attachments?
        if($ticket_message_id && isset($_FILES['attachment']) && isset($_FILES['attachment']['tmp_name']) && is_array($_FILES['attachment']['tmp_name'])){
            foreach($_FILES['attachment']['tmp_name'] as $key => $val){
                if(is_uploaded_file($val)){
                    // save attachments against ticket!

                    if(function_exists('finfo_open')){
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $val);
                    }else{
                        if(!function_exists('mime_content_type')) {

                            function mime_content_type($filename) {

                                $mime_types = array(

                                    'txt' => 'text/plain',
                                    'htm' => 'text/html',
                                    'html' => 'text/html',
                                    'php' => 'text/html',
                                    'css' => 'text/css',
                                    'js' => 'application/javascript',
                                    'json' => 'application/json',
                                    'xml' => 'application/xml',
                                    'swf' => 'application/x-shockwave-flash',
                                    'flv' => 'video/x-flv',

                                    // images
                                    'png' => 'image/png',
                                    'jpe' => 'image/jpeg',
                                    'jpeg' => 'image/jpeg',
                                    'jpg' => 'image/jpeg',
                                    'gif' => 'image/gif',
                                    'bmp' => 'image/bmp',
                                    'ico' => 'image/vnd.microsoft.icon',
                                    'tiff' => 'image/tiff',
                                    'tif' => 'image/tiff',
                                    'svg' => 'image/svg+xml',
                                    'svgz' => 'image/svg+xml',

                                    // archives
                                    'zip' => 'application/zip',
                                    'rar' => 'application/x-rar-compressed',
                                    'exe' => 'application/x-msdownload',
                                    'msi' => 'application/x-msdownload',
                                    'cab' => 'application/vnd.ms-cab-compressed',

                                    // audio/video
                                    'mp3' => 'audio/mpeg',
                                    'qt' => 'video/quicktime',
                                    'mov' => 'video/quicktime',

                                    // adobe
                                    'pdf' => 'application/pdf',
                                    'psd' => 'image/vnd.adobe.photoshop',
                                    'ai' => 'application/postscript',
                                    'eps' => 'application/postscript',
                                    'ps' => 'application/postscript',

                                    // ms office
                                    'doc' => 'application/msword',
                                    'rtf' => 'application/rtf',
                                    'xls' => 'application/vnd.ms-excel',
                                    'ppt' => 'application/vnd.ms-powerpoint',

                                    // open office
                                    'odt' => 'application/vnd.oasis.opendocument.text',
                                    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
                                );

                                $ext = strtolower(array_pop(explode('.',$filename)));
                                if (array_key_exists($ext, $mime_types)) {
                                    return $mime_types[$ext];
                                }
                                elseif (function_exists('finfo_open')) {
                                    $finfo = finfo_open(FILEINFO_MIME);
                                    $mimetype = finfo_file($finfo, $filename);
                                    finfo_close($finfo);
                                    return $mimetype;
                                }
                                else {
                                    return 'application/octet-stream';
                                }
                            }
                        }
                        $mime = mime_content_type($_FILES['attachment']['name'][$key]);
                    }

                    $attachment_id = update_insert('ticket_message_attachment_id','new','ticket_message_attachment',array(
                                             'ticket_id' => $ticket_id,
                                             'ticket_message_id' => $ticket_message_id,
                                             'file_name' => $_FILES['attachment']['name'][$key],
                                             'content_type' => $mime,
                                    ));
                    move_uploaded_file($val, 'includes/plugin_ticket/attachments/'.$attachment_id.'');
                }
            }
        }


        if($internal_from != 'autoreply'){
            // stops them all having the same timestamp on a big import.
            update_insert('ticket_id',$ticket_id,'ticket',array(
                                         'last_message_timestamp' => time(),
                              ));
        }else{
            // we are sending an auto reply, flag this in the special cache field.
            // hacky!
            update_insert('ticket_message_id',$ticket_message_id,'ticket_message',array(
                     'cache'=>'autoreply',
             ));
        }
        //$reply_line = module_config::s('ticket_reply_line','----- (Please reply above this line) -----');

        $s = self::get_statuses();


        if($to_user_id == $ticket_details['user_id']){
            // WE ARE emailing the "User" from support.
            // so the support is emailing a response back to the customer.
            module_ticket::send_customer_alert($ticket_id,$message,$ticket_message_id);

        }else{
            $message = nl2br(htmlspecialchars($message)); // because message is in text format, before we send admin notification do this.
            module_ticket::send_admin_alert($ticket_id,$message);
        }


        if($reply_type == 'end_user' && !$ticket_details['message_count']){
            // this is the first message!
            // send an email back to the user confirming this submissions via the web interface.
            self::send_autoreply($ticket_id,$message);
        }

        return $ticket_message_id;

    }

    /**
     * Sends the customer an email letting them know the administrator has updated
     * their ticket with a new message.
     *
     * @static
     * @param $ticket_id
     * @param string $message
     */
    public static function send_customer_alert($ticket_id,$message='',$ticket_message_id=false){

        $ticket_details = self::get_ticket($ticket_id);
        $ticket_account_data = self::get_ticket_account($ticket_details['ticket_account_id']);
        $ticket_number = self::ticket_number($ticket_id);
        $s = self::get_statuses();
        $reply_line = module_config::s('ticket_reply_line','----- (Please reply above this line) -----');
        if(!$ticket_message_id){
            $ticket_message_id = $ticket_details['last_ticket_message_id'];
        }
        $last_ticket_message = self::get_ticket_message($ticket_message_id);
        if($message){
            $message = nl2br(htmlspecialchars($message));
        }
        if(!$message && $last_ticket_message){
            if($last_ticket_message['htmlcontent']){
                $message = trim($last_ticket_message['htmlcontent']);
            }else if($last_ticket_message['content']){
                $message = nl2br(htmlspecialchars($last_ticket_message['content']));
            }
        }

        $to_user_id = $last_ticket_message['to_user_id'];
        if(!$to_user_id)$to_user_id = $ticket_details['user_id']; // default to assigned user
        $to_user_a = module_user::get_user($to_user_id);

        $from_user_id = $last_ticket_message['from_user_id'];
        if(!$from_user_id)$from_user_id = $ticket_details['assigned_user_id']; // default to assigned staff member
        $from_user_a = module_user::get_user($from_user_id);

        if($ticket_details['ticket_account_id']){
            $ticket_account = self::get_ticket_account($ticket_details['ticket_account_id']);
        }else{
            $ticket_account = false;
        }
        if($ticket_account && $ticket_account['email']){
            // want the user to reply to our ticketing system.
            $reply_to_address = $ticket_account['email'];
            $reply_to_name = $ticket_account['name'];
        }else{
            // reply to creator of the email.
            $reply_to_address = $from_user_a['email'];
            $reply_to_name = $from_user_a['name'];
        }

        $template = module_template::get_template_by_key('ticket_container');
        $template->assign_values(array(
            'ticket_number' => self::ticket_number($ticket_id),
            'ticket_status' => $s[$ticket_details['status_id']],
            'message' => $message,
            'subject' => $ticket_details['subject'],
            'position_current' => $ticket_details['position'],
            'position_all' => self::ticket_count('pending'), // all tickets that are less than status 6 (resolved)
            'reply_line' => $reply_line,
            'days' => module_config::c('ticket_turn_around_days',5),
            'url' => self::link_public($ticket_id),
            'message_count' => $ticket_details['message_count'],
        ));
        $content = $template->replace_content();

        $email = module_email::new_email();
        $email->set_to('user',$to_user_id);
        $email->set_from('user',$from_user_id);
        $email->set_reply_to($reply_to_address,$reply_to_name);
        $email->set_subject('[TICKET:'.$ticket_number.'] Re: '.$ticket_details['subject']);
        $email->set_html($content);
        // check attachments:
        $attachments = self::get_ticket_message_attachments($ticket_message_id);
        foreach($attachments as $attachment){
            $file_path = 'includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id'];
            $file_name = $attachment['file_name'];
            $email->AddAttachment($file_path,$file_name);
        }
        $email->send();
    }


    // send an alert to the admin letting them know there's a new ticket.
    public static function send_admin_alert($ticket_id,$message='') {
        $ticket_data = self::get_ticket($ticket_id);
        $ticket_account_data = self::get_ticket_account($ticket_data['ticket_account_id']);
        $ticket_number = self::ticket_number($ticket_id);
        if(!$message && $ticket_data['last_ticket_message_id']){
            $last_message = self::get_ticket_message($ticket_data['last_ticket_message_id']);
            $htmlmessage = trim($last_message['htmlcontent']);
            if($htmlmessage){
                $message = $htmlmessage;
            }else{
                $message = nl2br(htmlspecialchars(trim($last_message['content'])));
            }
        }
        $to = module_config::c('ticket_admin_email_alert',_ERROR_EMAIL);
        if(strlen($to)<4)return;
        // do we only send this on first emails or not ?
        $first_only = module_config::c('ticket_admin_alert_first_only',0);
        if($first_only && $ticket_data['message_count'] > 1)return;
        $s = self::get_statuses();
        $reply_line = module_config::s('ticket_reply_line','----- (Please reply above this line) -----');
        // autoreplies go back to the user - not our admin system:
        $from_user_a = module_user::get_user($ticket_data['user_id']);
        $reply_to_address = $from_user_a['email'];
        $reply_to_name = $from_user_a['name'];

        $template = module_template::get_template_by_key('ticket_admin_email');
        $template->assign_values(array(
            'ticket_number' => self::ticket_number($ticket_id),
            'ticket_status' => $s[$ticket_data['status_id']],
            'message' => $message,
            'subject' => $ticket_data['subject'],
            'position_current' => $ticket_data['position'],
            'position_all' => self::ticket_count('pending'), // all tickets that are less than status 6 (resolved)
            'reply_line' => $reply_line,
            'days' => module_config::c('ticket_turn_around_days',5),
            'url' => self::link_public($ticket_id),
            'url_admin' => self::link_open($ticket_id),
            'message_count' => $ticket_data['message_count'],
        ));
        $content = $template->replace_content();

        $email = module_email::new_email();
        $email->set_to_manual($to);
        if($ticket_account_data && $ticket_account_data['email']){
            $email->set_from_manual($ticket_account_data['email'],$ticket_account_data['name']);
            $email->set_bounce_address($ticket_account_data['email']);
        }else{
            $email->set_from_manual($to, module_config::s('admin_system_name'));
            $email->set_bounce_address($to);
        }
        //$email->set_from('user',$from_user_id);
        //$email->set_from('foo','foo',$to,'Admin');
        // do we reply to the user who created this, or to our ticketing system?
        if(module_config::c('ticket_admin_alert_postback',1) && $ticket_account_data && $ticket_account_data['email']){
            $email->set_reply_to($ticket_account_data['email'],$ticket_account_data['name']);
        }else{
            $email->set_reply_to($reply_to_address,$reply_to_name);
        }
        $email->set_subject(sprintf(module_config::c('ticket_admin_alert_subject','Support Ticket Updated: [TICKET:%s]'),$ticket_number));
        $email->set_html($content);
        // check attachments:
        $attachments = self::get_ticket_message_attachments($ticket_data['last_ticket_message_id']);
        foreach($attachments as $attachment){
            $file_path = 'includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id'];
            $file_name = $attachment['file_name'];
            $email->AddAttachment($file_path,$file_name);
        }
        $email->send();
    }



    public static function send_autoreply($ticket_id,$userse_message='') {
        // send back an auto responder letting them know where they are in the queue.
        $ticket_data = self::get_ticket($ticket_id);

        $template = module_template::get_template_by_key('ticket_autoreply');
        $auto_reply_message = $template->content;
        $from_user_id = $ticket_data['assigned_user_id'] ? $ticket_data['assigned_user_id'] : 1;
        //if($ticket_data['user_id'] != $from_user_id){
        // check if we have sent an autoreply to this address in the past 5 minutes, if we have we dont send another one.
        // this stops autoresponder spam messages.
        $time = time() - 300; // 5 mins
        $sql = "SELECT * FROM `"._DB_PREFIX."ticket_message` tm WHERE to_user_id = '".(int)$ticket_data['user_id']."' AND message_time > '".$time."' AND `cache` = 'autoreply'";
        $res = qa($sql);
        if(!count($res)){

            $send_autoreply = true;

            // other logic to check here???

            if($send_autoreply){
                self::send_reply($ticket_id,$auto_reply_message, $from_user_id, $ticket_data['user_id'], 'admin', 'autoreply');
            }
        }
        //}
    }

    public static function run_cron(){

        if(!function_exists('imap_open')){
            set_error('Please contact hosting provider and enable IMAP for PHP');
            echo 'Imap extension not available for php';
            return false;
        }

        include('cron/read_emails.php');
    }


    private function _subject_decode($str, $mode=0, $charset="UTF-8") {

        return iconv_mime_decode($str,ICONV_MIME_DECODE_CONTINUE_ON_ERROR,"UTF-8");

        $data = imap_mime_header_decode($str);
        if (count($data) > 0) {
          // because iconv doesn't like the 'default' for charset
          $charset = ($data[0]->charset == 'default') ? 'ASCII' : $data[0]->charset;
          return(iconv($charset, $charset, $data[0]->text));
        }
        return("");
     }


    public static function import_email($ticket_account_id,$import=true,$debug=false){


        require_once('includes/plugin_ticket/cron/rfc822_addresses.php');
        require_once('includes/plugin_ticket/cron/mime_parser.php');

        $admins_rel = self::get_ticket_staff_rel();
        $created_tickets = array();
        $ticket_account_id=(int)$ticket_account_id;
        $account = self::get_ticket_account($ticket_account_id);
        if(!$account)return false;
        $email_username = $account['username'];
        $email_password = $account['password'];
        $email_host = $account['host'];
        $email_port = $account['port'];
        $reply_from_user_id = $account['default_user_id'];
        $support_type = $account['default_type'];
        $subject_regex = $account['subject_regex'];
        $body_regex = $account['body_regex'];
        $to_regex = $account['to_regex'];
        $search_string = $account['search_string'];
        $mailbox = $account['mailbox'];
        $imap = (int)$account['imap'];
        $secure = (int)$account['secure'];
        $start_date = ($account['start_date'] && $account['start_date'] != '0000-00-00') ? $account['start_date'] : false;


        if(!$email_host || !$email_username)return false;

        // try to connect with ssl first:
        $ssl = ($secure) ? '/ssl' : '';
        if($imap){
            $host = '{'.$email_host.':'.$email_port.'/imap'.$ssl.'}'.$mailbox;
            if($debug)echo "Connecting to $host <br>\n";
            $mbox = imap_open ($host, $email_username, $email_password);
        }else{
            $host = '{'.$email_host.':'.$email_port.'/pop3'.$ssl.'/novalidate-cert}';
            if($debug)echo "Connecting to $host <br>\n";
            $mbox = imap_open ($host.$mailbox, $email_username, $email_password);
        }
        if(!$mbox){
            // todo: send email letting them know bounce checking failed?
            echo 'Failed to connect when checking for support ticket emails.'.imap_last_error();
            imap_errors();
            return false;
        }



        update_insert('ticket_account_id',$account['ticket_account_id'],'ticket_account',array(
                                             'last_checked' => time(),
                                         ));

        $MC = imap_check($mbox);
        //echo 'Connected'.$MC->Nmsgs;
        // do a search if
        $search_results = array(-1);
        if($imap && $search_string){
            //imap_sort($mbox,SORTARRIVAL,0);
            // we do a hack to support multiple searches in the imap string.
            if(strpos($search_string,'||')){
                $search_strings = explode('||',$search_string);
            }else{
                $search_strings = array($search_string);
            }
            $search_results = array();
            foreach($search_strings as $this_search_string){
                $this_search_string = trim($this_search_string);
                if(!$this_search_string){
                    return false;
                }
                if($debug)echo "Searching for $this_search_string <br>\n";
                $this_search_results = imap_search($mbox,$this_search_string);
                if($debug){
                    echo " -- found ".count($this_search_results)." results <br>\n";
                }
                $search_results = array_merge($search_results,$this_search_results);
            }
            if(!$search_results){
                echo "No search results for $search_string ";
                return false;
            }else{
                sort($search_results);
            }
        }
        imap_errors();
        //print_r($search_results);//imap_close($mbox);return false;
        $sorted_emails = array();
        foreach($search_results as $search_result){

            if($search_result>=0){
                $result = imap_fetch_overview($mbox,$search_result,0);
            }else{
                //$result = imap_fetch_overview($mbox,"1:100",0);
                $result = imap_fetch_overview($mbox,"1:". min(100,$MC->Nmsgs),0);
            }
            foreach ($result as $overview) {


                if(!isset($overview->subject))continue;
                $this_subject = self::_subject_decode((string)$overview->subject);
                if($subject_regex && !preg_match($subject_regex,$this_subject)){
                    continue;
                }
                if($start_date > 1000){
                    if(strtotime($overview->date) < strtotime($start_date)){
                        continue;
                    }
                }

                $message_id = isset($overview->message_id) ? (string)$overview->message_id : false;
                if(!$message_id){
                    $overview->message_id = $message_id = md5($this_subject . $overview->date);
                }

                //echo "#{$overview->msgno} ({$overview->date}) - From: {$overview->from} <br> {$this_subject} <br>\n";
                // check this email hasn't been processed before.
                // check this message hasn't been processed yet.
                $ticket = get_single('ticket_message','message_id',$message_id);
                if($ticket){
                    continue;
                }

                // get ready to sort them.
                $overview->time = strtotime($overview->date);
                $sorted_emails [] = $overview;
            }
        }
        if(!function_exists('dtbaker_ticket_import_sort')){
            function dtbaker_ticket_import_sort($a,$b){
                return $a->time > $b->time;
            }
        }
        uasort($sorted_emails,'dtbaker_ticket_import_sort');
        $message_number = 0;
        foreach($sorted_emails as $overview){
                $message_number++;

                $this_subject = self::_subject_decode((string)$overview->subject);
                $message_id = (string)$overview->message_id;

                if($debug){
                    ?>
                        <div style="padding:5px; border:1px solid #EFEFEF; margin:4px;">
                            <div>
                                <strong><?php echo $message_number;?></strong>
                                Date: <strong><?php echo $overview->date;?></strong> <br/>
                                Subject: <strong><?php echo htmlspecialchars($this_subject);?></strong> <br/>
                                From: <strong><?php echo htmlspecialchars($overview->from);?></strong>
                                To: <strong><?php echo htmlspecialchars($overview->to);?></strong>
                                <!-- <a href="#" onclick="document.getElementById('msg_<?php echo $message_number;?>').style.display='block'; return false;">view body</a>
                            </div>
                            <div style="display:none; padding:10px; border:1px solid #CCC;" id="msg_<?php echo $message_number;?>">
                                <?php
                                // echo htmlspecialchars($results['Data']);
                                ?> -->
                            </div>
                        </div>
                    <?php
                }
                if(!$import){
                    continue;
                }

                $tmp_file = tempnam(_UCM_FOLDER.'/temp/','ticket');
                imap_savebody  ($mbox, $tmp_file, $overview->msgno);
                $mail_content = file_get_contents($tmp_file);


                $mime=new mime_parser_class();
                $mime->mbox = 0;
                $mime->decode_bodies = 1;
                $mime->ignore_syntax_errors = 1;
                $parameters=array(
                    //'File'=>$mailfile,
                    'Data'=>$mail_content,
                    //'SaveBody'=>'/tmp',
                    //'SkipBody'=>0,
                );

                $parse_success = false;
                if(!$mime->Decode($parameters, $decoded)){
                    echo 'MIME message decoding error: '.$mime->error.' at position '.$mime->error_position."\n";
                    // TODO - send warning email to admin.
                    $parse_success = false;
                }else{
                    for($message = 0; $message < count($decoded); $message++){
                        if($mime->Analyze($decoded[$message], $results)){

                            if(isset($results['From'][0]['address'])){
                                $from_address = $results['From'][0]['address'];
                            }else{
                                continue;
                            }


                            if($to_regex){
                                $to_match = false;
                                foreach($results['To'] as $possible_to_address){
                                    if(preg_match($to_regex,$possible_to_address['address'])){
                                        $to_match = true;
                                    }
                                }
                                if(!$to_match){
                                    continue;
                                }
                            }

                            // find out which accout this sender is from.
                            if(preg_match('/@(.*)$/',$from_address,$matches)){



                                // run a hook now to parse the from address.


                                $domain = $matches[1];

                                // find this sender in the database.
                                // if we cant find this sender/customer in the database
                                // then we add this customer as a "support user" to the default customer for this ticketing system.
                                // based on the "to" address of this message.



                                //store this as an eamil
                                $email_to = '';
                                $email_to_first = current($results['To']);
                                if($email_to_first){
                                    $email_to = $email_to_first['address'];
                                }

                                // work out the from and to users.
                                $from_user_id = 0; // this becomes the "user_id" field in the ticket table.
                                $to_user_id = 0; // this is admin. leave blank for now i guess.
                                // try to find a user based on this from email address.
                                $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($from_address)."'";
                                $from_user = qa1($sql);
                                if($from_user){
                                    $from_user_id = $from_user['user_id'];
                                    // woo!!found a user. assign this customer to the ticket.
                                    if($from_user['customer_id']){
                                        $account['default_customer_id'] = $from_user['customer_id'];
                                    }

                                }else{
                                    // create a user under this account customer.
                                    if($account['default_customer_id']){
                                        // create a new support user! go go!
                                        $from_user = array(
                                            'name' => isset($results['From'][0]['name']) ? $results['From'][0]['name'] : $from_address,
                                            'customer_id' => $account['default_customer_id'],
                                            'email' => $from_address,
                                            'status_id' => 1,
                                            'password' => substr(md5(time().mt_rand(0,600)),3,7),
                                        );
                                        global $plugins;
                                        $from_user_id = $plugins['user']->create_user($from_user,'support');
                                    }else{
                                        echo 'Failed - no from accoutn set';
                                        continue;
                                    }
                                }

                                if(!$from_user_id){
                                    echo 'Failed - cannot find the from user id';
                                    echo $from_address . ' to '.var_export($results['To'],true).' : subject: '.$this_subject.'<hr>';
                                    continue;
                                }
                                $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($email_to)."'";
                                $to_user_temp = qa1($sql);
                                if($to_user_temp){
                                    $to_user_id = $to_user_temp['user_id'];
                                    // woo!!
                                }

                                $message_type_id = _TICKET_MESSAGE_TYPE_CREATOR; // from an end user.
                                if(isset($admins_rel[$from_user_id])){
                                    $message_type_id = _TICKET_MESSAGE_TYPE_ADMIN; // from an admin replying via email.
                                }
                                $ticket_id = false;
                                $new_message = true;
                                // check if the subject matches an existing ticket subject.
                                if(preg_match('#\[TICKET:(\d+)\]#i',$this_subject,$subject_matches)){
                                    // found an existing ticket.
                                    // find this ticket in the system.
                                    $ticket_id = ltrim($subject_matches[1],'0');
                                    // see if it exists.
                                    $existing_ticket = get_single('ticket','ticket_id',$ticket_id);
                                    if($existing_ticket){
                                        // woot!
                                        // todo - check the from/to email address is correct as well.
                                        // meh.
                                        update_insert('ticket_id',$ticket_id,'ticket',array(
                                                      'status_id' => 5,// change status to in progress.
                                                      'last_message_timestamp' => strtotime($overview->date),
                                                   ));
                                        $new_message = false;
                                    }else{
                                        // fail..
                                        $ticket_id = false;
                                    }
                                }else{
                                    // we search for this subject, and this sender, to see if they have sent a follow up
                                    // before we started the ticketing system.
                                    // handy for importing an existing inbox with replies etc..

                                    // check to see if the subject matches any existing subjects.
                                    $search_subject1 = trim(preg_replace('#^Re:?\s*#i','',$this_subject));
                                    $search_subject2 = trim(preg_replace('#^Fwd?:?\s*#i','',$this_subject));
                                    $search_subject3 = trim($this_subject);
                                    // find any threads that match this subject, from this user id.
                                    $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t ";
                                    $sql .= " WHERE t.`user_id` = ".(int)$from_user_id." ";
                                    $sql .= " AND ( t.`subject` LIKE '%".mysql_real_escape_string($search_subject1)."%' OR ";
                                    $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject2)."%' OR ";
                                    $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject3)."%') ";
                                    $sql .= " ORDER BY ticket_id DESC;";
                                    $match = qa1($sql);
                                    if(count($match) && (int)$match['ticket_id'] > 0){
                                        // found a matching email. stoked!
                                        // add it in as a reply from the end user.
                                        $ticket_id = $match['ticket_id'];
                                        update_insert('ticket_id',$ticket_id,'ticket',array(
                                                      'status_id' => 5,// change status to in progress.
                                                      'last_message_timestamp' => strtotime($overview->date),
                                                   ));
                                        $new_message = false;

                                    }

                                    if(!$ticket_id){
                                        // now we see if any match the "TO" address, ie: it's us replying to the user.
                                        // handly from a gmail import.
                                        if($email_to){
                                            $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($email_to)."'";
                                            $temp_to_user = qa1($sql);
                                            if($temp_to_user && $temp_to_user['user_id']){
                                                // we have sent emails to this user before...
                                                // check to see if the subject matches any existing subjects.

                                                $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t ";
                                                $sql .= " WHERE t.`user_id` = ".(int)$temp_to_user['user_id']." ";
                                                $sql .= " AND ( t.`subject` LIKE '%".mysql_real_escape_string($search_subject1)."%' OR ";
                                                $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject2)."%' OR ";
                                                $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject3)."%') ";
                                                $sql .= " ORDER BY ticket_id DESC;";
                                                $match = qa1($sql);
                                                if(count($match) && (int)$match['ticket_id'] > 0){
                                                    // found a matching email. stoked!
                                                    // add it in as a reply from the end user.
                                                    $ticket_id = $match['ticket_id'];
                                                    update_insert('ticket_id',$ticket_id,'ticket',array(
                                                                  'status_id' => 5,// change status to in progress.
                                                                  'last_message_timestamp' => strtotime($overview->date),
                                                               ));
                                                    $new_message = false;

                                                }
                                            }
                                        }
                                    }
                                }


                                if(!$ticket_id){
                                    $ticket_id = update_insert('ticket_id','new','ticket',array(
                                                      'subject' => $this_subject,
                                                      'ticket_account_id' => $account['ticket_account_id'],
                                                      'status_id' => 2, // new !
                                                      'user_id' => $from_user_id,
                                                      'assigned_user_id'=>$reply_from_user_id,
                                                      'customer_id' => $from_user['customer_id'],
                                                      'type' => $support_type,
                                                      'last_message_timestamp' => strtotime($overview->date),
                                                   ));
                                }

                                if(!$ticket_id){
                                    echo 'Error creating ticket';
                                    continue;
                                }
                                module_ticket::mark_as_unread($ticket_id);

                                $cache = array(
                                    'from_email' =>  $from_address,
                                    'to_email' => $email_to,
                                );

                                // pull otu the email bodyu.
                                $body = $results['Data'];
                                if($results['Type']=="html"){
                                    $is_html = true;
                                }else{
                                    // convert body to html, so we can do wrap.
                                    $body = nl2br($body);
                                    $is_html = true;
                                }
                                // find the alt body.
                                $altbody = '';
                                if(isset($results['Alternative']) && is_array($results['Alternative'])){
                                    foreach($results['Alternative'] as $alt_id => $alt){
                                        if($alt['Type']=="text"){
                                            $altbody = $alt['Data'];
                                            break;
                                        }
                                    }
                                }

                                if(!$altbody){
                                    // should really never happen, but who knows.
                                    // edit - i think this happens with godaddy webmailer.
                                    $altbody = $body; // todo: strip any html.
                                    $altbody = preg_replace('#<br[^>]*>\n*#imsU',"\n",$altbody);
                                    $altbody = strip_tags($altbody);
                                }



                                // pass the body and altbody through a hook so we can modify it if needed.
                                // eg: for envato tickets we strip the header/footer out and check the link to see if the buyer really bought anything.
                                // run_hook(...

                                //echo "<hr>$body<hr>$altbody<hr><br><br><br>";
                                // save the message!
                                $ticket_message_id = update_insert('ticket_message_id','new','ticket_message',array(
                                                                     'ticket_id' => $ticket_id,
                                                                      'message_id' => $message_id,
                                                                     'content' => $altbody,
                                                                     // save html content later on.
                                                                     'htmlcontent' => $body,
                                                                     'message_time' => strtotime($overview->date),
                                                                     'message_type_id' => $message_type_id, // from a support user.
                                                                     'from_user_id' => $from_user_id,
                                                                     'to_user_id' => $to_user_id,
                                                                      'cache' => serialize($cache),
                                ));

                                if(isset($results['Related'])){
                                    foreach($results['Related'] as $related){
                                        if(isset($related['FileName']) && $related['FileName']){
                                            // save as attachment against this email.
                                            $attachment_id = update_insert('ticket_message_attachment_id','new','ticket_message_attachment',array(
                                                                                                             'ticket_id' => $ticket_id,
                                                                                                             'ticket_message_id' => $ticket_message_id,
                                                                                                             'file_name' => $related['FileName'],
                                                                                                             'content_type' => $related['Type'].(isset($related['SubType']) ? '/'.$related['SubType'] : ''),
                                                                                                                                            ));
                                            file_put_contents('includes/plugin_ticket/attachments/'.$attachment_id.'',$related['Data']);
                                        }
                                    }
                                }
                                if(isset($results['Attachments'])){
                                    foreach($results['Attachments'] as $related){
                                        if(isset($related['FileName']) && $related['FileName']){
                                            // save as attachment against this email.
                                            $attachment_id = update_insert('ticket_message_attachment_id','new','ticket_message_attachment',array(
                                                                                                             'ticket_id' => $ticket_id,
                                                                                                             'ticket_message_id' => $ticket_message_id,
                                                                                                             'file_name' => $related['FileName'],
                                                                                                             'content_type' => $related['Type'].(isset($related['SubType']) ? '/'.$related['SubType'] : ''),
                                                                                                                                            ));
                                            file_put_contents('includes/plugin_ticket/attachments/'.$attachment_id.'',$related['Data']);
                                        }
                                    }
                                }

                                //$new_message &&
                                if(!preg_match('#failure notice#i',$this_subject)){

                                    // we don't sent ticket autoresponders when the from user and to user are teh same
                                    if($from_user_id && $to_user_id && $from_user_id == $to_user_id){

                                    }else{
                                        $created_tickets [] = $ticket_id;
                                    }

                                }

                                $parse_success = true;

                            }
                        }
                    }
                }

                if($parse_success && $account['delete']){
                    // remove email from inbox if needed.
                    imap_delete($mbox, $overview->msgno);
                }

                unlink($tmp_file);
            }

        imap_errors();
        //}
        imap_expunge($mbox);
        imap_close($mbox);
        imap_errors();

        return $created_tickets;

    }
    public function get_upgrade_sql($installed_version,$new_version){

    }
    public function get_install_sql(){
        ob_start();
        ?>


CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_account_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `website_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_user_id` int(11) NOT NULL,
  `last_message_timestamp` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `unread` tinyint(1) NOT NULL DEFAULT '1',
  `date_completed` date NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_account` (
  `ticket_account_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT '110',
  `delete` tinyint(4) NOT NULL DEFAULT '0',
  `default_user_id` int(11) NOT NULL DEFAULT '0',
  `default_customer_id` int(11) NOT NULL DEFAULT '0',
  `default_type` varchar(255) NOT NULL,
  `subject_regex` varchar(255) NOT NULL,
  `body_regex` varchar(255) NOT NULL,
  `to_regex` varchar(255) NOT NULL,
  `start_date` datetime NOT NULL,
  `secure` tinyint(4) NOT NULL DEFAULT '0',
  `imap` tinyint(4) NOT NULL DEFAULT '0',
  `search_string` varchar(255) NOT NULL,
  `mailbox` varchar(255) NOT NULL,
  `last_checked` int(11) NOT NULL DEFAULT '0',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  PRIMARY KEY (`ticket_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_message` (
  `ticket_message_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `message_id` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `htmlcontent` text NOT NULL,
  `message_time` int(11) NOT NULL,
  `message_type_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `cache` text NOT NULL,
  `status_id` int(11) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`ticket_message_id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_message_attachment` (
  `ticket_message_attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `ticket_message_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `content_type` varchar(60) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`ticket_message_attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

    <?php
// todo: add default admin permissions.

        return ob_get_clean();
    }

    public static function get_saved_responses() {
        // we use the extra module for saving canned responses for now.
        // why not? meh - use a new table later when we start with a FAQ system.
        $extra_fields = module_extra::get_extras(array('owner_table'=>'ticket_responses','owner_id'=>1));

        $responses = array();
        foreach($extra_fields as $extra){
            $responses[$extra['extra_id']] = $extra['extra_key'];
        }
        return $responses;
    }
    public static function get_saved_response($saved_response_id) {
        // we use the extra module for saving canned responses for now.
        // why not? meh - use a new table later when we start with a FAQ system.
        $extra = module_extra::get_extra($saved_response_id);
        return array(
            'saved_response_id' => $extra['extra_id'],
            'name' => $extra['extra_key'],
            'value' => $extra['extra'],
        );
    }
    public static function save_saved_response($saved_response_id,$data) {
        // we use the extra module for saving canned responses for now.
        // why not? meh - use a new table later when we start with a FAQ system.
        $extra_db = array(
            'extra' => $data['value'],
            'owner_table' => 'ticket_responses',
            'owner_id' => 1,
        );
        if(isset($data['name'])&&$data['name']){
            $extra_db['extra_key'] = $data['name'];
        }else if(!(int)$saved_response_id){
            return; // not saving correctly.
        }
        $extra_id = update_insert('extra_id',$saved_response_id,'extra',$extra_db);
        return $extra_id;
    }

    public static function get_ticket_data_access() {
        if(class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Ticket Access',array(
                                                                                                   _TICKET_ACCESS_ALL,
                                                                                                   _TICKET_ACCESS_ASSIGNED,
                                                                                                   _TICKET_ACCESS_CREATED,
                                                                                                                       ));
        }else{
            return _TICKET_ACCESS_ALL;
        }
    }

    /**
     * @static
     * @param $ticket_id
     * @return array
     *
     * return a ticket recipient ready for sending a newsletter based on the ticket id.
     *
     */
    public static function get_newsletter_recipient($ticket_id) {
        $ticket = self::get_ticket($ticket_id);
        // some other details the newsletter system might need.
        $contact = module_user::get_user($ticket['user_id']);
        $name_parts = explode(" ",preg_replace('/\s+/',' ',$contact['name']));
        $ticket['first_name'] = array_shift($name_parts);
        $ticket['last_name'] = implode(' ',$name_parts);
        $ticket['email'] = $contact['email'];
        $ticket['public_link'] = self::link_public($ticket_id);
        $ticket['ticket_number'] = self::ticket_number($ticket_id);
        if($ticket['status_id'] == 2 || $ticket['status_id'] == 3 || $ticket['status_id'] == 5){
            $ticket['pending_status'] = _l('%s out of %s tickets',ordinal($ticket['position']),$ticket['total_pending']);
        }else{
            $ticket['pending_status'] = 'ticket completed';
        }
        $ticket['_edit_link'] = self::link_open($ticket_id,false,$ticket);
        return $ticket;
    }

    private function _handle_save_ticket() {

        $ticket_data = $_POST;
        $ticket_id = (int)$_REQUEST['ticket_id'];
        // check security can user edit this ticket
        if($ticket_id>0){
            $test = self::get_ticket($ticket_id);
            if(!$test || $test['ticket_id'] != $ticket_id){
                $ticket_id = 0;
            }
        }
        // handle some security before passing if off to the save
        if(!self::can_edit_tickets()){
            // dont allow new "types" to be created
            if(isset($ticket_data['type']) && $ticket_data['type']){
                $types = self::get_types();
                $existing=false;
                foreach($types as $type){
                    if($type==$ticket_data['type']){
                        $existing=true;
                    }
                }
                if(!$existing){
                    unset($ticket_data['type']);
                }
            }
            if(isset($ticket_data['ticket_account_id']))unset($ticket_data['ticket_account_id']);
            if(isset($ticket_data['assigned_user_id']))unset($ticket_data['assigned_user_id']);
            if(isset($ticket_data['change_status_id']))unset($ticket_data['change_status_id']);
            if(isset($ticket_data['change_assigned_user_id']))unset($ticket_data['change_assigned_user_id']);
        }
        $ticket_data = array_merge(self::get_ticket($ticket_id), $ticket_data);
        if(isset($_REQUEST['mark_as_unread']) && $_REQUEST['mark_as_unread']){
            $ticket_data['unread'] = 1;
        }
        $ticket_id = $this->save_ticket($ticket_id,$ticket_data);

        // run the envato hook incase we're posting data to our sidebar bit.
        ob_start();
        handle_hook('ticket_sidebar',$ticket_id);
        ob_end_clean();

        set_message("Ticket saved successfully");
        if(isset($_REQUEST['mark_as_unread']) && $_REQUEST['mark_as_unread']){
            redirect_browser($this->link_open(false));
        }else{
            if(isset($_REQUEST['newmsg_next']) && isset($_REQUEST['next_ticket_id']) && (int)$_REQUEST['next_ticket_id']>0){
                redirect_browser($this->link_open($_REQUEST['next_ticket_id']));
            }
            redirect_browser($this->link_open($ticket_id));
        }
    }

    public static function get_unread_ticket_count() {
        $ticket_count = module_cache::time_get('ticket_unread_count');
        if($ticket_count===false){
            $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t WHERE t.unread = 1 AND t.status_id < 6 ";
            // work out what customers this user can access?
            $ticket_access = self::get_ticket_data_access();
            switch($ticket_access){
                case _TICKET_ACCESS_ALL:

                    break;
                case _TICKET_ACCESS_ASSIGNED:
                    // we only want tickets assigned to me.
                    $sql .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
                case _TICKET_ACCESS_CREATED:
                    // we only want tickets I created.
                    $sql .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
            }
            $res = query($sql);
            $ticket_count = mysql_num_rows($res);
            module_cache::time_save('ticket_unread_count',$ticket_count);
        }
        return $ticket_count;
    }


}