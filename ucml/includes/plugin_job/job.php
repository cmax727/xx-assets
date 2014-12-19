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

define('_JOB_TASK_CREATION_NOT_ALLOWED','Unable to create new tasks');
define('_JOB_TASK_CREATION_REQUIRES_APPROVAL','Created tasks require admin approval');
define('_JOB_TASK_CREATION_WITHOUT_APPROVAL','Created tasks do not require approval');

define('_JOB_ACCESS_ALL','All jobs in system');
define('_JOB_ACCESS_ASSIGNED','Only jobs I am assigned to');
define('_JOB_ACCESS_CUSTOMER','Jobs from customers I have access to');

define('_TASK_DELETE_KEY','-DELETE-');

class module_job extends module_base{

	public $links;
	public $job_types;

    public $version = 2.35;
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }

	public function init(){
		$this->links = array();
		$this->job_types = array();
		$this->module_name = "job";
		$this->module_position = 17;

        module_config::register_css('job','tasks.css');


        if($this->can_i('view','Jobs')){
            $this->ajax_search_keys = array(
                _DB_PREFIX.'job' => array(
                    'plugin' => 'job',
                    'search_fields' => array(
                        'name',
                    ),
                    'key' => 'job_id',
                    'title' => _l('Job: '),
                ),
            );
            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many jobs?
                $jobs = $this->get_jobs(array('customer_id'=>$_REQUEST['customer_id']));
                $name = _l('Jobs');
                if(count($jobs)){
                    $name .= " <span class='menu_label'>".count($jobs)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"job_admin",
                    'args'=>array('job_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            $this->links[] = array(
                "name"=>"Jobs",
                "p"=>"job_admin",
                'args'=>array('job_id'=>false),
            );
        }



	}
	public function handle_hook($hook,&$calling_module=false,$show_all=false){
		switch($hook){
			case "home_alerts":
				$alerts = array();
                /*if(module_config::c('job_task_alerts',1)){
                    // find out any overdue tasks or jobs.
                    $sql = "SELECT t.*,p.name AS job_name FROM `"._DB_PREFIX."task` t ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."job` p USING (job_id) ";
                    $sql .= " WHERE t.date_due != '0000-00-00' AND t.date_due <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."' AND ((t.hours = 0 AND t.completed = 0) OR t.completed < t.hours)";
                    $tasks = qa($sql);
                    foreach($tasks as $task){
                        $alert_res = process_alert($task['date_due'], _l('Job: %s',$task['job_name']));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($task['job_id']);
                            $alert_res['name'] = $task['description'];
                            $alerts[] = $alert_res;
                        }
                    }
                }*/
                if($show_all || module_config::c('job_alerts',1)){
                    // find any jobs that are past the due date and dont have a finished date.
                    $sql = "SELECT * FROM `"._DB_PREFIX."job` p ";
                    $sql .= " WHERE p.date_due != '0000-00-00' AND p.date_due <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."' AND p.date_completed = '0000-00-00'";
                    $tasks = qa($sql);
                    foreach($tasks as $task){
                        $alert_res = process_alert($task['date_due'], _l('Job Not Completed'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($task['job_id']);
                            $alert_res['name'] = $task['name'];
                            $alerts[] = $alert_res;
                        }
                    }
				}
                if($show_all || module_config::c('job_invoice_alerts',1)){
                    // find any completed jobs that don't have an invoice.
                    $sql = "SELECT j.* FROM `"._DB_PREFIX."job` j ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."task` t USING (job_id) ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii ON t.task_id = ii.task_id ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."invoice` i ON ii.invoice_id = i.invoice_id  ";
                    $sql .= " WHERE i.invoice_id IS NULL AND (j.date_completed != '0000-00-00')";
                    $sql .= " GROUP BY j.job_id";
                    $res = qa($sql);
                    foreach($res as $r){
                        $alert_res = process_alert($r['date_completed'], _l('Please Generate Invoice'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($r['job_id']);
                            $alert_res['name'] = $r['name'];
                            $alerts[] = $alert_res;
                        }
                    }
                }
                if($show_all || module_config::c('job_renew_alerts',1)){
                    // find any jobs that have a renew date soon and have not been renewed.
                    $sql = "SELECT p.* FROM `"._DB_PREFIX."job` p ";
                    $sql .= " WHERE p.date_renew != '0000-00-00'";
                    $sql .= " AND p.date_renew <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."'";
                    $sql .= " AND (p.renew_job_id IS NULL OR p.renew_job_id = 0)";
                    $res = qa($sql);
                    foreach($res as $r){
                        $alert_res = process_alert($r['date_renew'], _l('Job Renewal Pending'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($r['job_id']);
                            $alert_res['name'] = $r['name'];
                            // work out renewal period
                            if($r['date_start'] && $r['date_start'] != '0000-00-00'){
                                $time_diff = strtotime($r['date_renew']) - strtotime($r['date_start']);
                                if($time_diff > 0){
                                    $diff_type = 'day';
                                    $days = round($time_diff / 86400);
                                    if($days >= 365){
                                        $time_diff = round($days/365,1);
                                        $diff_type = 'year';
                                    }else{
                                        $time_diff = $days;
                                    }
                                    $alert_res['name'] .= ' '._l('(%s %s renewal)',$time_diff,$diff_type);
                                }
                            }
                            $alerts[] = $alert_res;
                        }
                    }
                }
                if($show_all || module_config::c('job_approval_alerts',1)){
                    $job_task_creation_permissions = self::get_job_task_creation_permissions();
                    if($job_task_creation_permissions == _JOB_TASK_CREATION_WITHOUT_APPROVAL){

                        // find any jobs that have tasks requiring approval
                        $sql = "SELECT p.job_id,p.name, t.date_updated, COUNT(t.task_id) AS approval_count FROM `"._DB_PREFIX."job` p ";
                        $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON p.job_id = t.job_id";
                        $sql .= " WHERE t.approval_required = 1";
                        $sql .= " GROUP BY p.job_id ";
                        $res = qa($sql);
                        foreach($res as $r){
                            $alert_res = process_alert($r['date_updated'], _l('Tasks Require Approval'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($r['job_id']);
                                $alert_res['name'] = _l('%s tasks in %s',$r['approval_count'],$r['name']);
                                $alerts[] = $alert_res;
                            }
                        }
                    }
                }
				return $alerts;
				break;
        }
        return false;
    }

    public static function link_generate($job_id=false,$options=array(),$link_options=array()){

        $key = 'job_id';
        if($job_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='job';
        $options['page'] = 'job_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['job_id'] = $job_id;
        $options['module'] = 'job';
        if((int)$job_id>0){
            $data = self::get_job($job_id);
        }else{
            $data = array();
        }
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : htmlspecialchars($data['name']);
        if(isset($data['customer_id']) && $data['customer_id']>0){
            $bubble_to_module = array(
                'module' => 'customer',
                'argument' => 'customer_id',
            );
        }
        array_unshift($link_options,$options);

        if(!module_security::has_feature_access(array(
            'name' => 'Customers',
            'module' => 'customer',
            'category' => 'Customer',
            'view' => 1,
            'description' => 'view',
        ))){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
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

	public static function link_open($job_id,$full=false){
        return self::link_generate($job_id,array('full'=>$full));
    }
	public static function link_ajax_task($job_id,$full=false){
        return self::link_generate($job_id,array('full'=>$full,'arguments'=>array('_process'=>'ajax_task')));
    }


    public static function link_public($job_id,$h=false){
        if($h){
            return md5('s3cret7hash for job '._UCM_FOLDER.' '.$job_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.job/h.public/i.'.$job_id.'/hash.'.self::link_public($job_id,true));
    }


    public function external_hook($hook){

        switch($hook){
            case 'public':
                $job_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($job_id && $hash){
                    $correct_hash = $this->link_public($job_id,true);
                    if($correct_hash == $hash){
                        // all good to print a receipt for this payment.
                        $job_data = $this->get_job($job_id);

                        if($job_data){
                            module_template::init_template('external_job','{HEADER}<h2>Job Overview</h2>
Job Name: <strong>{JOB_NAME}</strong> <br/>
{PROJECT_TYPE} Name: <strong>{PROJECT_NAME}</strong> <br/>

<br/>
<h3>Task List: {TASK_PERCENT_COMPLETED}</h3> <br/>
{TASK_LIST}
<br/><br/>
{JOB_INVOICES}
','Used when displaying the external view of a job.','code');
                            // correct!
                            // load up the receipt template.
                            $template = module_template::get_template_by_key('external_job');
                            // generate the html for the task output
                            ob_start();
                            include('pages/job_public.php');
                            $public_html = ob_get_clean();
                            $job_data['task_list'] = $public_html;
                            // do we link the job name?
                            $job_data['header'] = '';
                            if($this->can_i('edit','Jobs')){
                                $job_data['header'] = '<div style="text-align: center; padding: 0 0 10px 0; font-style: italic;">You can send this page to your customer as a quote or progress update (this message will be hidden).</div>';
                            }
                            //$job_data['job_name'] = $job_data['name'];
                            $job_data['job_name'] = self::link_open($job_id,true);
                            $job_data['TASK_PERCENT_COMPLETED'] = ($job_data['total_percent_complete']>0 ? _l('(%s%% completed)',$job_data['total_percent_complete']*100) : '');

                            $job_data['job_invoices'] = '';
                            $invoices = module_invoice::get_invoices(array('job_id'=>$job_id));
                            $job_data['project_type'] = _l(module_config::c('project_name_single','Website'));
                            $website_data = module_website::get_website($job_data['website_id']);
                            $job_data['project_name'] = $website_data['name'];
                            if(count($invoices)){
                                $job_data['job_invoices'] .= '<h3>'._l('Job Invoices:').'</h3>';
                                $job_data['job_invoices'] .= '<ul>';
                                foreach($invoices as $invoice){
                                    $job_data['job_invoices'] .= '<li>';
                                    $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                                    $job_data['job_invoices'] .=  module_invoice::link_open($invoice['invoice_id'],true);
                                    $job_data['job_invoices'] .=  "<br/>";
                                    $job_data['job_invoices'] .=  _l('Total: ').dollar($invoice['total_amount'],true,$invoice['currency_id']);
                                    $job_data['job_invoices'] .=  "<br/>";
                                    $job_data['job_invoices'] .=  '<span class="';
                                    if($invoice['total_amount_due']>0){
                                        $job_data['job_invoices'] .=  'error_text';
                                    }else{
                                        $job_data['job_invoices'] .=  'success_text';
                                    }
                                    $job_data['job_invoices'] .=  '">';
                                    if($invoice['total_amount_due']>0){
                                        $job_data['job_invoices'] .=  dollar($invoice['total_amount_due'],true,$invoice['currency_id']);
                                        $job_data['job_invoices'] .=  ' '._l('due');
                                    }else{
                                        $job_data['job_invoices'] .=  _l('All paid');
                                    }
                                    $job_data['job_invoices'] .=  '</span>';
                                    $job_data['job_invoices'] .=  "<br>";
                                    // view receipts:
                                    $payments = module_invoice::get_invoice_payments($invoice['invoice_id']);
                                    if(count($payments)){
                                        $job_data['job_invoices'] .=  "<ul>";
                                        foreach($payments as $invoice_payment_id => $invoice_payment_data){
                                            $job_data['job_invoices'] .=  "<li>";
                                            $job_data['job_invoices'] .=  '<a href="'. module_invoice::link_receipt($invoice_payment_data['invoice_payment_id']) .'" target="_blank">'._l('View Receipt for payment of %s',dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id'])).'</a>';
                                            $job_data['job_invoices'] .=  "</li>";
                                        }
                                        $job_data['job_invoices'] .=  "</ul>";
                                    }
                                    $job_data['job_invoices'] .= '</li>';
                                }
                                $job_data['job_invoices'] .= '</ul>';
                            }
                            $template->assign_values($job_data);
                            $template->page_title = $job_data['name'];
                            echo $template->render('pretty_html');
                        }
                    }
                }
                break;
        }
    }


	public function process(){
		$errors=array();
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['job_id']){
            $data = self::get_job($_REQUEST['job_id']);
            if(module_form::confirm_delete('job_id',"Really delete job: ".$data['name'],self::link_open($_REQUEST['job_id']))){
                $this->delete_job($_REQUEST['job_id']);
                set_message("job deleted successfully");
                redirect_browser($this->link_open(false));
            }
		}else if("ajax_task" == $_REQUEST['_process']){

            // we are requesting editing a task.
            $job_id = (int)$_REQUEST['job_id'];
            $job = self::get_job($job_id,true);
            $job_tasks = self::get_tasks($job_id);

            if($job['job_id'] != $job_id)exit; // no permissions.
            if(!self::can_i('edit','Job Tasks'))exit; // no permissions

            if(isset($_REQUEST['delete_task_log_id']) && (int)$_REQUEST['delete_task_log_id'] > 0){

                $task_id = (int)$_REQUEST['task_id'];
                $task_log_id = (int)$_REQUEST['delete_task_log_id'];
                $sql = "DELETE FROM `"._DB_PREFIX."task_log` WHERE task_id = '$task_id' AND task_log_id = '$task_log_id' LIMIT 1";
                query($sql);
                echo 'done';


            }else if(isset($_REQUEST['update_task_order'])){

                // updating the task orders for this task..
                $task_order = (array)$_REQUEST['task_order'];
                foreach($task_order as $task_id => $new_order){
                    if((int)$new_order>0 && isset($job_tasks[$task_id])){
                        update_insert('task_id',$task_id,'task',array(
                                               'task_order' => (int)$new_order,
                                                            ));
                    }
                }
                echo 'done';
            }else{

                $task_id = (int)$_REQUEST['task_id'];
                $task_data = $job_tasks[$task_id];
                $task_editable = !($task_data['invoiced']);

                $job_task_creation_permissions = module_job::get_job_task_creation_permissions();

                // todo - load this select box in via javascript from existing one on page.
                $staff_members = module_user::get_staff_members();
                $staff_member_rel = array();
                foreach($staff_members as $staff_member){
                    $staff_member_rel[$staff_member['user_id']] = $staff_member['name'];
                }

                if(isset($_REQUEST['get_preview'])){
                    $after_task_id = $task_id; // this will put it right back where it started.
                    $previous_task_id = 0;
                    $job_tasks = self::get_tasks($job_id);
                    foreach($job_tasks as $k=>$v){
                        // find out where this new task position is!
                        if($k==$task_id){
                            $after_task_id = $previous_task_id;
                            break;
                        }
                        $previous_task_id = $k;
                    }
                    $result = array(
                        'task_id' => $task_id,
                        'after_task_id' => $after_task_id,
                        'html' => self::generate_task_preview($job_id,$job,$task_id,$task_data),
                        'summary_html' => self::generate_job_summary($job_id,$job),
                    );
                    echo json_encode($result);
                }else{
                    $show_task_numbers = (module_config::c('job_show_task_numbers',1) && $job['auto_task_numbers'] != 2);
                    ob_start();
                    include('pages/ajax_task_edit.php');
                    $result = array(
                        'task_id' => $task_id,
                        'hours' => isset($_REQUEST['hours']) ? (float)$_REQUEST['hours'] : 0,
                        'html' => ob_get_clean(),
                        //'summary_html' => self::generate_job_summary($job_id,$job),
                    );
                    echo json_encode($result);
                }
            }

            exit;
		}else if("save_job_tasks_ajax" == $_REQUEST['_process']){

            // do everything via ajax. trickery!
            // dont bother saving the job. it's already created.
            $result = $this->save_job_tasks($_REQUEST['job_id'],$_POST);

            // we now have to edit the parent DOM to reflect these changes.
            // what were we doing? adding a new task? editing an existing task?
            switch($result['status']){
                case 'created':
                    // we added a new task.
                    // add a new task to the bottom (OR MID WAY!) through the task list.
                    if((int)$result['task_id']>0){
                        ?>
                        <script type="text/javascript">
                            parent.refresh_task_preview(<?php echo (int)$result['task_id'];?>);
                            parent.clear_create_form();
                            parent.ucm.add_message('<?php _e('New task created successfully');?>');
                            parent.ucm.display_messages(true);
                        </script>
                    <?php }else{
                        set_error('New task creation failed.');
                        ?>
                        <script type="text/javascript">
                            top.location.href = '<?php echo $this->link_open($_REQUEST['job_id']);?>&added=true';
                        </script>
                    <?php
                    }
                    break;
                case 'deleted':
                    // we deleted a task.
                    set_message('Task removed successfully');
                    ?>
                    <script type="text/javascript">
                        top.location.href = '<?php echo $this->link_open($_REQUEST['job_id']);?>';
                    </script>
                    <?php
                    break;
                case 'error':
                    set_error('Something happened while trying to save a task. Unknown error.');
                    // something happened, refresh the parent browser frame
                    ?>
                    <script type="text/javascript">
                        top.location.href = '<?php echo $this->link_open($_REQUEST['job_id']);?>';
                    </script>
                    <?php
                    break;
                case 'edited':
                    // we changed a task (ie: completed?);
                    // update this task above.
                    if((int)$result['task_id']>0){
                        ?>
                        <script type="text/javascript">
                            parent.canceledittask();
                            //parent.refresh_task_preview(<?php echo (int)$result['task_id'];?>);
                            parent.ucm.add_message('<?php _e('Task saved successfully');?>');
                            parent.ucm.display_messages(true);
                        </script>
                        <?php
                    }else{
                        ?>
                        <script type="text/javascript">
                            parent.canceledittask();
                            parent.ucm.add_error('<?php _e('Unable to save task');?>');
                            parent.ucm.display_messages(true);
                        </script>
                        <?php
                    }
                    break;
                default:
                    ?>
                    <script type="text/javascript">
                        parent.ucm.add_error('<?php _e('Unable to save task. Please check required fields.');?>');
                        parent.ucm.display_messages(true);
                    </script>
                    <?php
                    break;
            }

            exit;
		}else if("save_job" == $_REQUEST['_process']){
			$job_id = $this->save_job($_REQUEST['job_id'],$_POST);

            // look for the new tasks flag.
            if(isset($_REQUEST['default_task_list_id']) && isset($_REQUEST['default_tasks_action'])){
                switch($_REQUEST['default_tasks_action']){
                    case 'insert_default':
                        if((int)$_REQUEST['default_task_list_id']>0){
                            $default = self::get_default_task($_REQUEST['default_task_list_id']);
                            $task_data = $default['task_data'];
                            $new_task_data = array('job_task' => array());
                            foreach($task_data as $task){
                                $task['job_id']=$job_id;
                                if($task['date_due'] && $task['date_due']!='0000-00-00'){
                                    $diff_time = strtotime($task['date_due']) - $task['saved_time'];
                                    $task['date_due'] = date('Y-m-d',time() + $diff_time);
                                }
                                $new_task_data['job_task'][]=$task;
                            }
                            $this->save_job_tasks($job_id,$new_task_data);
                        }
                        break;
                    case 'save_default':
                        $new_default_name = trim($_REQUEST['default_task_list_id']);
                        if($new_default_name!=''){
                            // time to save it!
                            $task_data = self::get_tasks($job_id);
                            $cached_task_data = array();
                            foreach($task_data as $task){
                                $cached_task_data[] = array(
                                    'hours' => $task['hours'],
                                    'amount' => $task['amount'],
                                    'billable' => $task['billable'],
                                    'fully_completed' => $task['fully_completed'],
                                    'description' => $task['description'],
                                    'long_description' => $task['long_description'],
                                    'date_due' => $task['date_due'],
                                    'user_id' => $task['user_id'],
                                    'approval_required' => $task['approval_required'],
                                    'task_order' => $task['task_order'],
                                    'saved_time' => time(),
                                );
                            }
                            self::save_default_tasks((int)$_REQUEST['default_task_list_id'],$new_default_name,$cached_task_data);
                            unset($task_data);
                        }
                        break;
                }
            }

            // check if we are generating any renewals
            if(isset($_REQUEST['generate_renewal']) && $_REQUEST['generate_renewal'] > 0){
                $job = $this->get_job($job_id);
                if(strtotime($job['date_renew']) <= strtotime('+'.module_config::c('alert_days_in_future',5).' days')){
                    // /we are allowed to renew.
                    unset($job['job_id']);
                    // work out the difference in start date and end date and add that new renewl date to the new order.
                    $time_diff = strtotime($job['date_renew']) - strtotime($job['date_start']);
                    if($time_diff > 0){
                        // our renewal date is something in the future.
                        if(!$job['date_start'] || $job['date_start'] == '0000-00-00'){
                            set_message('Please set a job start date before renewing');
                            redirect_browser($this->link_open($job_id));
                        }
                        // work out the next renewal date.
                        $new_renewal_date = date('Y-m-d',strtotime($job['date_renew'])+$time_diff);

                        $job['date_start'] = $job['date_renew'];
                        $job['date_due'] = $job['date_renew'];
                        $job['date_renew'] = $new_renewal_date;
                        $job['status'] = module_config::s('job_status_default','New');
                        $job['date_completed'] = '';
                        // todo: copy the "more" listings over to the new job
                        // todo: copy any notes across to the new listing.

                        $new_job_id = $this->save_job('new',$job);
                        if($new_job_id){
                            // now we create the tasks
                            $tasks = $this->get_tasks($job_id);
                            foreach($tasks as $task){
                                unset($task['task_id']);
                                //$task['completed'] = 0;
                                $task['job_id'] = $new_job_id;
                                $task['date_due'] = $job['date_due'];
                                update_insert('task_id','new','task',$task);
                            }
                            // link this up with the old one.
                            update_insert('job_id',$job_id,'job',array('renew_job_id'=>$new_job_id));
                        }
                        set_message("Job renewed successfully");
                        redirect_browser($this->link_open($new_job_id));
                    }
                }
            }

            set_message("Job saved successfully");
            redirect_browser($this->link_open($job_id));


		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_jobs($search=array()){
		// limit based on customer id
		/*if(!isset($_REQUEST['customer_id']) || !(int)$_REQUEST['customer_id']){
			return array();
		}*/
		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT u.*,u.job_id AS id ";
        $sql .= ", u.name AS name ";
        $sql .= ", c.customer_name ";
        $sql .= ", w.name AS website_name";// for export
        $sql .= ", us.name AS staff_member";// for export
        $from = " FROM `"._DB_PREFIX."job` u ";
        $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
        $from .= " LEFT JOIN `"._DB_PREFIX."website` w ON u.website_id = w.website_id"; // for export
        $from .= " LEFT JOIN `"._DB_PREFIX."user` us ON u.user_id = us.user_id"; // for export
		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' "; //OR ";
			//$where .= " u.url LIKE '%$str%'  ";
			$where .= ' ) ';
		}
        foreach(array('customer_id','website_id','renew_job_id','status') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
		$group_order = ' GROUP BY u.job_id ORDER BY u.name';


        switch(self::get_job_access_permissions()){
            case _JOB_ACCESS_ALL:

                break;
            case _JOB_ACCESS_ASSIGNED:
                // only assigned jobs!
                $from .= " LEFT JOIN `"._DB_PREFIX."task` t ON u.job_id = t.job_id ";
                $where .= " AND (u.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
            case _JOB_ACCESS_CUSTOMER:
                break;
        }

        // tie in with customer permissions to only get jobs from customers we can access.
        switch(module_customer::get_customer_data_access()){
            case _CUSTOMER_ACCESS_ALL:
                // all customers! so this means all jobs!
                break;
            case _CUSTOMER_ACCESS_CONTACTS:
                // we only want customers that are directly linked with the currently logged in user contact.
                if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                    // this session variable is set upon login, it holds their customer id.
                    // todo - share a user account between multiple customers!
                    //$where .= " AND c.customer_id IN (SELECT customer_id FROM )";
                    $where .= " AND u.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                }
                break;
            case _CUSTOMER_ACCESS_TASKS:
                // only customers who have a job that I have a task under.
                // this is different to "assigned jobs" Above
                // this will return all jobs for a customer even if we're only assigned a single job for that customer
                // tricky!
                // copied from customer.php
                $where .= " AND u.customer_id IN ";
                $where .= " ( SELECT cc.customer_id FROM `"._DB_PREFIX."customer` cc ";
                $where .= " LEFT JOIN `"._DB_PREFIX."job` jj ON cc.customer_id = jj.customer_id ";
                $where .= " LEFT JOIN `"._DB_PREFIX."task` tt ON jj.job_id = tt.job_id ";
                $where .= " WHERE (jj.user_id = ".(int)module_security::get_loggedin_id()." OR tt.user_id = ".(int)module_security::get_loggedin_id().")";
                $where .= " )";

                break;
        }

		$sql = $sql . $from . $where . $group_order;
//        echo $sql;
		$result = qa($sql);
		//module_security::filter_data_set("job",$result);
		return $result;
//		return get_multiple("job",$search,"job_id","fuzzy","name");

	}
    public static function get_tasks($job_id,$order_by='task'){
        if((int)$job_id<=0)return array();
        $sql = "SELECT t.*, t.task_id AS id, i.invoice_item_id AS invoiced, i.invoice_id AS invoice_id ";
        $sql .= ", SUM(tl.hours) AS `completed` ";
        $sql .= ", inv.name AS invoice_number";
        $sql .= ", u.name AS user_name";
        $sql .= ", j.name AS job_name";
        $sql .= " FROM `"._DB_PREFIX."task` t ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task_log` tl ON t.task_id = tl.task_id";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` i ON t.task_id = i.task_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice` inv ON i.invoice_id = inv.invoice_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."user` u ON t.user_id = u.user_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON t.job_id = j.job_id";
        $sql .= " WHERE t.`job_id` = ".(int)$job_id;
        $sql .= " GROUP BY t.task_id ";
        switch($order_by){
            case 'task':
                $sql .= " ORDER BY t.task_order, t.date_due ASC ";
                break;
            case 'date':
                $sql .= " ORDER BY t.date_due ASC ";
                break;
        }
        return qa($sql);
		//return get_multiple("task",array('job_id'=>$job_id),"task_id","exact","task_id");

	}
    public static function get_invoicable_tasks($job_id){
        $sql = "SELECT t.*, t.task_id AS id ";
        $sql .= " ,SUM(tl.hours) AS `completed` ";
        $sql .= " FROM `"._DB_PREFIX."task` t ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task_log` tl ON t.task_id = tl.task_id";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` i ON t.task_id = i.task_id";
        $sql .= " WHERE t.`job_id` = ".(int)$job_id;
        $sql .= " AND i.invoice_id IS NULL ";
        //$sql .= " AND `completed` > 0 ";
        //$sql .= " AND t.`billable` != 0 ";
        //$sql .= " AND `completed` >= t.`hours` ";
        if(module_config::c('job_task_log_all_hours',1)){
            $sql .= " AND `fully_completed` = 1";
        }
        $sql .= " GROUP BY t.task_id ";
        $sql .= " ORDER BY t.task_order ASC ";
        $res = qa($sql);
        foreach($res as $rid=>$r){
            // todo: are we billing the hours worked, or the hours quoted.

            if(module_config::c('job_task_log_all_hours',1)){
                // we have to have a "fully_completed" flag before invoicing.
                if(!$r['billable']){
                    // unbillable - pass onto invoice as a blank.
                    // todo: better ! pass through hours/amount so customer can see.
                    $res[$rid]['hours'] = 0;
                    $res[$rid]['amount'] = 0;
                }
            }else{
                // old way, only completed hour tasks or "fully_completed" tasks come through.
                if(!$r['billable']){
                    // unbillable - pass onto invoice as a blank.
                    // todo: better ! pass through hours/amount so customer can see.
                    $res[$rid]['hours'] = 0;
                    $res[$rid]['amount'] = 0;
                    $res[$rid]['fully_completed'] = 1;
                }else if ($r['hours'] <= 0 && $r['amount'] <= 0 && !$r['fully_completed']){
                    // no hours, no amount, and not fully completed. skip this one.
                    unset($res[$rid]);
                }else if($r['hours'] <= 0 && $r['amount'] > 0 && !$r['fully_completed']){
                    // no hours set. but we have an amount. and we are not completed.
                    // skip.
                    unset($res[$rid]);
                }else if($r['hours'] <= 0 && $r['fully_completed']){
                    // no hours, but we are fully completed.
                    // keep this one
                }else if ($r['hours'] > 0 && ($r['completed'] <= 0 || $r['completed'] < $r['hours'])){
                    // we haven't yet completed this task based on the hours.
                    unset($res[$rid]);
                }
            }
        }
        return $res;
		//return get_multiple("task",array('job_id'=>$job_id),"task_id","exact","task_id");

	}
    public static function get_tasks_todo(){

        // find all the tasks that are due for completion
        // sorted by due date.
        $sql = "SELECT ";
        $sql .= " SUM(tl.hours) AS `hours_completed` ";
        $sql .= " ,t.* ";
        $sql .= " FROM `"._DB_PREFIX."task` t ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task_log` tl ON t.task_id = tl.task_id";
        $sql .= " WHERE t.date_due != '0000-00-00' ";
        //$sql .= " AND ((t.hours = 0 AND `completed` = 0) OR `completed` < t.hours)";
        if(module_config::c('job_task_log_all_hours',1)){
            // tasks have to have a 'fully_completed' before they are done.
            $sql .= " AND t.fully_completed = 0";
        }
        $sql .= " GROUP BY t.task_id ";
        $sql .= " ORDER BY t.date_due ASC ";
        $sql .= " LIMIT ".(int)module_config::c('todo_list_limit',6);
        $tasks = qa($sql);
        foreach($tasks as $task_id => $task){

            if(module_config::c('job_task_log_all_hours',1)){
                // tasks have to have a 'fully_completed' before they are done.

            }else{
                // old way. based on logged hours:
                if( ($task['hours'] <= 0 && $task['fully_completed'] == 0) || ($task['hours'] > 0 && $task['hours_completed'] < $task['hours'])){
                    //keep
                }else{
                    unset($tasks[$task_id]);
                }
            }
        }
        return $tasks;

	}
    public static function get_task_log($task_id){
		return get_multiple("task_log",array('task_id'=>$task_id),"task_log_id","exact","task_log_id");

	}
    
	public static function get_job($job_id,$full=true){
        $job_id = (int)$job_id;
        if($job_id<=0){
            $job=array();
        }else{
            $job = get_single("job","job_id",$job_id);
        }
        // check permissions
        if($job && isset($job['job_id']) && $job['job_id']==$job_id){
            switch(self::get_job_access_permissions()){
                case _JOB_ACCESS_ALL:

                    break;
                case _JOB_ACCESS_ASSIGNED:
                    // only assigned jobs!
                    $has_job_access = false;
                    if($job['user_id']==module_security::get_loggedin_id()){
                        $has_job_access=true;
                        break;
                    }
                    $tasks = module_job::get_tasks($job['job_id']);
                    foreach($tasks as $task){
                        if($task['user_id']==module_security::get_loggedin_id()){
                            $has_job_access=true;
                            break;
                        }
                    }
                    if(!$has_job_access){
                        $job = array();
                    }
                    break;
                case _JOB_ACCESS_CUSTOMER:
                    // tie in with customer permissions to only get jobs from customers we can access.
                    $customers = module_customer::get_customers();
                    $has_job_access = false;
                    foreach($customers as $customer){
                        if($customer['customer_id']==$job['customer_id']){
                            $has_job_access = true;
                            break;
                        }
                    }
                    if(!$has_job_access){
                        $job=array();
                    }
                    break;
            }
        }
        if(!$full)return $job;
        if(!$job){
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
            $job = array(
                'job_id' => 'new',
                'customer_id' => $customer_id,
                'website_id' => (isset($_REQUEST['website_id'])? $_REQUEST['website_id'] : 0),
                'hourly_rate' => module_config::c('hourly_rate',60),
                'name' => '',
                'date_start' => date('Y-m-d'),
                'date_due' => '',
                'date_completed' => '',
                'date_renew' => '',
                'user_id' => module_security::get_loggedin_id(),
                'renew_job_id' => '',
                'status'  => module_config::s('job_status_default','New'),
                'type'  => module_config::s('job_type_default','Website Design'),
                'currency_id' => module_config::c('default_currency_id',1),
            );
            // some defaults from the db.
            $job['total_tax_rate'] = module_config::c('tax_percent',10);
            $job['total_tax_name'] = module_config::c('tax_name','TAX');
        }
        if($job){
            // work out total hours etc..
            $job['total_hours'] = 0;
            $job['total_hours_completed'] = 0;
            $job['total_hours_overworked'] = 0;
            $job['total_sub_amount'] = 0;
            $job['total_sub_amount_unbillable'] = 0;
            $job['total_sub_amount_invoicable'] = 0;
            $job['total_tasks_remain'] = 0;

            $job['total_amount_paid'] = 0;
            $job['total_amount_invoiced'] = 0;
            $job['total_amount_todo'] = 0;
            $job['total_amount_outstanding'] = 0;
            $job['total_amount_due'] = 0;
            $job['total_hours_remain'] = 0;
            $job['total_percent_complete'] = 0;

            if($job_id>0){
                $non_hourly_job_count = $non_hourly_job_completed = 0;
                $tasks = self::get_tasks($job['job_id']);
                foreach($tasks as $task_id => $task){
                    if(module_config::c('job_task_log_all_hours',1)){
                        // jobs have to be marked fully_completd.
                        if(!$task['fully_completed']){
                            $job['total_tasks_remain']++;
                        }
                    }else{
                        if($task['amount'] != 0 && $task['completed'] <= 0){
                            $job['total_tasks_remain']++;
                        }else if($task['hours'] > 0 && $task['completed'] < $task['hours']){
                            $job['total_tasks_remain']++;
                        }
                    }
                    $tasks[$task_id]['sum_amount'] = 0;
                    if($task['amount'] != 0){
                        // we have a custom amount for this task
                        $tasks[$task_id]['sum_amount'] = $task['amount'];
                    }
                    if($task['hours'] > 0){
                        $job['total_hours'] += $task['hours'];
                        $task_completed_hours = min($task['hours'],$task['completed']);
                        if($task['fully_completed']){
                            // hack to record that we have worked 100% of this task.
                            $task_completed_hours = $task['hours'];
                        }
                        $job['total_hours_completed'] += $task_completed_hours;
                        if($task['completed'] > $task['hours']){
                            $job['total_hours_overworked'] = $task['completed'] - $task['hours'];
                        }
                        if($task['amount'] <= 0){
                            $tasks[$task_id]['sum_amount'] = ($task['hours'] * $job['hourly_rate']);
                        }
                    }else{
                        // it's a non-hourly task.
                        // work out if it's completed or not.
                        $non_hourly_job_count++;
                        if($task['fully_completed']){
                            $non_hourly_job_completed++;
                        }
                    }
                    if(!$task['invoiced'] && $task['billable'] &&
                       (
                           module_config::c('job_task_log_all_hours',1)
                           ||
                           ($task['hours'] > 0 && $task['completed'] > 0 && $task['completed'] >= $task['hours'])
                           ||
                           ($task['hours'] <= 0 && $task['fully_completed'] )
                       )
                    ){
                        if(module_config::c('job_task_log_all_hours',1)){
                            // a task has to be marked "fully_completeD" before it will be invoiced.
                            if($task['fully_completed']){
                                $job['total_sub_amount_invoicable'] += $tasks[$task_id]['sum_amount'];
                            }
                        }else{
                            $job['total_sub_amount_invoicable'] += $tasks[$task_id]['sum_amount'];
                            //(min($task['hours'],$task['completed']) * $job['hourly_rate']);
                        }
                    }

                    if($task['billable']){
                        $job['total_sub_amount'] += $tasks[$task_id]['sum_amount'];
                    }else{
                        $job['total_sub_amount_unbillable'] += $tasks[$task_id]['sum_amount'];
                    }
                }
                $job['total_hours_remain'] = $job['total_hours'] - $job['total_hours_completed'];
                if($job['total_hours'] > 0){
                    // total hours completed. work out job task based on hours completed.
                    $job['total_percent_complete'] = round($job['total_hours_completed'] / $job['total_hours'],2);
                }else if($non_hourly_job_count>0){
                    // work out job completed rate based on $non_hourly_job_completed and $non_hourly_job_count
                    $job['total_percent_complete'] = round($non_hourly_job_completed/$non_hourly_job_count,2);
                }

                // todo: save these two values in the database so that future changes do not affect them.
                $job['total_tax'] = ($job['total_sub_amount'] * ($job['total_tax_rate'] / 100));
                $job['total_amount'] = $job['total_sub_amount'] + $job['total_tax'];
                $job['total_amount_invoicable'] = $job['total_sub_amount_invoicable'] + ($job['total_sub_amount_invoicable'] * ($job['total_tax_rate'] / 100));

                // find any invoices
                $invoices = module_invoice::get_invoices(array('job_id'=>$job_id));
                foreach($invoices as $invoice){
                    $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                    // we only ad up the invoiced tasks that are from this job
                    // an invoice could have added manually more items to it, so this would throw the price out.
                    $this_invoice = 0;
                    $invoice_items = module_invoice::get_invoice_items($invoice['invoice_id']);
                    foreach($invoice_items as $invoice_item){
                        if($invoice_item['task_id'] && isset($tasks[$invoice_item['task_id']]) && $tasks[$invoice_item['task_id']]['billable']){
                            $this_invoice += $tasks[$invoice_item['task_id']]['sum_amount'];
                        }
                    }
                    $this_invoice = ($this_invoice + ($this_invoice * ($job['total_tax_rate'] / 100)));
                    $job['total_amount_invoiced'] += $this_invoice;
                    $job['total_amount_paid'] += min($invoice['total_amount_paid'],$this_invoice);
                }
                $job['total_amount_due'] = $job['total_amount'] - $job['total_amount_paid'];
                $job['total_amount_outstanding'] = $job['total_amount_invoiced'] - $job['total_amount_paid'];


                $job['total_amount_todo'] = $job['total_amount'] -  $job['total_amount_invoiced'] - $job['total_amount_invoicable'];//$job['total_amount_paid'] -

            }


        }
		return $job;
	}
	public function save_job($job_id,$data){
        if(isset($data['website_id']) && $data['website_id']){
            $website = module_website::get_website($data['website_id']);
            $data['customer_id'] = $website['customer_id'];
        }
        if((int)$job_id>0){
            $original_job_data = self::get_job($job_id,false);
        }else{
            $original_job_data = array();
        }


		$job_id = update_insert("job_id",$job_id,"job",$data);
        if($job_id){
            $result = $this->save_job_tasks($job_id,$data);
            $check_completed = true;
            switch($result['status']){
                case 'created':
                    // we added a new task.

                    break;
                case 'deleted':
                    // we deleted a task.

                    break;
                case 'edited':
                    // we changed a task (ie: completed?);

                    break;
                default:
                    // nothing changed.
                    $check_completed = false;
                    break;
            }
            if($check_completed){
                $all_completed = true;
                $tasks = self::get_tasks($job_id);
                foreach($tasks as $task){
                    if(
                        (
                            // tasks have to have a 'fully_completed' before they are done.
                            module_config::c('job_task_log_all_hours',1) && $task['fully_completed']
                        )
                            ||
                        (
                            $task['fully_completed']
                            ||
                            ($task['hours']>0 && ($task['completed'] >= $task['hours']))
                            ||
                            ($task['hours'] <= 0 && $task['completed'] > 0)
                        )
                    ){
                        // this one is done!
                    }else{
                        $all_completed = false;
                        break;
                    }
                }
                if($all_completed){
                    if(!isset($data['date_completed']) || !$data['date_completed'] || $data['date_completed'] == '0000-00-00'){
                        update_insert("job_id",$job_id,"job",array(
                                                     'date_completed' => date('Y-m-d'),
                                                     'status' => _l('Completed'),
                                                 ));
                    }
                }else{
                    // not completed. remove compelted date.
                    update_insert("job_id",$job_id,"job",array(
                                                     'date_completed' => '0000-00-00',
                                                     'status' => module_config::s('job_status_default','New'),
                                                 ));
                }
            }
            if($original_job_data){
                // we check if the hourly rate has changed
                if(isset($data['hourly_rate']) && $data['hourly_rate'] != $original_job_data['hourly_rate']){
                    // update all the task hours.
                    $sql = "UPDATE `"._DB_PREFIX."task` SET `amount` = 0 WHERE `hours` > 0 AND job_id = ".(int)$job_id;
                    query($sql);

                }
                // check if the job assigned user id has changed.
                if(module_config::c('job_allow_staff_assignment',1)){
                    if(isset($data['user_id'])){ // && $data['user_id'] != $original_job_data['user_id']){
                        // user id has changed! update any that were the old user id.
                        $sql = "UPDATE `"._DB_PREFIX."task` SET `user_id` = ".(int)$data['user_id'].
                            " WHERE (`user_id` = ".(int)$original_job_data['user_id']." OR user_id = 0) AND job_id = ".(int)$job_id;
                        query($sql);
                    }
                }
                // check if the due date has changed.
                if(
                    isset($original_job_data['date_due']) && $original_job_data['date_due'] &&
                    isset($data['date_due']) && $data['date_due'] && $data['date_due'] != '0000-00-00' &&
                    $original_job_data['date_due'] != $data['date_due']
                ){
                    // the date has changed.
                    // update all the tasks with this new date.
                    $tasks = self::get_tasks($job_id);
                    foreach($tasks as $task){
                        if(!$task['date_due'] || $task['date_due'] == '0000-00-00'){
                            // no previously set task date. set it
                            update_insert('task_id',$task['task_id'],'task',array('date_due'=>$data['date_due']));
                        }else if($task['date_due'] == $original_job_data['date_due']){
                            // the date was the old date. do we change it?
                            // only change it on incompleted tasks.
                            $percentage = self::get_percentage($task);
                            if($percentage < 1 || (module_config::c('job_tasks_overwrite_completed_due_dates',0) && $percentage == 1)){
                                update_insert('task_id',$task['task_id'],'task',array('date_due'=>$data['date_due']));
                            }
                        }else{
                            // there's a new date
                            if(module_config::c('job_tasks_overwrite_diff_due_date',0)){
                                update_insert('task_id',$task['task_id'],'task',array('date_due'=>$data['date_due']));
                            }
                        }
                    }
                }
            }

        }
        module_extra::save_extras('job','job_id',$job_id);
		return $job_id;
	}


    private function save_job_tasks($job_id, $data) {

        $result = array(
            'status' => false,
        );

        $job_task_creation_permissions = self::get_job_task_creation_permissions();
        // check for new tasks or changed tasks.
        $tasks = self::get_tasks($job_id);
        if(isset($data['job_task']) && is_array($data['job_task'])){
            foreach($data['job_task'] as $task_id => $task_data){
                $original_task_id = $task_id;
                $task_id = (int)$task_id;
                if(!is_array($task_data))continue;
                if($task_id > 0 && !isset($tasks[$task_id])){
                    $task_id = 0; // creating a new task on this job.
                }
                if(!isset($task_data['description']) || $task_data['description'] == '' || $task_data['description'] == _TASK_DELETE_KEY){
                    if($task_id>0 && $task_data['description'] == _TASK_DELETE_KEY){
                        // remove task.
                        // but onyl remove it if it hasn't been invoiced.
                        if(isset($tasks[$task_id]) && $tasks[$task_id]['invoiced']){
                            // it has been invoiced! dont remove it.
                            set_error('Unable to remove an invoiced task');
                            $result['status'] = 'error';
                            break; // break out of loop saving tasks.
                        }else{
                            $sql = "DELETE FROM `"._DB_PREFIX."task` WHERE task_id = '$task_id' AND job_id = $job_id LIMIT 1";
                            query($sql);
                            $sql = "DELETE FROM `"._DB_PREFIX."task_log` WHERE task_id = '$task_id'";
                            query($sql);
                            $result['status'] = 'deleted';
                            $result['task_id'] = $task_id;
                        }
                    }
                    continue;
                }
                // add / save this task.
                $task_data['job_id'] = $job_id;
                // remove the amount of it equals the hourly rate.
                if(isset($task_data['amount']) && $task_data['amount'] > 0 && $task_data['hours'] > 0){
                    if($task_data['amount'] - ($task_data['hours'] * $data['hourly_rate']) == 0){
                        unset($task_data['amount']);
                    }
                }
                // check if we haven't unticked a non-hourly task
                if(isset($task_data['fully_completed_t']) && $task_data['fully_completed_t']){
                    if(!isset($task_data['fully_completed']) || !$task_data['fully_completed']){
                        // we have unchecked that tickbox
                        $task_data['fully_completed'] = 0;
                    }else{
                        // we checked the tickbox
                    }
                    $check_completed = true;
                }
                // check if we haven't unticked a billable task
                if(isset($task_data['billable_t']) && $task_data['billable_t'] && !isset($task_data['billable'])){
                    $task_data['billable'] = 0;
                }
                if(isset($task_data['completed']) && $task_data['completed'] > 0){
                    // check the completed date of all our tasks.
                    $check_completed = true;
                }
                if(!$task_id && isset($task_data['new_fully_completed']) && $task_data['new_fully_completed']){
                    $task_data['fully_completed'] = 1; // is this bad for set amount tasks?
                    $task_data['log_hours'] = $task_data['hours'];
                }

                // todo: move the task creation code into a public method so that the public user can add tasks to their jobs.
                if(!$task_id && module_security::is_logged_in() && !module_security::can_i('create','Job Tasks')){
                    continue; // dont allow new tasks.
                }

                // check if the user is allowed to create new tasks.

                // check the approval status of jobs
                switch($job_task_creation_permissions){
                    case _JOB_TASK_CREATION_NOT_ALLOWED:
                        if(!$task_id){
                            continue; // dont allow new tasks.
                        }
                        break;
                    case _JOB_TASK_CREATION_REQUIRES_APPROVAL:
                        $task_data['approval_required'] = 1;
                        break;
                    case _JOB_TASK_CREATION_WITHOUT_APPROVAL:
                         // no action required .
                        break;
                }

                $task_id = update_insert('task_id',$task_id,'task',$task_data); // todo - fix cross task job boundary issue. meh.
                $result['task_id'] = $task_id;
                if($task_id != $original_task_id){
                    $result['status'] = 'created';
                }else{
                    $result['status'] = 'edited';
                }

                if($task_id && isset($task_data['log_hours']) && (float)$task_data['log_hours'] > 0){
                    // we are increasing the task complete hours by the amount specified in log hours.
                    // log a new task record, and incrase the "completed" column.
                    //$original_task_data = $tasks[$task_id];
                    //$task_data['completed'] = $task_data['completed'] + $task_data['log_hours'];
                    update_insert('task_log_id','new','task_log',array(
                                                   'task_id' => $task_id,
                                                   'job_id' => $job_id,
                                                   'hours' => (float)$task_data['log_hours'],
                                                   'log_time' => time(),
                                                                 ));
                    $result['log_hours'] = $task_data['log_hours'];
                }
            }
        }

        return $result;
    }

	public static function delete_job($job_id){
		$job_id=(int)$job_id;
		if(_DEMO_MODE && $job_id == 1){
			return;
		}
		$sql = "DELETE FROM "._DB_PREFIX."job WHERE job_id = '".$job_id."' LIMIT 1";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."task WHERE job_id = '".$job_id."'";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."task_log WHERE job_id = '".$job_id."'";
		$res = query($sql);
		$sql = "UPDATE "._DB_PREFIX."job SET renew_job_id = NULL WHERE renew_job_id = '".$job_id."'";
		$res = query($sql);
        foreach(module_invoice::get_invoices(array('job_id'=>$job_id)) as $val){
            // only delete this invoice if it has no tasks left
            // it could be a combined invoice with other jobs now.
            $invoice_items = module_invoice::get_invoice_items($val['invoice_id']);
            if(!count($invoice_items)){
                module_invoice::delete_invoice($val['invoice_id']);
            }

        }
		module_note::note_delete("job",$job_id);
        module_extra::delete_extras('job','job_id',$job_id);
	}
    public function login_link($job_id){
        return module_security::generate_auto_login_link($job_id);
    }

    public static function get_statuses(){
        $sql = "SELECT `status` FROM `"._DB_PREFIX."job` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }
    public static function get_types(){
        $sql = "SELECT `type` FROM `"._DB_PREFIX."job` GROUP BY `type` ORDER BY `type`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['type']] = $r['type'];
        }
        return $statuses;
    }


    public function get_upgrade_sql($installed_version,$new_version){
        $sql = '';
        $installed_version = (string)$installed_version;
        $new_version = (string)$new_version;
        $options = array(
            '2' => array(
                '2.1' =>   'ALTER TABLE  `'._DB_PREFIX.'task` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;' .
                         'ALTER TABLE  `'._DB_PREFIX.'task_log` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;',
                '2.2' =>   'ALTER TABLE  `'._DB_PREFIX.'task` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;' .
                         'ALTER TABLE  `'._DB_PREFIX.'task_log` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;' .
                        'ALTER TABLE  `'._DB_PREFIX.'invoice` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;',
            ),
            '2.1' => array(
                '2.2' =>   'ALTER TABLE  `'._DB_PREFIX.'invoice` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;',
            ),

        );
        if(isset($options[$installed_version]) && isset($options[$installed_version][$new_version])){
            $sql = $options[$installed_version][$new_version];
        }
        $fields = get_fields('job');
        if(!isset($fields['auto_task_numbers'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'job` ADD  `auto_task_numbers` TINYINT( 1 ) NOT NULL DEFAULT  \'0\' AFTER  `user_id`;';
        }
        if(!isset($fields['currency_id'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'job` ADD  `currency_id` int(11) NOT NULL DEFAULT  \'1\' AFTER  `user_id`;';
        }
        $fields = get_fields('task');
        if(!isset($fields['long_description'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD `long_description` LONGTEXT NULL;';
        }
        if(!isset($fields['task_order'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD  `task_order` int(11) NOT NULL DEFAULT  \'0\' AFTER `approval_required`;';
        }
        return $sql;
    }
    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>job` (
  `job_id` int(11) NOT NULL auto_increment,
  `customer_id` INT(11) NULL,
  `website_id` INT(11) NULL,
  `hourly_rate` DECIMAL(10,2) NULL,
  `name` varchar(255) NOT NULL DEFAULT  '',
  `type` varchar(255) NOT NULL DEFAULT  '',
  `status` varchar(255) NOT NULL DEFAULT  '',
  `total_tax_name` varchar(20) NOT NULL DEFAULT  '',
  `total_tax_rate` DECIMAL(10,2) NULL,
  `date_start` date NOT NULL,
  `date_due` date NOT NULL,
  `date_completed` date NOT NULL,
  `date_renew` date NOT NULL,
  `renew_job_id` INT(11) NULL,
  `user_id` INT NOT NULL DEFAULT  '0',
  `auto_task_numbers` TINYINT( 1 ) NOT NULL DEFAULT  '0',
  `currency_id` INT NOT NULL DEFAULT  '1',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `<?php echo _DB_PREFIX; ?>task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NULL,
  `hours` decimal(10,2) NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0',
  `billable` tinyint(2) NOT NULL DEFAULT '1',
  `fully_completed` tinyint(2) NOT NULL DEFAULT '0',
  `description` text NULL,
  `long_description` LONGTEXT NULL,
  `date_due` date NOT NULL,
  `invoice_id` int(11) NULL,
  `user_id` INT NOT NULL DEFAULT  '0',
  `approval_required` TINYINT( 1 ) NOT NULL DEFAULT  '0',
  `task_order` INT NOT NULL DEFAULT  '0',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `<?php echo _DB_PREFIX; ?>task_log` (
  `task_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `hours` decimal(10,2) NOT NULL DEFAULT '0',
  `log_time` int(11) NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`task_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    <?php
// todo: add default admin permissions.

        return ob_get_clean();
    }

    public static function customer_id_changed($old_customer_id, $new_customer_id) {
        $old_customer_id = (int)$old_customer_id;
        $new_customer_id = (int)$new_customer_id;
        if($old_customer_id>0 && $new_customer_id>0){
            $sql = "UPDATE `"._DB_PREFIX."job` SET customer_id = ".$new_customer_id." WHERE customer_id = ".$old_customer_id;
            query($sql);
            module_invoice::customer_id_changed($old_customer_id,$new_customer_id);
            module_file::customer_id_changed($old_customer_id,$new_customer_id);
        }
    }

    public static function get_job_task_creation_permissions() {

        if(!module_security::is_logged_in()){
            //todo - option to allow guests to create tasks with approval? or not to create tasks at all.
            return _JOB_TASK_CREATION_REQUIRES_APPROVAL;
        }else if (class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Job Task Creation',array(
                _JOB_TASK_CREATION_WITHOUT_APPROVAL,
                _JOB_TASK_CREATION_REQUIRES_APPROVAL,
                _JOB_TASK_CREATION_NOT_ALLOWED,
            ));
        }else{
            return _JOB_TASK_CREATION_WITHOUT_APPROVAL; // default to all permissions.
        }
    }

    public static function get_job_access_permissions() {
        if (class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Job Data Access',array(
                _JOB_ACCESS_ALL,
                _JOB_ACCESS_ASSIGNED,
                _JOB_ACCESS_CUSTOMER,
            ));
        }else{
            return _JOB_ACCESS_ALL; // default to all permissions.
        }
    }

    public static function handle_import_tasks($data,$add_to_group){

        $import_options = unserialize(base64_decode($_REQUEST['import_options']));
        $job_id = (int)$import_options['job_id'];
        if(!$import_options || !is_array($import_options) || $job_id<=0){
            echo 'Sorry import failed. Please try again';
            exit;
        }
        $existing_tasks = self::get_tasks($job_id);
        $existing_staff = module_user::get_staff_members();


        // woo! we're doing an import.
        // make sure we have a job id


        foreach($data as $rowid => $row){
            $row['job_id'] = $job_id;
            // check for required fields
            if(!isset($row['description']) || !trim($row['description'])){
                unset($data[$rowid]);
                continue;
            }
            if(!isset($row['task_id']) || !$row['task_id']){
                $data[$rowid]['task_id'] = 0;
            }
            // make sure this task id exists in the system against this job.
            if($data[$rowid]['task_id'] > 0){
                if(!isset($existing_tasks[$data[$rowid]['task_id']])){
                    $data[$rowid]['task_id'] = 0; // create a new task.
                    // this stops them updating a task in another job.
                }
            }
            if(!$data[$rowid]['task_id'] && $row['description']){
                // search for a task based on this name. dont want duplicates in the system.
                $existing_task = get_single('task',array('job_id','description'),array($job_id,$row['description']));
                if($existing_task){
                    $data[$rowid]['task_id'] = $existing_task['task_id'];
                }
            }

            // we have to save the user_name specially.
            if(isset($row['user_name']) && $row['user_name']){
                // see if this staff member exists.
                foreach($existing_staff as $staff_member){
                    if(strtolower($staff_member['name']) == strtolower($row['user_name'])){
                        $data[$rowid]['user_id'] = $staff_member['user_id'];
                    }
                }
            }

        }
        $c=0;
        $task_data = array();
        foreach($data as $rowid => $row){
            // now save the data.

            // we specify a "log_hours" value if we are logging more hours on a specific task.
            if(isset($row['completed']) && $row['completed'] > 0 && isset($row['hours']) && $row['hours']>0){
                if($row['task_id'] == 0){
                    // we are logging hours against a new task
                    $row['log_hours'] = $row['completed'];
                }else if($row['task_id']>0){
                    // we are adjusting hours on an existing task.
                    $existing_completed_hours = $existing_tasks[$row['task_id']]['completed'];
                    if($row['completed'] > $existing_completed_hours){
                        // we are logging additional hours against the job.
                        $row['log_hours'] = $row['completed'] - $existing_completed_hours;
                    }else if($row['completed'] < $existing_completed_hours){
                        // we are removing hours on this task!
                        // tricky!!
                        $sql = "DELETE FROM `"._DB_PREFIX."task_log` WHERE task_id = ".(int)$row['task_id'];
                        query($sql);
                        $row['log_hours'] = $row['completed'];
                    }
                }
            }

            if($row['task_id']>0){
                $task_id = $row['task_id'];
            }else{
                $task_id = 'new'.$c.'new';
                $c++;
            }

            $task_data[$task_id] = $row;

            /*foreach($add_to_group as $group_id => $tf){
                module_group::add_to_group($group_id,$task_id,'task');
            }*/
            
        }

        self::save_job($job_id,array(
                                  'job_id'=>$job_id,
                                  'job_task'=>$task_data,
                               ));


    }

    public static function generate_task_preview($job_id, $job, $task_id, $task_data){

        ob_start();
        // can we edit this task?
        // if its been invoiced we cannot edit it.
        $task_editable = !($task_data['invoiced']);
        
        // todo-move this into a method so we can update it via ajax.


        $percentage = self::get_percentage($task_data);

        /*if($task_data['hours'] <= 0 && $task_data['fully_completed']){
            $percentage = 1;
        }else if ($task_data['completed'] > 0) {
            if($task_data['hours'] > 0){
                $percentage = round($task_data['completed'] / $task_data['hours'],2);
                $percentage = min(1,$percentage);
            }else{
                $percentage = 1;
            }
        }else{
            $percentage = 0;
        }*/

        $task_due_time = strtotime($task_data['date_due']);

        $show_task_numbers = (module_config::c('job_show_task_numbers',1) && $job['auto_task_numbers'] != 2);

        include('pages/ajax_task_preview.php');
        return ob_get_clean();
    }

    public static function get_default_tasks() {
        // we use the extra module for saving default task lists for now
        // why not? meh - use a new table later (similar to ticket default responses)
        $extra_fields = module_extra::get_extras(array('owner_table'=>'job_task_defaults','owner_id'=>1));
        $responses = array();
        foreach($extra_fields as $extra){
            $responses[$extra['extra_id']] = $extra['extra_key'];
        }
        return $responses;
    }
    public static function get_default_task($default_task_list_id) {
        $extra = module_extra::get_extra($default_task_list_id);
        return array(
            'default_task_list_id' => $extra['extra_id'],
            'name' => $extra['extra_key'],
            'task_data' => unserialize($extra['extra']),
        );
    }
    public static function save_default_tasks($default_task_list_id,$name,$task_data) {
        $extra_db = array(
            'extra' => serialize($task_data),
            'owner_table' => 'job_task_defaults',
            'owner_id' => 1,
        );
        if(!(int)$default_task_list_id){
            $extra_db['extra_key'] = $name; // don't update names of previous ones.
        }
        $extra_id = update_insert('extra_id',$default_task_list_id,'extra',$extra_db);
        return $extra_id;
    }

    public static function get_percentage($task_data) {

        if(!$task_data['task_id'])return 0;
        $percentage = 0;
        if(module_config::c('job_task_log_all_hours',1)){
            if($task_data['fully_completed']){
                $percentage = 1;
            }else{
                // work out percentage based on hours.
                // default to 99% if not fully_completed is ticked yet.
                if ($task_data['completed'] > 0) {
                    if($task_data['hours'] > 0){
                        $percentage = round($task_data['completed'] / $task_data['hours'],2);
                        $percentage = min(1,$percentage);
                    }
                }
                if($percentage>=1){
                    // hack for invoiced tasks. mark this as fully completed.
                    if($task_data['invoiced']){
                        update_insert('task_id',$task_data['task_id'],'task',array('fully_completed'=>1));
                        $percentage = 1;
                    }else{
                        $percentage = 0.99;
                    }
                }
            }
        }else{
            if($task_data['hours'] <= 0 && $task_data['fully_completed']){
                $percentage = 1;
            }else if ($task_data['completed'] > 0) {
                if($task_data['hours'] > 0){
                    $percentage = round($task_data['completed'] / $task_data['hours'],2);
                    $percentage = min(1,$percentage);
                }else{
                    $percentage = 1;
                }
            }
        }
        return $percentage;
    }

    public static function generate_job_summary($job_id, $job) {
        $show_task_numbers = (module_config::c('job_show_task_numbers',1) && $job['auto_task_numbers'] != 2);
        ob_start();
        include('pages/ajax_job_summary.php');
        return ob_get_clean();
    }


}