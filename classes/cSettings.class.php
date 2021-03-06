<?php
/**
 * Settings
 *
 * @package Coordinator\Classes
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

 /**
  * Settings class
  */
 class cSettings{

  /** Properties */
  protected $settings_array;

  /**
   * Settings class
   *
   * @return boolean
   */
  public function __construct(){
   // definitions
   $this->settings_array=array();
   // get settings and build object
   $settings_results=$GLOBALS['database']->queryObjects("SELECT * FROM `framework__settings` ORDER BY `setting` ASC");
   foreach($settings_results as $setting){$this->settings_array[$setting->setting]=$setting->value;}
   // make logo
   if(file_exists(DIR."uploads/framework/logo.png")){
    $this->settings_array["logo"]=PATH."uploads/framework/logo.png";
   }else{
    $this->settings_array["logo"]=PATH."uploads/framework/logo.default.png";
   }
   return true;
  }

  /**
   * Get
   *
   * @param string $setting Setting name
   * @return string Setting value
   */
  public function __get($setting){
   // check if setting exist
   if(!array_key_exists($setting,$this->settings_array)){return false;}
   // return setting value
   return $this->settings_array[$setting];
  }

 }

?>