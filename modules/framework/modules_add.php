<?php
/**
 * Framework - Module Add
 *
 * @package Coordinator\Modules\Framework
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.zavynet.org
 */
 $authorization="framework-modules_manage";
 // include module template
 require_once(MODULE_PATH."template.inc.php");
 // set html title
 $html->setTitle(api_text("modules_add"));
 // build profile form
 $form=new cForm("?mod=framework&scr=submit&act=module_add","POST",null,"modules_add");
 $form->addField("text","url",api_text("modules_add-url"),NULL,api_text("modules_add-url-placeholder"),NULL,NULL,NULL,"required");
 $form->addField("text","directory",api_text("modules_add-directory"),NULL,api_text("modules_add-directory-placeholder"),NULL,NULL,NULL,"required");
 $form->addField("radio","method",api_text("modules_add-method"),NULL,NULL,NULL,"radio-inline");
 $form->addFieldOption("git",api_text("modules_add-method-git"));
 $form->addFieldOption("zip",api_text("modules_add-method-zip"));
 $form->addControl("submit",api_text("modules_add-submit"));
 $form->addControl("button",api_text("modules_add-cancel"),"?mod=framework&scr=modules_list");
 // build grid object
 $grid=new cGrid();
 $grid->addRow();
 $grid->addCol($form->render(),"col-xs-12");
 // add content to html
 $html->addContent($grid->render());
 // jQuery script
 $jquery = <<< EOT
/* Popover Script */
$(function(){
 $("input[name='url']").change(function(){
  var url=$("input[name='url']").val();
  if(url.substr(0,4)!="http"){return FALSE;}
  var name=url.substr(url.lastIndexOf("/")+1,url.length-url.lastIndexOf("/")-5).toLowerCase().replace("coordinator-","");
  $("input[name='directory']").val(name);
  var ext=url.substr(-4).toLowerCase();
  if(ext===".git"){
   $("input[name='method'][value='git']").prop("checked", true)
  }else if(ext===".zip"){
   $("input[name='method'][value='zip']").prop("checked", true)
  }
 });
});
EOT;
 // add script to html
 $html->addScript($jquery);
 // renderize html page
 $html->render();
?>