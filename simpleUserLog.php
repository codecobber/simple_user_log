<?php

session_start();

/*
Plugin Name: Simple User Log
Description: Restrict user access for certain pages
Version: 1.0
Author: Code Cobber
Author URI: https://www.codecobber.co.uk/
*/




# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile,
	'Simple User Log',
	'1.2',
	'Code Cobber',
	'https://www.codecobber.co.uk/',
	'See users logged in',
	'simple_user_log',
	'simple_user_log_show'
);

// ===========================================

define("simple_user_log_path", GSDATAOTHERPATH."simple_log_users.json");


add_action('nav-tab', 'createNavTab', array('simple_user_log_tab', $thisfile, 'Simple User Log','overview' ) );
add_action('simple_user_log_tab', 'createSideMenu', array($thisfile, '<i class="fa fa-eye" aria-hidden="true"></i> Overview', 'overview'));



function notLoggedIn(){
	if(file_exists(constant('simple_user_log_path')) == 1){
		$myUsersfile2 = file_get_contents(constant('simple_user_log_path')) or die("Unable to open file!");
		$JUsers2 = json_decode($myUsersfile2);

		if(isset($_SESSION['sur'])){
			foreach ($JUsers2 as $jukey2 => $juvalue2){

				//remove a count value of one when user logs out
				if($juvalue2->name == $_SESSION['sur']){
					if($juvalue2->count >0){
						$juvalue2->count = $juvalue2->count -1;
						//update file
						$JUsers2 = json_encode($JUsers2,JSON_PRETTY_PRINT);
						file_put_contents(constant('simple_user_log_path'),$JUsers2) or die('Problem writing to file');
						break;
					}
				}
			}
			// close the sessions ready for a clean start
			unset($_SESSION['sur']);
			if(isset($_SESSION['surcount'])){
				unset($_SESSION['surcount']);
			}
		}
	}
}

// ===========================================

function getLoggInData(){
	//get json file contents
	if(file_exists(constant('simple_user_log_path')) == 1){
		$myUsersfile = file_get_contents(constant('simple_user_log_path')) or die("Unable to open file!");
		$JUsers = json_decode($myUsersfile);
		$userFound_flag = 0;

		foreach ($JUsers as $jukey => $juvalue) {
			if($juvalue->name != $_SESSION['sur']){
				continue;
			}
			else{
				$userFound_flag = 1;
				$juvalue->count = $juvalue->count +1;
			}
		}

		if($userFound_flag == 0){
			//if no match found the add new logged in user to file
			$new_logged_user = array("name" => $_SESSION['sur'], "count" => 1);
			array_push($JUsers,$new_logged_user);

		}
		$jenc = json_encode($JUsers,JSON_PRETTY_PRINT);
		file_put_contents(constant('simple_user_log_path'),$jenc) or die("can't write to file");
		$userFound_flag = 0;
	}

} //close getLoggInData

// ===========================================

//if user logs in then create session
$SA_user = get_cookie('GS_ADMIN_USERNAME');

if(isset($SA_user)){
	$_SESSION['sur'] = $GLOBALS['SA_user'];//store the user name
	if(!isset($_SESSION['surcount'])){
		$_SESSION['surcount'] = 1; //record user logged in count
		getLoggInData(); //only gets called once when user logs in
	}
}
else{
	notLoggedIn();
}


function simple_user_log_show(){
	//Output the current log to tab
	if(isset($_GET['overview'])){
		echo "
		<h1 style='font-size:1.5em'>Users - log</h1>
		<hr>
		<p>Shows the user login name and the number of people currently logged in under that name.</p>
		";

		if(file_exists(constant('simple_user_log_path')) == 1){
			$usersLF = file_get_contents(constant('simple_user_log_path')) or die("Unable to open file!");
			$JusersLF = json_decode($usersLF);

			foreach ($JusersLF as $jukey => $juvalue) {
					echo"<p style='border-bottom:dotted 1px #ccc9c9;padding-bottom:6px;'><b>".$juvalue->name." - ".$juvalue->count."</p>";
			}
		}
		else{
			echo "<p><b>THE LOG FILE APPEARS TO BE MISSING! </b></p>
			<p><b> . . . Creating new file now . . .</b></p>
			<p style='color:red; font-weight:bold;'>PLEASE LOG OUT AND LOG BACK IN TO UPDATE THE NEW FILE</p>";
			$newArr = array();
			$newLog = json_encode($newArr,JSON_PRETTY_PRINT);
			file_put_contents(constant('simple_user_log_path'),$newLog) or die("can't write to file");

		}


	}
}











?>
