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

print_heading('CRON Job / Scheduled Task');

?>
<p>
    <?php _e('The CRON Job/Scheduled Task should run at least once a day to process any background tasks for the system (like sending out newsletters). Idealy the CRON job should be setup to run every 15 minutes if you have the option, but once a day should do the trick for most people.'); ?>
</p>
<p>
   <?php _e('Please contact your hosting provider for instructions on how to set the CRON job. Here are the details you need:'); ?>
</p>

<table class="tableclass">
    <tbody>
    <tr>
        <th>Command/Script to execute:</th>
        <td>
            <?php
            exec('which php',$path);
            if(!$path){
                $path = 'php';
            }else{
                $path = $path[0];
            }
            echo $path.' '.getcwd().'/cron.php';?>
        </td>
    </tr>
    <tr>
        <th>Run every:</th>
        <td>
            15 minutes
        </td>
    </tr>
    <tr>
        <th>
            Minute:
        </th>
        <td>
            */15
        </td>
    </tr>
    <tr>
        <th>
            Hour:
        </th>
        <td>
            *
        </td>
    </tr>
    <tr>
        <th>
            Day of month:
        </th>
        <td>
            *
        </td>
    </tr>
    <tr>
        <th>
            Month:
        </th>
        <td>
            *
        </td>
    </tr>
    <tr>
        <th>
            Day of week:
        </th>
        <td>
            *
        </td>
    </tr>
    </tbody>
</table>


    <em><?php _e('Or - if you cannot setup a CRON job through your hosting provider you can use one of the many <a href="http://www.google.com.au/search?q=free+web+based+cron+job" target="_blank">free web based cron job</a> services to process the CRON job at this special URL: <br> %s <br>(or just click that special CRON job URL every now and then).',full_link('cron.php?hash='.md5(_UCM_FOLDER.' secret hash '))); ?></em>