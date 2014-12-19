<form action="" method="post">


    <?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:18:53 
  * IP Address: 127.0.0.1
  */
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
            <!--<th>
                <?php /*_e('Key');*/?>
            </th>-->
            <th>
                <?php _e('Description');?>
            </th>
            <th>
                <?php _e('Value');?>
            </th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($settings as $setting){ ?>
            <tr>
                <!--<td>
                    <?php /*echo $setting['key'];*/?>
                </td>-->
                <td><?php echo $setting['description'];?></td>
                <td>

                    <?php switch($setting['type']){
                        case 'number':
                        ?>
                            <input type="text" name="config[<?php echo $setting['key'];?>]" value="<?php echo htmlspecialchars(module_config::c($setting['key'],$setting['default']));?>" size="20">
                            <?php
                        break;
                        case 'text':
                        ?>
                            <input type="text" name="config[<?php echo $setting['key'];?>]" value="<?php echo htmlspecialchars(module_config::c($setting['key'],$setting['default']));?>" size="60">
                            <?php
                        break;
                        case 'textarea':
                        ?>
                            <textarea name="config[<?php echo $setting['key'];?>]" rows="6" cols="50"><?php echo htmlspecialchars(module_config::c($setting['key'],$setting['default']));?></textarea>
                            <?php
                        break;
                        case 'select':
                        ?>
                            <select name="config[<?php echo $setting['key'];?>]">
                                <option value=""><?php _e('N/A');?></option>
                                <?php foreach($setting['options'] as $key=>$val){ ?>
                                <option value="<?php echo $key;?>"<?php echo module_config::c($setting['key'],$setting['default']) == $key ? ' selected':'' ?>><?php echo htmlspecialchars($val);?></option>
                                <?php } ?>
                            </select>
                            <?php
                        break;
                        case 'checkbox':
                        ?>
                            <input type="hidden" name="config_default[<?php echo $setting['key'];?>]" value="1">
                            <input type="checkbox" name="config[<?php echo $setting['key'];?>]" value="1" <?php if(module_config::c($setting['key'])) echo ' checked'; ?>>
                            <?php
                        break;

                        }

                    if(isset($setting['help'])){
                        _h($setting['help']);
                    }
                        ?>

                </td>
            </tr>
            <?php } ?>

            <tr>
                <td colspan="3" align="center">
                    <input type="submit" name="save" value="<?php _e('Save settings');?>" class="submit_button save_button">
                </td>
            </tr>
        </tbody>
    </table>

</form>