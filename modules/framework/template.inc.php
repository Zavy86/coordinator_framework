<?php
/**
 * Framework - Template
 *
 * @package Rasmotic\Modules\Settings
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.zavynet.org
 */
 // build html object
 $html=new HTML($module_title);
 // build navbar object
 $nav=new Nav("nav-tabs");

 $nav->setTitle(api_text("framework"));

 $nav->addItem(api_icon("fa-th-large",NULL,"test hidden-link"),"?mod=framework&scr=dashboard");

 // settings
 if(substr(SCRIPT,0,8)=="settings"){
  $nav->addItem(api_text("settings_framework"),"?mod=framework&scr=settings_framework");
 }

 // own
 if(substr(SCRIPT,0,3)=="own"){
  $nav->addItem(api_text("own_profile"),"?mod=framework&scr=own_profile");
  $nav->addItem(api_text("own_password"),"?mod=framework&scr=own_password"); /** @todo if auth is standard */
 }

 // users
 if(substr(SCRIPT,0,5)=="users"){
  // lists
  $nav->addItem(api_text("users_list"),"?mod=framework&scr=users_list");
  // template operations
  if(in_array(SCRIPT,array("users_view","users_edit")) && $_REQUEST['idUser']){
   $nav->addItem(api_text("nav-operations"),NULL,"active");
   // users view operations
   if(in_array(SCRIPT,array("users_view"))){
    if(1){ /** @todo check administrators permission */
     $nav->addSubItem(api_text("nav-operations-user_interpret"),"?mod=framework&scr=submit&act=user_interpret&idUser=".$_REQUEST['idUser'],NULL,api_text("nav-operations-user_interpret-confirm"));
     $nav->addSubSeparator();
    }
    /** @todo check permissions */
    $nav->addSubItem(api_text("nav-operations-user_edit"),"?mod=framework&scr=users_edit&idUser=".$_REQUEST['idUser']);
    $nav->addSubItem(api_text("nav-operations-user_group_add"),"?mod=framework&scr=users_view&idUser=".$_REQUEST['idUser']."&act=group_add");
   }
   // users edit operations
   if(in_array(SCRIPT,array("users_edit"))){

   }
  }else{
   // users add
   $nav->addItem(api_text("users_add"),"?mod=framework&scr=users_add");
  }
 }

 // groups
 if(substr(SCRIPT,0,6)=="groups"){
  // lists
  $nav->addItem(api_text("groups_list"),"?mod=framework&scr=groups_list");
  // template operations
  if(in_array(SCRIPT,array("groups_view","groups_edit")) && $_REQUEST['idGroup']){
   $nav->addItem(api_text("nav-operations"));
   $nav->addSubItem(api_text("groups_edit"),"?mod=framework&scr=groups_edit&idGroup=".$_REQUEST['idGroup']);
  }else{
   // users add
   $nav->addItem(api_text("groups_add"),"?mod=framework&scr=groups_edit");
  }
 }

 // sessions
 if(substr(SCRIPT,0,8)=="sessions"){
  $nav->addItem(api_text("sessions_list"),"?mod=framework&scr=sessions_list");
 }

 // modules
 if(substr(SCRIPT,0,7)=="modules"){
  $nav->addItem(api_text("modules_list"),"?mod=framework&scr=modules_list");
  $nav->addItem(api_text("modules_add"),"?mod=framework&scr=modules_add");
 }

 // add nav to html
 $html->addContent($nav->render(FALSE));
?>