<?php
/**
 * Framework - Submit
 *
 * @package Coordinator\Modules\Framework
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */
// check for actions
if(!defined('ACTION')){die("ERROR EXECUTING SCRIPT: The action was not defined");}
// switch action
switch(ACTION){
 // settings
 case "settings_framework":settings_framework();break;

 // users old
 case "user_login":user_login();break;
 case "user_logout":user_logout();break;
 case "user_recovery":user_recovery();break;
 /** @todo ^ check */

 // users
 case "user_add":user_add();break;
 case "user_edit":user_edit();break;
 case "user_delete":user_deleted(TRUE);break;
 case "user_undelete":user_deleted(FALSE);break;
 case "user_group_add":user_group_add();break;
 case "user_group_remove":user_group_remove();break;

 // groups
 case "group_save":group_save();break;
 /** @todo delete */
 /** @todo undelete */

 // own
 case "own_profile_update":own_profile_update();break;
 case "own_password_update":own_password_update();break;

 // sessions
 case "sessions_terminate":sessions_terminate();break;
 case "sessions_terminate_all":sessions_terminate_all();break;

 // modules
 case "module_update_source":module_update_source();break;
 case "module_update_database":module_update_database();break;

 // default
 default:
  api_alerts_add(api_text("alert_submitFunctionNotFound",array(MODULE,SCRIPT,ACTION)),"danger");
  api_redirect("?mod=".MODULE);
}

/**
 * Settings Framework
 */
function settings_framework(){
 // acquire variables
 $r_tab=$_REQUEST['tab'];
 // definitions
 $settings_array=array();
 $availables_settings_array=array(
  /* general */
  "maintenance","title","owner",
  /* sessions */
  "sessions_authentication_method","sessions_multiple","sessions_idle_timeout",
  "sessions_ldap_hostname","sessions_ldap_dn","sessions_ldap_domain",
  "sessions_ldap_userfield","sessions_ldap_groups","sessions_ldap_cache",
  /* sendmail */
  "sendmail_from_name","sendmail_from_mail","sendmail_asynchronous","sendmail_method",
  "sendmail_smtp_hostname","sendmail_smtp_username","sendmail_smtp_encryption",
  /* users */
  "users_password_expiration",
  /* tokens */
  "token_cron"
 );

 api_dump($_REQUEST);

 // cycle all form fields and set availables
 foreach($_REQUEST as $setting=>$value){if(in_array($setting,$availables_settings_array)){$settings_array[$setting]=$value;}}

 // sendmail smtp password (save password only if change)
 if(isset($settings_array['sendmail_smtp_username'])){if($settings_array['sendmail_smtp_username']){if($_REQUEST['sendmail_smtp_password']){$settings_array['sendmail_smtp_password']=$_REQUEST['sendmail_smtp_password'];}}else{$settings_array['sendmail_smtp_password']=NULL;}}

 api_dump($settings_array);

 // cycle all settings
 foreach($settings_array as $setting=>$value){
  // buil setting query
  $query="INSERT INTO `framework_settings` (`setting`,`value`) VALUES ('".$setting."','".$value."') ON DUPLICATE KEY UPDATE `setting`='".$setting."',`value`='".$value."'";
  // execute setting query
  $GLOBALS['database']->queryExecute($query,$GLOBALS['debug']);
  api_dump($query);
 }
 // redirect
 api_alerts_add(api_text("settings_alert_settingsUpdated"),"success");
 api_redirect("?mod=framework&scr=settings_framework&tab=".$r_tab);
}






/**
 * User Authentication
 *
 * @param string $username Username (Mail address)
 * @param string $password Password
 * @return integer Account User ID or Error Code
 *                 -1 User account was not found
 *                 -2 Password does not match
 */
function user_authentication($username,$password){
 // retrieve user object
 $user_obj=$GLOBALS['database']->queryUniqueObject("SELECT * FROM `framework_users` WHERE `mail`='".$username."'",$GLOBALS['debug']);
 if(!$user_obj->id){return -1;}
 if(md5($password)!==$user_obj->password){return -2;}
 return $user_obj->id;
}

/**
 * User Login
 */
function user_login(){
 // acquire variables
 $r_username=$_REQUEST['username'];
 $r_password=$_REQUEST['password'];
 //
 api_dump($_SESSION["coordinator_session_id"],"session_id");
 api_dump($GLOBALS['session']->debug(),"session");

 // switch authentication method
 switch($GLOBALS['settings']->sessions_authentication_method){
  case "ldap":
   /** @todo ldap auth */
   break;
  default:
   // standard authentication
   $authentication_result=user_authentication($r_username,$r_password);
 }
 // check authentication result
 if($authentication_result<1){api_alerts_add(api_text("alert_authenticationFailed"),"warning");api_redirect(DIR."login.php");}
 // try to authenticate user
 $GLOBALS['session']->build($authentication_result);
 //
 api_dump($_SESSION["coordinator_session_id"],"session_id after");
 api_dump($GLOBALS['session']->debug(),"session after");
 // redirect
 api_redirect(DIR."index.php");
}

/**
 * User Logout
 */
function user_logout(){
 // destroy session  /** @todo cercare un nome decente.. */
 $GLOBALS['session']->destroy();
 // redirect
 api_redirect(DIR."index.php");
}

/**
 * User Recovery   /** @todo rename in own ?
 */
function user_recovery(){
 // acquire variables
 $r_mail=$_REQUEST['mail'];
 $r_secret=$_REQUEST['secret'];
 // retrieve user object
 $user_obj=$GLOBALS['database']->queryUniqueObject("SELECT * FROM `framework_users` WHERE `mail`='".$r_mail."'",$GLOBALS['debug']);
 // check user
 if(!$user_obj->id){api_redirect(DIR."login.php?error=userNotFound");} /** @todo sistemare error alert */
 // remove all user sessions
 $GLOBALS['database']->queryExecute("DELETE FROM `framework_sessions` WHERE `fkUser`='".$user_obj->id."'");
 // check for secret
 if(!$r_secret){
  // generate new secret code and save into database
  $f_secret=md5(date("Y-m-d H:i:s").rand(1,99999));
  $GLOBALS['database']->queryExecute("UPDATE `framework_users` SET `secret`='".$f_secret."' WHERE `id`='".$user_obj->id."'");
  $recoveryLink=URL."index.php?mod=framework&scr=submit&act=user_recovery&mail=".$r_mail."&secret=".$f_secret;
  // send recovery link
  api_sendmail($r_mail,"Coordinator password recovery",$recoveryLink); /** @todo fare mail come si deve */
  // redirect
  api_redirect(DIR."login.php?error=userRecoveryLinkSended"); /** @todo sistemare error alert */
 }else{
  // check secret code
  if($r_secret!==$user_obj->secret){api_redirect(DIR."login.php?error=userRecoverySecretError");} /** @todo sistemare error alert */
  // generate new password
  $f_password=substr(md5(date("Y-m-d H:i:s").rand(1,99999)),0,8);
  // update password and reset secret
  $GLOBALS['database']->queryExecute("UPDATE `framework_users` SET `password`='".md5($f_password)."',`secret`=NULL,`pwdTimestamp`=NULL WHERE `id`='".$user_obj->id."'");
  // send new password
  api_sendmail($r_mail,"Coordinator new password",$f_password); /** @todo fare mail come si deve */
  // redirect
  api_redirect(DIR."login.php?error=userRecoveryPasswordSended"); /** @todo sistemare error alert */
 }
}







/**
 * User Add
 */
function user_add(){
 // make password
 $v_password=substr(md5(date("Y-m-d H:i:s").rand(1,99999)),0,8);
 // build user objects
 $user=new stdClass();
 // acquire variables
 $user->mail=$_REQUEST['mail'];
 $user->firstname=$_REQUEST['firstname'];
 $user->lastname=$_REQUEST['lastname'];
 $user->localization=$_REQUEST['localization'];
 $user->timezone=$_REQUEST['timezone'];
 $user->password=md5($v_password);
 $user->enabled=1;
 $user->addTimestamp=time();
 $user->addFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($_REQUEST);
 api_dump($user);
 // update user
 $user->id=$GLOBALS['database']->queryInsert("framework_users",$user);
 // check user
 if(!$user->id){api_alerts_add(api_text("settings_alert_userError"),"danger");api_redirect("?mod=framework&scr=users_list");}
 // send password to user
 api_sendmail($user->mail,"Coordinator new user welcome",$v_password); /** @todo fare mail come si deve */
 // redirect
 api_alerts_add(api_text("settings_alert_userCreated"),"success");
 api_redirect("?mod=framework&scr=users_edit&idUser=".$user->id);
}
/**
 * User Edit
 */
function user_edit(){
 // build user objects
 $user=new stdClass();
 // acquire variables
 $user->id=$_REQUEST['idUser'];
 $user->enabled=$_REQUEST['enabled'];
 $user->mail=$_REQUEST['mail'];
 $user->firstname=$_REQUEST['firstname'];
 $user->lastname=$_REQUEST['lastname'];
 $user->localization=$_REQUEST['localization'];
 $user->timezone=$_REQUEST['timezone'];
 $user->updTimestamp=time();
 $user->updFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($_REQUEST);
 api_dump($user);
 // check
 if(!$user->id){api_alerts_add(api_text("settings_alert_userNotFound"),"danger");api_redirect("?mod=framework&scr=users_list");}
 // update user
 $GLOBALS['database']->queryUpdate("framework_users",$user);
 // redirect
 api_alerts_add(api_text("settings_alert_userUpdated"),"success");
 api_redirect("?mod=framework&scr=users_edit&idUser=".$user->id);
}
/**
 * User Deleted
 *
 * @param boolean $deleted Deleted or Undeleted
 */
function user_deleted($deleted){
 // get objects
 $user_obj=new User($_REQUEST['idUser']);
 // check
 if(!$user_obj->id){api_alerts_add(api_text("settings_alert_userNotFound"),"danger");api_redirect("?mod=framework&scr=users_list");}
 // build user query objects
 $user_qobj=new stdClass();
 $user_qobj->id=$user_obj->id;
 $user_qobj->deleted=($deleted?1:0);
 if($deleted){$user_qobj->enabled=0;}
 $user_qobj->updTimestamp=time();
 $user_qobj->updFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($_REQUEST);
 api_dump($user_obj);
 api_dump($user_qobj);
 // update user
 $GLOBALS['database']->queryUpdate("framework_users",$user_qobj);
 // alert
 if($deleted){api_alerts_add(api_text("settings_alert_userDeleted"),"warning");}
 else{api_alerts_add(api_text("settings_alert_userUndeleted"),"success");}
 // redirect
 api_redirect("?mod=framework&scr=users_view&idUser=".$user_obj->id);
}
/**
 * User Group Add
 */
function user_group_add(){
 // get objects
 $user_obj=new User($_REQUEST['idUser']);
 // check objects
 if(!$user_obj->id){api_alerts_add(api_text("settings_alert_userNotFound"),"danger");api_redirect("?mod=framework&scr=users_list");}
 // check for duplicates
 if(array_key_exists($_REQUEST['fkGroup'],$user_obj->groups_array)){api_alerts_add(api_text("settings_alert_userGroupDuplicated"),"warning");api_redirect("?mod=framework&scr=users_view&idUser=".$user_obj->id);}
 // build user join group query object
 $user_join_group_qobj=new stdClass();
 $user_join_group_qobj->fkUser=$user_obj->id;
 $user_join_group_qobj->fkGroup=$_REQUEST['fkGroup'];
 $user_join_group_qobj->main=(count($user_obj->groups_array)?0:1);
 // build user query object
 $user_qobj=new stdClass();
 $user_qobj->id=$user_obj->id;
 $user_qobj->updTimestamp=time();
 $user_qobj->updFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($_REQUEST,"_REQUEST");
 api_dump($user_obj,"user_obj");
 api_dump($user_join_group_qobj,"user_join_group_qobj");
 api_dump($user_qobj,"user_qobj");
 // insert group
 $GLOBALS['database']->queryInsert("framework_users_join_groups",$user_join_group_qobj);
 // update user
 $GLOBALS['database']->queryUpdate("framework_users",$user_qobj);
 // redirect
 api_alerts_add(api_text("settings_alert_userGroupAdded"),"success");
 api_redirect("?mod=framework&scr=users_view&idUser=".$user_obj->id);
}
/**
 * User Group Remove
 */
function user_group_remove(){
 // get objects
 $user_obj=new User($_REQUEST['idUser']);
 // check objects
 if(!$user_obj->id){api_alerts_add(api_text("settings_alert_userNotFound"),"danger");api_redirect("?mod=framework&scr=users_list");}
 // check if user is in request group
 if(!array_key_exists($_REQUEST['idGroup'],$user_obj->groups_array)){api_alerts_add(api_text("settings_alert_userGroupNotFound"),"danger");api_redirect("?mod=framework&scr=users_view&idUser=".$user_obj->id);}
 // check if request group is main for user and not only
 if(count($user_obj->groups_array)>1 && $user_obj->groups_main==$_REQUEST['idGroup']){api_alerts_add(api_text("settings_alert_userGroupError"),"danger");api_redirect("?mod=framework&scr=users_view&idUser=".$user_obj->id);}
 // build user query object
 $user_qobj=new stdClass();
 $user_qobj->id=$user_obj->id;
 $user_qobj->updTimestamp=time();
 $user_qobj->updFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($_REQUEST,"_REQUEST");
 api_dump($user_obj,"user_obj");
 api_dump($user_qobj,"user_qobj");
 // delete group
 $GLOBALS['database']->queryExecute("DELETE FROM `framework_users_join_groups` WHERE `fkUser`='".$user_obj->id."' AND `fkGroup`='".$_REQUEST['idGroup']."'");
 // update user
 $GLOBALS['database']->queryUpdate("framework_users",$user_qobj);
 // redirect
 api_alerts_add(api_text("settings_alert_userGroupRemoved"),"warning");
 api_redirect("?mod=framework&scr=users_view&idUser=".$user_obj->id);
}

/**
 * Own Profile Update
 */
function own_profile_update(){
 // build user objects
 $user=new stdClass();
 $user->id=$GLOBALS['session']->user->id;
 // acquire variables
 $user->firstname=$_REQUEST['firstname'];
 $user->lastname=$_REQUEST['lastname'];
 $user->localization=$_REQUEST['localization'];
 $user->timezone=$_REQUEST['timezone'];
 $user->updTimestamp=time();
 $user->updFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($user);
 // update user
 $GLOBALS['database']->queryUpdate("framework_users",$user);
 // upload avatar
 if(intval($_FILES['avatar']['size'])>0 && $_FILES['avatar']['error']==UPLOAD_ERR_OK){
  if(!is_dir(ROOT."uploads/framework/users")){mkdir(ROOT."uploads/framework/users",0777,TRUE);}
  if(file_exists(ROOT."uploads/framework/users/avatar_".$user->id.".jpg")){unlink(ROOT."uploads/framework/users/avatar_".$user->id.".jpg");}
  if(is_uploaded_file($_FILES['avatar']['tmp_name'])){move_uploaded_file($_FILES['avatar']['tmp_name'],ROOT."uploads/framework/users/avatar_".$user->id.".jpg");}
 }
 // redirect
 api_alerts_add(api_text("settings_alert_ownProfileUpdated"),"success");
 api_redirect("?mod=framework&scr=own_profile");
}

/**
 * Group Save
 */
function group_save(){
 // build group objects
 $group=new stdClass();
 // acquire variables
 $group->id=$_REQUEST['idGroup'];
 $group->fkGroup=$_REQUEST['fkGroup'];
 $group->name=$_REQUEST['name'];
 $group->description=$_REQUEST['description'];
 $group->updTimestamp=time();
 $group->updFkUser=$GLOBALS['session']->user->id;
 // debug
 api_dump($group);
 // check group
 if($group->id){
  // update user
  $GLOBALS['database']->queryUpdate("framework_groups",$group);
  api_alerts_add(api_text("settings_alert_groupUpdated"),"success");
 }else{
  // update user
  $GLOBALS['database']->queryInsert("framework_groups",$group);
  api_alerts_add(api_text("settings_alert_groupCreated"),"success");
 }
 // redirect
 api_redirect("?mod=framework&scr=groups_list");
}

/**
 * Own Password Update
 */
function own_password_update(){
 // retrieve user object
 $user_obj=$GLOBALS['database']->queryUniqueObject("SELECT * FROM `framework_users` WHERE `id`='".$GLOBALS['session']->user->id."'",$GLOBALS['debug']);
 // check
 if(!$user_obj->id){api_alerts_add(api_text("settings_alert_userNotFound"),"danger");api_redirect(DIR."index.php");}
 // acquire variables
 $r_password=$_REQUEST['password'];
 $r_password_new=$_REQUEST['password_new'];
 $r_password_confirm=$_REQUEST['password_confirm'];
 // check old password
 if(md5($r_password)!==$user_obj->password){api_alerts_add(api_text("settings_alert_ownPasswordIncorrect"),"danger");api_redirect("?mod=framework&scr=own_password");}
 // check new password
 if($r_password_new!==$r_password_confirm){api_alerts_add(api_text("settings_alert_ownPasswordNotMatch"),"danger");api_redirect("?mod=framework&scr=own_password");}
 if(strlen($r_password_new)<8){api_alerts_add(api_text("settings_alert_ownPasswordWeak"),"danger");api_redirect("?mod=framework&scr=own_password");}
 // check if new password is equal to oldest password
 if(md5($r_password_new)===$user_obj->password){api_alerts_add(api_text("settings_alert_ownPasswordOldest"),"danger");api_redirect("?mod=framework&scr=own_password");}
 // build user objects
 $user=new stdClass();
 $user->id=$user_obj->id;
 $user->password=md5($r_password_new);
 $user->pwdTimestamp=time();
 // debug
 api_dump($user);
 // insert user to database
 $GLOBALS['database']->queryUpdate("framework_users",$user);
 // redirect
 api_alerts_add(api_text("settings_alert_ownPasswordUpdated"),"success");
 api_redirect("?mod=framework&scr=own_profile");
}

/**
 * Sessions Terminate
 */
function sessions_terminate(){
 $idSession=$_REQUEST['idSession'];
 if(!$idSession){api_alerts_add(api_text("settings_alert_sessionNotFound"),"danger");api_redirect("?mod=framework&scr=sessions_list");}
 // delete session
 $GLOBALS['database']->queryExecute("DELETE FROM `framework_sessions` WHERE `id`='".$idSession."'");
 // redirect
 api_alerts_add(api_text("settings_alert_sessionTerminated"),"warning");
 api_redirect("?mod=framework&scr=sessions_list");
}
/**
 * Sessions Terminate All
 */
function sessions_terminate_all(){
 // delete all sessions
 $GLOBALS['database']->queryExecute("DELETE FROM `framework_sessions`");
 // redirect
 api_alerts_add(api_text("settings_alert_sessionTerminatedAll"),"warning");
 api_redirect(DIR."index.php");
}

/**
 * Module Update Source
 */
function module_update_source(){
 // disabled for localhost and 127.0.0.1
 if(in_array($_SERVER['HTTP_HOST'],array("localhost","127.0.0.1"))){api_alerts_add(api_text("settings_alert_moduleUpdatesGitLocalhost"),"danger");api_redirect("?mod=framework&scr=modules_list");}
 // acquire variables
 $module_obj=new Module($_REQUEST['module']);
 // check objects
 if(!$module_obj->module){api_alerts_add(api_text("settings_alert_moduleNotFound"),"danger");api_redirect("?mod=framework&scr=modules_list");}
 /** @todo cycle all selected modules (multiselect in table) */
 // exec shell commands
 $shell_output=exec('whoami')."@".exec('hostname').":".shell_exec("cd ".$module_obj->source_path." ; pwd ; git stash ; git stash clear ; git pull ; chmod 755 -R ./");
 // debug
 api_dump($shell_output);
 // alert
 if(strpos(strtolower($shell_output),"up-to-date")){api_alerts_add(api_text("settings_alert_moduleUpdateScourceAlready"),"success");}
 elseif(strpos(strtolower($shell_output),"abort")){api_alerts_add(api_text("settings_alert_moduleUpdatesSourceAborted"),"danger");}
 else{api_alerts_add(api_text("settings_alert_moduleUpdateScourceUpdated"),"warning");}
 // redirect
 api_redirect("?mod=framework&scr=modules_list");
}
/**
 * Module Updates Database
 */
function module_update_database(){
 // disabled for localhost and 127.0.0.1
 if(in_array($_SERVER['HTTP_HOST'],array("localhost","127.0.0.1"))){api_alerts_add(api_text("settings_alert_moduleUpdatesGitLocalhost"),"danger");api_redirect("?mod=framework&scr=modules_list");}
 /** @todo execute .sql file and update version in database */
 // alert
 api_alerts_add(api_text("settings_alert_moduleUpdateDatabaseUpdated"),"success");
 // redirect
 api_redirect("?mod=framework&scr=modules_list");
}









?>