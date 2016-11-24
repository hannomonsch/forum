<?php

require_once($CFG->dirroot . "/user/lib.php");

function xmldb_forum_install() {
	global $CFG, $DB;

    $anon_user 			= new stdClass();
    $anon_user->username 	= get_config(null, 'forum_anonuser');
    $anon_user->lastname 	= $anon_user->username;
    $anon_user->auth 		= 'manual';
    $anon_user->maildisplay = 1;
    $anon_user->mailformat = 1;
    $anon_user->maildigest = 0;
    $anon_user->autosubscribe = 0;
    $anon_user->trackforums = 0;

    // From PHP 5.0, empty() evaluates to false for objects without members
	if (!$anon_user->username || empty($anon_user->username)) {
		$anon_user->lastname = 'Anonymous';
		$anon_user->username = strtolower($anon_user->lastname);
        set_config('forum_anonuser', $anon_user->username);
    }
    
	$anon_pw = "_MyPassword".(string)mt_rand();
    $anon_user->password = $anon_pw;

    if ($DB->count_records('user', array('username'=>$anon_user->username)) == 0){
        set_config('forum_anonuser_id', user_create_user($anon_user)); 
    }else{
		$anon_user = $DB->get_record('user', array('username'=>$anon_user->username));
		set_config('forum_anonuser_id', $anon_user->id);
		update_internal_user_password($anon_user, $anon_pw);// If someone else had created this user, they're now locked out.
		$anon_user->lastname = 'Anonymous';
		$anon_user->username = strtolower($anon_user->lastname);
		$anon_user->email 	 = ''; // It might have been set.
		
		$DB->update_record('user', $anon_user);
    }
}

function xmldb_forum_install_recovery() {
	xmldb_forum_install();
}
