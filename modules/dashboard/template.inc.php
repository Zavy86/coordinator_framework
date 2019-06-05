<?php
/**
 * Dashboard - Template
 *
 * @package Coordinator\Modules\Dashboard
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.zavynet.org
 */
 // build html object
 $html=new strHTML($module_name);
 // build nav object
 $nav=new strNav("nav-tabs");
 $nav->addItem(api_text("dashboard_view"),"?mod=dashboard&scr=dashboard");
 $nav->addItem(api_text("dashboard_customize"),"?mod=dashboard&scr=dashboard_customize");
 // customizations
 if(SCRIPT=="dashboard_customize"){$nav->addSubItem(api_text("nav-dashboard-add"),"?mod=dashboard&scr=dashboard_customize&act=addTile");}
 // add nav to html
 $html->addContent($nav->render(false));
?>