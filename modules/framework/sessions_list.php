<?php
/**
 * Framework - Sessions List
 *
 * @package Coordinator\Modules\Framework
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.zavynet.org
 */
 $authorization="framework-sessions_manage";
 // include module template
 require_once(MODULE_PATH."template.inc.php");
 // set html title
 $html->setTitle(api_text("sessions_list"));
 // build grid object
 $table=new cTable(api_text("accounts_list-tr-unvalued"));
 $table->addHeader("&nbsp;",null,16);
 $table->addHeader(api_text("sessions_list-th-fullname"),null,"100%");
 $table->addHeader(api_text("sessions_list-th-idle"),"nowrap text-right");
 $table->addHeader(api_text("sessions_list-th-start"),"nowrap");
 $table->addHeader(api_text("sessions_list-th-ipAddress"),"nowrap");
 $table->addHeader(api_text("sessions_list-th-id"),"nowrap","100%");
 $table->addHeader(api_link("?mod=framework&scr=submit&act=sessions_terminate_all",api_icon("remove",api_text("sessions_list-th-terminate")),null,null,false,api_text("sessions_list-th-terminate-confirm")),"text-center",16);
 // definitions
 $users_array=array();
 // acquire sessions
 $sessions_results=$GLOBALS['database']->queryObjects("SELECT `framework__sessions`.* FROM `framework__sessions` JOIN `framework__users` ON `framework__users`.`id`=`framework__sessions`.`fkUser` ORDER BY `lastname`,`firstname`,`lastTimestamp`",$GLOBALS['debug']);
 foreach($sessions_results as $session_r){
  if(!array_key_exists($session_r->fkUser,$users_array)){
   $users_array[$session_r->fkUser]=new cUser($session_r->fkUser);
   $users_array[$session_r->fkUser]->sessions=array();
  }
  // add session to user
  $users_array[$session_r->fkUser]->sessions[]=$session_r;
 }
 // cycle all users
 foreach($users_array as $user){
  $table->addRow();
  $table->addRowField(api_image($user->avatar,null,18),null,null,"rowspan=\"".count($user->sessions)."\"");
  $table->addRowField($user->fullname,null,null,"rowspan=\"".count($user->sessions)."\"");
  // cycle all user sessions
  foreach($user->sessions as $count=>$session_r){
   if($count){$table->addRow();}
   $table->addRowField(round((time()-$session_r->lastTimestamp)/60)." min","nowrap text-right"); /** @todo fare api per timestamp difference */
   $table->addRowField(api_timestamp_format($session_r->startTimestamp,"Y-m-d H:i"),"nowrap");
   $table->addRowField($session_r->ipAddress,"nowrap");
   $table->addRowField($session_r->id,"nowrap");
   /** @todo nome decente per session destroy */
   $table->addRowField(api_link("?mod=framework&scr=submit&act=sessions_terminate&idSession=".$session_r->id,api_icon("remove",api_text("sessions_list-td-terminate")),null,null,false,api_text("sessions_list-td-terminate-confirm")));
  }
 }
 // build grid object
 $grid=new cGrid();
 $grid->addRow();
 $grid->addCol($table->render(),"col-xs-12");
 // add content to html
 $html->addContent($grid->render());
 // renderize html
 $html->render();
?>