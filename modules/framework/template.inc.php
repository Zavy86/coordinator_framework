<?php
/**
 * Framework - Template
 *
 * @package Rasmotic\Modules\Settings
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.zavynet.org
 */
 // check authorizations
 if($authorization){if(!api_checkAuthorization(MODULE,$authorization)){api_alerts_add(api_text("alert_unauthorized",array(MODULE,$authorization)),"danger");api_redirect("?mod=framework&scr=dashboard");}}

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

 // menus
 if(substr(SCRIPT,0,5)=="menus"){
  // lists
  $nav->addItem(api_text("menus_list"),"?mod=framework&scr=menus_list");
  // menu edit
  if(in_array(SCRIPT,array("menus_edit")) && $_REQUEST['idMenu']){
   $nav->addItem(api_text("nav-operations"));
   $nav->addSubItem(api_text("menus_edit"),"?mod=framework&scr=menus_edit&idMenu=".$_REQUEST['idMenu']);
  }else{
   // menu add
   $nav->addItem(api_text("menus_add"),"?mod=framework&scr=menus_edit");
  }
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
  // users view or edit
  if(in_array(SCRIPT,array("users_view","users_edit"))){
   // users view operations
   if(SCRIPT=="users_view"){  /** @todo check authorizations */
    $nav->addItem(api_text("nav-operations"),NULL,NULL,"active");
    // check for deleted
    if($user_obj->deleted){
     $nav->addSubItem(api_text("nav-operations-user_undelete"),"?mod=framework&scr=submit&act=user_undelete&idUser=".$user_obj->id,TRUE,api_text("nav-operations-user_undelete-confirm"));
    }else{
     // check superuser authorization
     if($GLOBALS['session']->user->superuser && $user_obj->id!=$GLOBALS['session']->user->id){
      $nav->addSubItem(api_text("nav-operations-user_interpret"),"?mod=framework&scr=submit&act=user_interpret&idUser=".$user_obj->id,TRUE,api_text("nav-operations-user_interpret-confirm"));
      $nav->addSubSeparator();
     }
     $nav->addSubItem(api_text("nav-operations-user_edit"),"?mod=framework&scr=users_edit&idUser=".$user_obj->id);
     if($user_obj->enabled){$nav->addSubItem(api_text("nav-operations-user_disable"),"?mod=framework&scr=submit&act=user_disable&idUser=".$user_obj->id);}
     else{$nav->addSubItem(api_text("nav-operations-user_enable"),"?mod=framework&scr=submit&act=user_enable&idUser=".$user_obj->id);}
     $nav->addSubItem(api_text("nav-operations-user_group_add"),"?mod=framework&scr=users_view&idUser=".$user_obj->id."&act=group_add");
    }
   }
   // users edit
   if(SCRIPT=="users_edit"){$nav->addItem(api_text("users_edit"),"?mod=framework&scr=users_edit");}
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
  // lists
  $nav->addItem(api_text("modules_list"),"?mod=framework&scr=modules_list");
  // module operations
  if(in_array(SCRIPT,array("modules_view")) && $_REQUEST['module']){
   $nav->addItem(api_text("nav-operations"),NULL,NULL,"active");
   // get module object
   $module_obj=new Module($_REQUEST['module']);
   // check enabled
   if($module_obj->module<>"framework"){
    if($module_obj->enabled){$nav->addSubItem(api_text("nav-operations-module_disable"),"?mod=framework&scr=submit&act=module_disable&module=".$_REQUEST['module'],TRUE,api_text("nav-operations-module_disable-confirm"));}
    else{$nav->addSubItem(api_text("nav-operations-module_enable"),"?mod=framework&scr=submit&act=module_enable&module=".$_REQUEST['module']);}
   }
   // authorizations
   if(count($module_obj->authorizations_array)){
    $nav->addSubSeparator();
    $nav->addSubHeader(api_text("nav-operations-module_authorizations"));
    $nav->addSubItem(api_text("nav-operations-module_authorizations_group_add"),"?mod=framework&scr=modules_view&act=module_authorizations_group_add&module=".$_REQUEST['module']);
    $nav->addSubItem(api_text("nav-operations-module_authorizations_reset"),"?mod=framework&scr=submit&act=module_authorizations_reset&module=".$_REQUEST['module'],TRUE,api_text("nav-operations-module_authorizations_reset-confirm"));
   }
  }else{
   // add module
   $nav->addItem(api_text("modules_add"),"?mod=framework&scr=modules_add");
  }
 }

 // add nav to html
 $html->addContent($nav->render(FALSE));
?>