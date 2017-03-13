<?php
/**
 * Settings - Users Password
 *
 * @package Coordinator\Modules\Settings
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.zavynet.org
 */
 // include module template
 require_once(MODULE_PATH."template.inc.php");
 // check permissions
 /** @todo check permissions */
 // set html title
 $html->setTitle(api_text("own_password-title"));
 // build profile form
 $form=new Form("?mod=settings&scr=submit&act=own_password_update","POST",null,"own_password");
 $form->addField("password","password",api_text("own_password-password"),NULL,api_text("own_password-password-placeholder"));
 $form->addField("password","password_new",api_text("own_password-password_new"),NULL,api_text("own_password-password_new-placeholder"));
 $form->addField("password","password_confirm",api_text("own_password-password_confirm"),NULL,api_text("own_password-password_confirm-placeholder"));
 $form->addControl("submit",api_text("own_password-submit"));
 $form->addControl("button",api_text("own_password-cancel"),"?mod=settings&scr=own_profile");
 // build grid object
 $grid=new Grid();
 $grid->addRow();
 $grid->addCol($form->render(),"col-xs-12");
 // add content to html
 $html->addContent($grid->render(FALSE));
 // renderize html page
 $html->render();
?>