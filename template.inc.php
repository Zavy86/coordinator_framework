<?php
/**
 * Template
 *
 * @package Coordinator
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */
 // set meta tags
 $this->setMetaTag("author","Manuel Zavatta [www.zavynet.org]");
 $this->setMetaTag("copyright","2009-".date("Y")." &copy; Coordinator [www.coordinator.it]");
 $this->setMetaTag("description","Coordinator is an Open Source Modular Framework");
 $this->setMetaTag("owner",$GLOBALS['settings']->owner);
 // add style sheets
 $this->addStylesheet(HELPERS."bootstrap/css/bootstrap-3.3.7.min.css");
 //$this->addStylesheet(HELPERS."bootstrap/css/bootstrap-3.3.7-theme.min.css"); /** @todo definire temi "giovanniani" */
 $this->addStylesheet(HELPERS."font-awesome/css/font-awesome.min.css");
 $this->addStylesheet(HELPERS."font-awesome-animation/css/font-awesome-animation.min.css");
 /** @todo add some helpders here */
 $this->addStylesheet(HELPERS."bootstrap/css/bootstrap-3.3.7-custom.css");
 // add scripts
 $this->addScript(HELPERS."jquery/jquery-1.12.0.min.js",TRUE);
 $this->addScript(HELPERS."jquery-sortable/jquery-sortable-0.9.13.min.js",TRUE);
 /** @todo add some helpders here */
 $this->addScript(HELPERS."bootstrap/js/bootstrap-3.3.7.min.js",TRUE);
 $this->addScript(HELPERS."bootstrap-filestyle/js/bootstrap-filestyle-1.2.1.min.js",TRUE);

 // build header navbar object
 $header_navbar=new Navbar($GLOBALS['settings']->title,"navbar-default navbar-fixed-top");
 $header_navbar->addNav("navclass");

 // check session
 if($GLOBALS['session']->validity){
  $header_navbar->addItem(api_icon("fa-th-large",api_text("nav-dashboard"),"faa-tada animated-hover"),"?mod=dashboards");
  // cycle all menus
  foreach(api_framework_menus(NULL) as $menu_obj){
   if($menu_obj->icon){$icon_source=api_icon($menu_obj->icon)." ";}else{$icon_source=NULL;}
   $header_navbar->addItem($icon_source.$menu_obj->label,$menu_obj->url,TRUE,NULL,NULL,NULL,$menu_obj->target);
   foreach(api_framework_menus($menu_obj->id) as $submenu_obj){
    if($submenu_obj->icon){$icon_source=api_icon($submenu_obj->icon)." ";}else{$icon_source=NULL;}
    $header_navbar->addSubItem($icon_source.$submenu_obj->label,$submenu_obj->url,TRUE,NULL,NULL,NULL,$submenu_obj->target);
   }
  }
  // account and settings
  $header_navbar->addNav("navbar-right");
  $header_navbar->addItem(api_image($GLOBALS['session']->user->avatar,NULL,20,20,FALSE,"alt='Brand'"));
  $header_navbar->addSubHeader($GLOBALS['session']->user->fullname,"text-right");
  $header_navbar->addSubItem(api_text("nav-own-profile"),"?mod=framework&scr=own_profile","text-right");
  $header_navbar->addSubSeparator();
  $header_navbar->addSubItem(api_text("nav-settings"),"?mod=framework&scr=dashboard","text-right");
  $header_navbar->addSubItem(api_text("nav-logout"),"?mod=framework&scr=submit&act=user_logout","text-right");

 }else{

 }

 // set header
 $this->setHeader($header_navbar->render(FALSE));
 // build footer grid
 $footer_grid=new Grid();
 $footer_grid->addRow();
 $footer_grid->addCol("Copyright 2009-".date("Y")." &copy; Coordinator - All Rights Reserved".($GLOBALS['debug']?" [ Queries: ".$GLOBALS['database']->query_counter." | Execution time: ~".number_format((microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"]),2)." secs ]":NULL),"col-xs-12 text-right");
 // set footer
 $this->setFooter($footer_grid->render(FALSE));

 // jQuery scripts
 $this->addScript("/* Popover Script */\n$(function(){\$(\"[data-toggle='popover']\").popover({'trigger':'hover'});});");
 $this->addScript("/* Current Row Timeout Script */\n$(function(){setTimeout(function(){\$('.currentrow').removeClass('info');},5000);});");

?>