<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:20:20 
  * IP Address: 127.0.0.1
  */ print_heading('Theme settings'); ?>

<p><?php _e('This is just a basic CSS editor. Paste in CSS compatible rules over the top of defaults. Click the default value to return to that value.');?></p>

<form action="" method="post">


    <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>

    <input type="hidden" name="_config_settings_hook" value="save_config">

    <table class="tableclass tableclass_rows">
        <thead>
        <tr>
            <th>
                <?php _e('Description');?>
            </th>
            <th>
                <?php _e('CSS Property');?>
            </th>
            <th>
                <?php _e('Value');?>
            </th>
            <th>
                <?php _e('Default');?>
            </th>
        </tr>
        </thead>
        <tbody>
            <?php
            $r=1;
            $x=1;
            foreach(module_theme::get_theme_styles() as $style){
                $c=0;
                foreach($style['v'] as $k=>$v){
                    $c++;
                    ?>
                    <tr class="<?php echo $x%2?'odd':'even';?>">
                        <?php if($c==1){ ?>
                        <td rowspan="<?php echo count($style['v']);?>"><?php echo $style['d'];?></td>
                        <?php } ?>
                        <td>
                            <?php echo $k;?>
                        </td>
                        <td>
                        <?php switch($k){
                            default;
                            ?>
                                <input type="text" name="config[_theme_<?php echo htmlspecialchars($style['r'] .'_'.$k);?>]" value="<?php echo htmlspecialchars($v[0]);?>" size="60" id="s<?php echo $r;?>">
                                <?php
                            break;
                    } ?>
                        </td>
                        <td<?php if($v[0]!=$v[1])echo ' style="font-weight:bold"';?>>
                            <a href="#" onclick="$('#s<?php echo $r;?>').val('<?php echo htmlspecialchars($v[1]);?>');return false;"><?php echo htmlspecialchars($v[1]);?></a>
                        </td>
                    </tr>
                <?php
                $r++;
                }
            $x++;
            } ?>

            <tr>
                <td colspan="4" align="center">
                    <input type="submit" name="save" value="<?php _e('Save settings');?>" class="submit_button save_button">
                </td>
            </tr>
        </tbody>
    </table>

</form>

    <p><?php _e('More advanced changes can be made like normal in the /css/styles.css and /css/desktop.css files. (use Chrome or Firebug to locate the styles you wish to change)');?></p>

    <?php
$settings = array(
         array(
            'key'=>_THEME_CONFIG_PREFIX.'theme_logo',
            'default'=>'images/logo.png',
             'type'=>'text',
             'description'=>'URL for header logo',
         ),
);

module_config::print_settings_form(
     $settings
);

?>

<?php print_heading('Menu Positions'); ?>

<form action="" method="POST">

    <input type="hidden" name="_config_settings_hook" value="save_config">



</form>