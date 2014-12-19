<?php

switch($display_mode){
    case 'mobile':
        if(class_exists('module_mobile',false)){
            module_mobile::render_start($page_title,$page);
        }
        break;
    case 'ajax':

        break;
    case 'iframe':
    case 'normal':
    default:

        ?>

        <!DOCTYPE html>
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $page_title; ?></title>

        <?php module_config::print_css();?>
        <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/desktop.css" type="text/css" />
        <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/styles.css" type="text/css" />
        <link type="text/css" href="<?php echo _BASE_HREF;?>css/smoothness/jquery-ui-1.8.2.custom.css" rel="stylesheet" />



        <script language="javascript" type="text/javascript">
            // by dtbaker.
            var ajax_search_ini = '<?php echo _l('Quick Search:'); ?>';
            var ajax_search_xhr = false;
            var ajax_search_url = '<?php echo _BASE_HREF;?>ajax.php';
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

        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-1.6.3.min.js"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-ui-1.8.6.custom.min.js"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/timepicker.js"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/cookie.js"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/javascript.js?ver=2"></script>
        <?php module_config::print_js();?>


        <!--
        Author: David Baker (dtbaker.com.au)
        10/May/2010
        -->
        <script type="text/javascript">
        $(function(){
            init_interface();
        });
        </script>


        </head>
        <body <?php if($display_mode=='iframe') echo ' style="background:#FFF;"';?>>

<?php if($display_mode=='iframe'){ ?>
<div id="iframe">
<?php }else{ ?>
<?php if(_DEBUG_MODE){
    module_debug::print_heading();
} ?>
<div id="holder">


	<div id="header">

        <div>
            <div style="position:absolute; margin-left:367px;width:293px; display:none;" id="message_popdown">
                <?php if(print_header_message()){
                    ?>
                    <script type="text/javascript">
                        $('#message_popdown').fadeIn('slow');
                        <?php if(module_config::c('header_messages_fade_out',1)){ ?>
                        $(function(){
                            setTimeout(function(){
                                $('#message_popdown').fadeOut();
                            },4000);
                        });
                        <?php } ?>
                    </script>
                        <?php
                } ?>
            </div>
        </div>


		<div id="header_logo">
            <?php if($header_logo = module_theme::get_config('theme_logo',_BASE_HREF.'images/logo.png')){ ?>
                <a href="<?php echo _BASE_HREF;?>"><img src="<?php echo htmlspecialchars($header_logo);?>" border="0" title="<?php echo htmlspecialchars(module_config::s('header_title','UCM'));?>"></a>
            <?php }else{ ?>
                <a href="<?php echo _BASE_HREF;?>"><?php echo module_config::s('header_title','UCM');?></a>
            <?php } ?>
		</div>
		<?php
		if(module_security::getcred()){
			?>
	    	<div id="profile_info">
				<?php echo module_user::link_open($_SESSION['_user_id'],true);?> <span class="sep">|</span>
                <a href="<?php echo _BASE_HREF;?>index.php?_logout=true"><?php _e('Logout');?></a>
                <div class="date"><?php echo date('l jS \of F Y'); ?></div>
			</div>
		<?php
		}
		?>

	</div>

	<div id="main_menu">
        <?php
        $menu_include_parent=false;
        $show_quick_search=true;
        if(is_file('design_menu.php'))include("design_menu.php");
        ?>
	</div>

	<div id="page_middle">
    <?php }
    ?>

		<div class="content">

                
        <?php
}
