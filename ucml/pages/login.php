<div class="blob">
	<h2><?php echo _l('Please Login'); ?> </h2>
	<p><?php echo _l('Welcome to %s - Please Login Below',module_config::s('admin_system_name')); ?></p>

    <?php ob_start(); ?>

        <form action="" method="post">
            <input type="hidden" name="_process_login" value="true">
            <table width="100%" class="tableclass">
                <tr>
                    <th class="width1">
                        <label for="email"><?php echo _l('Username'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="email" name="email" value="<?php echo (defined('_DEMO_MODE') && _DEMO_MODE)?'admin@example.com':''; ?>" style="width:185px;" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="password"><?php echo _l('Password'); ?></label>
                    </th>
                    <td>
                        <input type="password" name="password" id="password" value="<?php echo (defined('_DEMO_MODE') && _DEMO_MODE)?'password':''; ?>" style="width:185px;" />
                    </td>
                </tr>
                <tr>
                    <th>
                        
                    </th>
                    <td>
                    <input type="submit" class="submit_button" name="login" value="<?php echo _l('Login'); ?><?php echo (defined('_DEMO_MODE') && _DEMO_MODE)?' to demo':''; ?>">
                    </td>
                </tr>
            </table>
          </form>
    <?php $login_form = ob_get_clean(); ?>

    <?php
    if($display_mode == 'mobile'){
        echo $login_form;
    }else{ ?>

      <table width="100%">
        <tr>
            <td width="65" valign="top">
            <img src="<?php echo _BASE_HREF;?>images/lock.png" alt="lock" />
            </td>
            <td>
                <?php echo $login_form;?>
            </td>
        </tr>
      </table>

    <script type="text/javascript">
        $(function(){
            $('#email')[0].focus();
            setTimeout(function(){
                if($('#email').val() != ''){
                    $('#password')[0].focus();
                }
            },100);
        });
    </script>
    <?php } ?>



</div>
