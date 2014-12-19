<?php
/**
@author : Boris Lukov	( kcjsjn01@gmail.com )

  notice that you have to allow proper permissions to target directory and , proper permission for the command to run by shell_exec  to /etc/sudoers file

*/
error_reporting(-1);
define('REMOTE_REPOSITORY', 'git@github.com:thewebcraftsmen/ADNCSM.git');
$gitrepo = REMOTE_REPOSITORY;




define('DELETE_FILES', false);

define('EXCLUDE', serialize(array(
	'.git',
	'webroot/uploads',
	'app/config/database.php',
)));

define('TMP_DIR', '/tmp/spgd-'.md5(REMOTE_REPOSITORY).'-'.time().'/');

define('VERSION_FILE', TMP_DIR.'DEPLOYED_VERSION.txt');

/**
 * Time limit for each command.
 *
 * @var int Time in seconds
 */
define('TIME_LIMIT', 500);

/**
 * OPTIONAL
 * Backup the $TARGET_DIR into BACKUP_DIR before deployment
 *
 * @var string Full backup directory path e.g. '/tmp/'
 */
define('BACKUP_DIR', false);

// ===========================================[ Configuration end ]===

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>GIT deploy script: authorized by Sasha</title>
	<style>
body { padding: 0 1em; background: #222; color: #fff; }
h2, .error { color: #c33; }
.prompt { color: #6be234; }
.command { color: #729fcf; }
.output { color: #999; }
	</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>
<body>

<pre>

Checking the environment ...

Running as <b><?php echo trim(shell_exec('whoami')); ?></b>.

Deploying <?php /*echo REMOTE_REPOSITORY; */?> <?php echo $branch."\n"; ?>...


<?php
// The commands
$commands = array();
//$chanel = $_GET['chanel'];

define('BRANCH_DEFAULT', 'master');
$is_staging = !empty($_GET['staging']);
$port = $is_staging? '3010' : '3000';

$branch = $_GET['branch'];
if (empty($branch) || $is_staging){
	$branch = BRANCH_DEFAULT;
}
$TARGET_DIR = empty($_GET['staging'])? '/projects' : '/projects/staging';

$shell_path = '/projects/';
$app_dir = $TARGET_DIR."/$branch";


$pull_branch_name = $branch;

// ==================================================[ Deployment ]===
// $command = "cd $working_path; git fetch origin; git reset --hard; git checkout $pull_branch_name; git pull origin $pull_branch_name;";
// git clone -b my-branch git@github.com:user/myproject.git
$command = "rm -r $app_dir; cd $TARGET_DIR; git clone -b $branch $gitrepo $branch";

// =======================================[ Run the command steps ]===


	set_time_limit(TIME_LIMIT); // Reset the time limit for each command
	$tmp = array();
	
	exec($command.' 2>&1', $tmp, $return_code); // Execute the command
	// Output the result
	printf('
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
		, htmlentities(trim($command))
		, htmlentities(trim(implode("\n", $tmp)))
	);
	flush(); // Try to output everything as it happens

	// Error handling and cleanup
	$ret_2 = 1; //error
if ($return_code == 0){
	if ($_GET['restart'] == 'true'){
		$command_restart = " cd $shell_path; sudo ./boot.sh stop $app_dir/app.js ; sudo ./boot.sh start $app_dir/app.js $port"; 
//	    $command_restart = "node $app_dir/app.js";
		echo $command_restart ."<br>";
		$tmp = array();
	printf('<span class="command">restaring server...</span><div></div>');    
	exec($command_restart.' 2>&1', $tmp, $ret_2);
		echo trim(implode("\n", $tmp)) . "<br>"; 
		if ( $ret_2 != 0){
			echo '<span class="output">server restarting failed.</span>';
		}else{
			echo '<span class="output">server restarted.</span>';
		}
	}
}	

if ($return_code !== 0) {
		
		printf('
<div class="error">
Error encountered!
</div>

<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
			, htmlentities(trim($commands['cleanup']))
			, htmlentities(trim($tmp))
		);
		error_log(sprintf(
			'Deployment error! %s'
			, __FILE__
		));
		break;
	}

?>

Done.
</pre>

<hr/>
<a href="//dev1.twc.bz">development version</a> 
<a href="//staging.twc.bz">Staging version</a> 


<div>
	please contact to <a href="mailto:kcjsjn01@gmail.com">Boris Lukov</a> if you have any problems regarding deployment.
</div>
<script>
var return_code = '<?php echo $return_code?>';
var ret_2 = '<?php echo $ret_2?>';
var msg = return_code == 0?  "Server has been re-deployed successfully " : "Server has been failed to re-deployed ";
if ( ret_2 == 0) 
	msg += " with restarting App";
else
	msg += " without restarting App";

$(document).ready(function(){
	
		alert(msg);
})
</script>
</body>
</html>
