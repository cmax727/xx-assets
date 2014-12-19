<h2><?php echo _l('Home Page'); ?></h2>

<p>
    <?php echo _l('Hi %s, and Welcome to %s', $_SESSION['_user_name'], module_config::s('admin_system_name')); ?>
</p>


<table width="100%" cellpadding="5">
    <tr>
        <td width="50%" valign="top">

            <?php if(module_security::can_user(module_security::get_loggedin_id(),'Show Dashboard Alerts')){ ?>

            <?php print_heading(array('title'=>'Your Alerts','type'=>'h3'));?>

            <table class="tableclass tableclass_rows tableclass_full tbl_fixed">
                <tbody>
                <?php
                $alerts = array();
                $results = handle_hook("home_alerts");
                if (is_array($results)) {
                    foreach ($results as $res) {
                        if (is_array($res)) {
                            foreach ($res as $r) {
                                $alerts[] = $r;
                            }
                        }
                    }
                    // sort the alerts
                    function sort_alert($a,$b){
                        return strtotime($a['date']) > strtotime($b['date']);
                    }
                    uasort($alerts,'sort_alert');
                }
                if (count($alerts)) {
                    $x = 0;
                    foreach ($alerts as $alert) {
                        ?>
                        <tr class="<?php echo ($x++ % 2) ? 'even' : 'odd'; ?>">
                            <td class="row_action">
                                <a href="<?php echo $alert['link']; ?>"><?php echo $alert['item']; ?></a>
                            </td>
                            <td>
                                <?php echo isset($alert['name']) ? $alert['name'] : ''; ?>
                            </td>
                            <td width="16%">
                                <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                <?php echo $alert['days']; ?>
                                <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                            </td>
                            <td width="16%">
                                <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                <?php echo print_date($alert['date']); ?>
                                <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td class="odd" colspan="4"><?php _e('Yay! No alerts!');?></td>
                    </tr>
                <?php  } ?>
                </tbody>
            </table>
                <?php  } ?>
        </td>
        <td valign="top">

            <?php if(module_security::can_user(module_security::get_loggedin_id(),'Show Dashboard Todo List')){ ?>

            <?php print_heading(array('title'=>'Todo List','type'=>'h3'));?>

             <table class="tableclass tableclass_rows tableclass_full">
                <tbody>
                <?php
                $todo_list = module_job::get_tasks_todo();
                $x=0;
                if(!count($todo_list)){
                    ?>
                    <tr>
                        <td>
                            <?php _e('Yay! No todo list!'); ?>
                        </td>
                    </tr>
                    <?php
                }else{
                foreach ($todo_list as $todo_item) {
                    if($todo_item['hours_completed'] > 0){
                        if($todo_item['hours'] > 0){
                            $percentage = round($todo_item['hours_completed'] / $todo_item['hours'],2);
                            $percentage = min(1,$percentage);
                        }else{
                            $percentage = 1;
                        }
                    }else{
                        $percentage = 0;
                    }
                    ?>
                    <tr class="<?php echo ($x++ % 2) ? 'even' : 'odd'; ?>">
                        <td class="row_action">
                            <a href="<?php echo module_job::link_open($todo_item['job_id']); ?>"><?php echo $todo_item['description']; ?></a>
                        </td>
                        <td width="5%">
                            <?php echo $percentage*100;?>%
                        </td>
                        <td>
                            <?php echo module_job::link_open($todo_item['job_id'],true);?>
                        </td>
                        <td width="16%">
                            <?php
                            $alert = process_alert($todo_item['date_due'],'temp');
                            ?>
                            <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                            <?php echo $alert['days']; ?>
                            <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                        </td>
                        <td width="16%">
                            <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                            <?php echo print_date($alert['date']); ?>
                            <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                        </td>
                    </tr>
                    <?php }
                }
                ?>
                </tbody>
             </table>

    <?php } ?>

        </td>
    </tr>
</table>

<?php
$calling_module='home';
handle_hook('dashboard',$calling_module);
?>

<!-- end page -->

<?php if(get_display_mode()=='mobile'){ ?>
<p>
    <a href="?display_mode=desktop"><?php _e('Switch to desktop mode');?></a>
</p>
<?php } ?>