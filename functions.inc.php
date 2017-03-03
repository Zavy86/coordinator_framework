<?php
/**
 * Functions
 *
 * @package Coordinator
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

 session_start();

 // definitions
 global $debug;
 global $develop;
 global $configuration;
 global $database;
 global $settings;
 global $session;

 // reset session logs
 $_SESSION['coordinator_logs']=NULL;

 // include configuration file
 $configuration=new stdClass();
 require_once("config.inc.php");

 // check for debug from session and parameters
 if($_SESSION['coordinator_debug']){$debug=TRUE;}
 if(isset($_GET['debug'])){
  if($_GET['debug']==1){$debug=TRUE;$_SESSION['coordinator_debug']=TRUE;}
  else{$debug=FALSE;$_SESSION['coordinator_debug']=FALSE;}
 }

 // errors configuration
 ini_set("display_errors",($debug||$develop?TRUE:FALSE));
 if($develop){error_reporting(E_ALL & ~E_NOTICE);}
 else{error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);}

 // module variables
 $r_module=$_REQUEST['mod'];
 if(!$r_module){$r_module="dashboards";}
 $r_script=$_REQUEST['scr'];
 if(!$r_script){$r_script=NULL;}
 $r_action=$_REQUEST['act'];
 if(!$r_action){$r_action=NULL;}

 // defines constants
 define('DIR',$configuration->dir);
 define('URL',(isset($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST'].$GLOBALS['configuration']->dir);
 define('ROOT',realpath(dirname(__FILE__))."/");
 define('HELPERS',DIR."helpers/");
 define('MODULE',$r_module);
 define('MODULE_PATH',ROOT."modules/".MODULE."/");
 if($r_script){define("SCRIPT",$r_script);}
 if($r_action){define("ACTION",$r_action);}

 // include classes
 require_once(ROOT."classes/database.class.php");
 require_once(ROOT."classes/settings.class.php");
 require_once(ROOT."classes/session.class.php");
 require_once(ROOT."classes/html.class.php");
 require_once(ROOT."classes/grid.class.php");
 require_once(ROOT."classes/nav.class.php");
 require_once(ROOT."classes/navbar.class.php");
 require_once(ROOT."classes/table.class.php");
 require_once(ROOT."classes/form.class.php");

 // load modules  /** @todo fare funzione */
 require_once(ROOT."modules/accounts/functions.inc.php");

 // build database instance
 $database=new Database();

 // build settings instance
 $settings=new Settings();

 // build session instance
 $session=new Session();


/**
 * Renderize a variable dump into a pre tag
 *
 * @param string $variable variable to dump
 * @param string $label dump label
 * @param API_DUMP_PRINTR|API_DUMP_VARDUMP $function dump function
 * @param string $class pre dump class
 */
 function api_dump($variable,$label=NULL,$function=API_DUMP_PRINTR,$class=NULL){
  echo "\n\n<!-- dump -->\n";
  echo "<pre class='".$class."'>\n";
  if($label<>NULL){echo "<strong>".$label."</strong><br>";}
  if(is_string($variable)){$variable=str_replace(array("<",">"),array("&lt;","&gt;"),$variable);}
  switch($function){
   case API_DUMP_PRINTR:print_r($variable);break;
   case API_DUMP_VARDUMP:var_dump($variable);break;
   default:echo $variable."\n";
  }
  echo "</pre>\n<!-- /dump -->\n\n";
 }

/**
 * api_dump contants
 *
 * @const API_DUMP_PRINTR dump with print_r()
 * @const API_DUMP_VARDUMP dump with var_dump()
 */
 define('API_DUMP_PRINTR',1);
 define('API_DUMP_VARDUMP',2);


/**
 * Redirect
 *
 * @param string $location Location URL
 */
 function api_redirect($location){
  if($GLOBALS['debug']){die(api_link($location,$location));}
  exit(header("location: ".$location));
 }


/**
* Datetime Now
*
* @return current datetime
*/
 function api_datetime_now(){
  return date("Y-m-d H:i:s");
 }


/**
* Timestamp Format
*
* @param integer $timestamp Unix timestamp
* @param string $format Date Time format (see php.net/manual/en/function.date.php)
* @return string|boolean Formatted timestamp or false
*/
 function api_timestamp_format($timestamp,$format="Y-m-d H:i:s",$timezone=NULL){
  if(!is_numeric($timestamp)){return FALSE;}
  if(!$timezone){$timezone=$GLOBALS['session']->user->timezone;}
  // build date time object
  $datetime=new DateTime("@".$timestamp);
  // set date time timezone
  $datetime->setTimeZone(new DateTimeZone($timezone));
  // return date time formatted
  return $datetime->format($format);
 }


/**
 * Parse URL to standard class                  @todo modificare nome qui e in nav class
 *
 * @param string $url URL to parse
 * @return object Parsed
 */
 function api_parse_url($url=NULL){
  // check url
  if(!$url){$url=(isset($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];}
  // build object
  $return=new stdClass();
  // parse url string into object
  foreach(parse_url($url) as $key=>$value){$return->$key=$value;}
  // parse query to array
  $return->query_array=array();
  parse_str($return->query,$return->query_array);
  // return
  return $return;
 }


/**
* Link
* @param string $url URL
* @param string $label Label
* @param string $title Title
* @param string $class CSS class
* @param booelan $popup Show popup title
* @param string $confirm Show confirm alert box
* @param string $style Style tags
* @param string $target Target window
* @param string $id Link ID or random created
* @return string link
*/
function api_link($url,$label,$title=NULL,$class=NULL,$popup=FALSE,$confirm=NULL,$style=NULL,$target="_self",$id=NULL){
 if($url==NULL){return FALSE;}
 if($id==NULL){$id="link_".rand(1,999);}
 if(substr($url,0,1)=="?"){$url="index.php".$url;}
 $return="<a id=\"".$id."\" href=\"".$url."\" class='".$class."' style=\"".$style."\"";
 if($popup && $title){
  $return.=" data-toggle='popover' data-placement='top' data-content=\"".$title."\"";
 }elseif($title){
  $return.=" title=\"".$title."\"";
 }
 if($confirm){
  $return.=" onClick=\"return confirm('".addslashes($confirm)."')\"";
 }
 $return.=" target='".$target."'>".$label."</a>";
 return $return;
}

function api_sendmail($recipient,$subject,$message){
  /** @todo fare funzione con phpmailer */
 mail($recipient,$subject,$message);
}








            /**
             * Alerts Add
             *
             * @param string $message alert message
             * @param string $class alert class
             * @return boolean alert saved status
             */
             function api_alerts_add($message,$class="info"){
              // checks
              if(!$message){return FALSE;}
              if(!is_array($_SESSION['alerts'])){$_SESSION['alerts']=array();}
              // build alert object
              $alert=new stdClass();
              $alert->timestamp=api_datetime_now();
              $alert->message=$message;
              $alert->class=$class;
              $_SESSION['alerts'][]=$alert;
              // return
              return TRUE;
             }

?>