<?

if(!getcred()){
	header("Location: index.php");
	exit;
}

$section = $_REQUEST['_process'];

switch($section){
	case "user_master":
		if($_REQUEST['butt_del']){
			delete_user($_REQUEST['user_id']);
		}else{
			$errors = save_user();
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
	case "client_master":
		if($_REQUEST['butt_del']){
			delete_client($_REQUEST['client_id']);
		}else{
			$errors = save_client();
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
	case "client_details":
		if($_REQUEST['butt_del']){
			delete_client_details($_REQUEST['client_id']);
		}else{
			$errors = save_client_detail();
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
	case "client_loan_details":
		if($_REQUEST['butt_del']){
			delete_client_loan($_REQUEST['client_id'],$_REQUEST['client_loan_id']);
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}else{
			$errors = save_client_loan();
			if(!count($errors)){
				redirect_browser($_REQUEST['_redirect']);
				exit;
			}
		}		
		break;
	case "delete_client_loan_amount":
		$errors = delete_client_loan_amount();
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
	case "delete_client_loan_note":
		$errors = delete_client_loan_note();
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
	case "lending_master":
		if($_REQUEST['butt_del']){
			delete_lending($_REQUEST['lending_id']);
		}else{
			$errors = save_lending();
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
	case "insurers_master":
		if($_REQUEST['butt_del']){
			delete_insurer($_REQUEST['insurer_id']);
		}else{
			$errors = save_insurer();
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		break;
}


			
?>
