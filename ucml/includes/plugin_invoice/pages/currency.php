<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: This licence entitles you to use this application on a single installation only. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 872 861506aca0575d691626a677b73958cd
  * Envato: 646ea150-0482-4175-ae26-14effba4a0ed
  * Package Date: 2012-03-14 14:19:46 
  * IP Address: 127.0.0.1
  */

if(isset($_REQUEST['currency_id'])){
    $currency_id = (int)$_REQUEST['currency_id'];

    $currency = get_single('currency','currency_id',$currency_id);

    if(isset($_REQUEST['butdelete_currency'])){

        if(module_form::confirm_delete('currency_id','Really delete currency: '.htmlspecialchars($currency['code']))){
            delete_from_db('currency','currency_id',$currency_id);
            set_message('Currency deleted successfully');
            redirect_browser($_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'],'?') === false ? '?' : '&') . 'deleted=true');
        }

    }else if(isset($_REQUEST['save'])){
        update_insert('currency_id',$currency_id,'currency',$_POST);
        set_message('Currency saved successfully');
        //redirect_browser('?saved=true');
        redirect_browser($_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'],'?') === false ? '?' : '&') . 'saved=true');
    }

    $currency = get_single('currency','currency_id',$currency_id);
    ?>

        <form action="" method="post">
            <input type="hidden" name="currency_id" value="<?php echo $currency_id;?>">
            <input type="hidden" name="save" value="true">

            <?php print_heading('Currency');?>
            <table class="tableclass tableclass_form tableclass_full">
                <tbody>
                <tr>
                    <th class="width1"><?php _e('Code');?></th>
                    <td>
                        <input type="text" name="code" value="<?php echo isset($currency['code']) ? htmlspecialchars($currency['code']) : '';?>">
                        <?php _h('Example: USD or AUD');?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Symbol');?></th>
                    <td>
                        <input type="text" name="symbol" value="<?php echo isset($currency['symbol']) ? htmlspecialchars($currency['symbol']) : '';?>">
                        <?php _h('Example: $ or &pound;');?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Position');?></th>
                    <td>
                        <select name="location">
                            <option value="1" <?php echo isset($currency['location']) && $currency['location'] == 1 ? 'selected' : '';?>><?php _e('before');?></option>
                            <option value="0" <?php echo isset($currency['location']) && $currency['location'] == 0 ? 'selected' : '';?>><?php _e('after');?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <input type="submit" name="save" value="<?php _e('Save');?>" class="submit_button save_button">
                        <?php if($currency_id > 0){ ?>
                            <input type="submit" name="butdelete_currency" value="<?php _e('Delete');?>" class="submit_button delete_button">
                        <?php } ?>
                    </td>
                </tr>
                </tbody>
            </table>

        </form>

    <?php

}else{

    ?>


    <h2>
        <span class="button">
            <?php echo create_link("Add New","add",$_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'],'?') === false ? '?' : '&') . 'currency_id=new'); ?>
        </span>
        <?php echo _l('Currency'); ?>
    </h2>


    <form action="" method="post">

    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Code'); ?></th>
            <th><?php echo _l('Symbol'); ?></th>
            <th><?php echo _l('Example'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $c=0;
        foreach(get_multiple('currency') as $currency){ ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <a href="?currency_id=<?php echo $currency['currency_id'];?>"><?php echo htmlspecialchars($currency['code']);?></a>
                    <?php if($currency['currency_id']==module_config::c('default_currency_id',1)){
                    _e('(default)');
                } ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($currency['symbol']); ?>
                </td>
                <td>
                    <?php echo dollar(1234.56,true,$currency['currency_id']);?>
                </td>
            </tr>
        <?php } ?>
      </tbody>
    </table>
    </form>

<?php


    $currencies = array();
    foreach(get_multiple('currency','','currency_id') as $currency){
        $currencies[$currency['currency_id']] = $currency['code'] . ' '.$currency['symbol'];
    }

    $settings = array(
        array(
            'key'=>'default_currency_id',
            'default'=>'1',
            'type'=>'select',
            'description'=>'Default currency to use throughout the system',
            'options'=>$currencies,
        ),
    );

    module_config::print_settings_form(
        $settings
    );

}