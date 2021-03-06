<?php
/**
 * Grid
 *
 * Coordinator Structure Class for Grids
 *
 * @package Coordinator\Classes
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */

 /**
  * Grid structure class
  */
 class strGrid{

  /** Properties */
  protected $id;
  protected $class;
  protected $current_row;
  protected $rows_array;

  /**
   * Grid structure class
   *
   * @param string $class Grid css class
   * @param string $id Grid ID, if null randomly generated
   * @return boolean
   */
  public function __construct($class=null,$id=null){
   $this->id="grid_".($id?$id:api_random());
   $this->class=$class;
   $this->current_row=0;
   $this->rows_array=array();
   return true;
  }

  /**
   * Add Row
   *
   * @param string $class Element css class
   * @return boolean
   */
  public function addRow($class=null){
   $row=new stdClass();
   $row->class=$class;
   $row->cols_array=array();
   $this->current_row++;
   $this->rows_array[$this->current_row]=$row;
   return true;
  }

  /**
   * Add Col
   *
   * @param string $content Col content
   * @param string $class Col css class (col-
   * @return boolean
   */
  public function addCol($content,$class=null){
   if(!$this->current_row){api_dump("ERROR - Grid->addCol - No rows defined");return false;}
   //if(!$content){api_dump("ERROR - Grid->addCol - Content is required");return false;}
   if(substr($class,0,4)!="col-"){api_dump("ERROR - Grid->addCol - Class \"col-..\" is required");return false;}
   $col=new stdClass();
   $col->content=$content;
   $col->class=$class;
   $this->rows_array[$this->current_row]->cols_array[]=$col;
   return true;
  }

  /**
   * Renderize Grid object
   *
   * @param boolean $container Renderize container
   * @return string HTML source code
   */
  public function render($container=true){
   // renderize grid
   if($container){
    $return="<!-- grid container -->\n";
    $return.="<div id=\"".$this->id."\" class='container ".$this->class."'>\n";
   }
   // cycle all grid rows
   foreach($this->rows_array as $row){
    // renderize grid rows
    $return.=" <!-- grid-row -->\n";
    $return.=" <div class=\"row ".$row->class."\">\n";
    // cycle all grid row cols
    foreach($row->cols_array as $col){
     // renderize grid row cols
     $return.="  <!-- grid-row-col -->\n";
     $return.="  <div class=\"".$col->class."\">\n\n";
     $return.=$col->content."\n";
     $return.="  </div><!-- /grid-row-col -->\n";
    }
    $return.=" </div><!-- /grid-row -->\n";
   }
   if($container){$return.="</div><!-- /grid container -->\n\n";}
   // return
   return $return;
  }

 }

?>