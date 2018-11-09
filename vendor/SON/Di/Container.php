<?php
namespace SON\Di;

class Container{
  public static function getClass($name){
    $str_class = "\\App\\Model\\".ucfirst($name);
    $class = new $str_class(\SON\Db\DBConnector::getInstance());
    return $class;
  }
}
?>
