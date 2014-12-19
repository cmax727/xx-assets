<?php
switch($display_mode){
    case 'iframe':
        ?>
         </div>
        </body>
        </html>
        <?php
        module_debug::push_to_parent();
        break;
    case 'mobile':
        if(class_exists('module_mobile',false)){
            module_mobile::render_stop();
        }
        break;
    case 'normal':
    default:
        ?>

        </div>
          </div>
          <div id="footer">
              &copy; <?php echo module_config::s('admin_system_name','Ultimate Client Manager'); ?>
              - <?php echo date("Y"); ?>
              - Version: <?php echo module_config::current_version(); ?>
              - Time: <?php echo microtime(true)-$start_time;?>
          </div>
        </div>
        </body>
        </html>
        <?php
        break;
}