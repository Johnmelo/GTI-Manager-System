<?php
namespace SON\Controller;

class Action{
  protected $view;
  protected $action;

  public function __construct(){
    $this->view = new \stdClass;
  }

  public function render($action, $layout=true){
    $this->action = $action;
    if($layout == true && file_exists("../App/View/layout.phtml")){
      include_once '../App/View/layout.phtml';
    }else{
      $this->content();
    }

  }

  public function content(){
    $actual = get_class($this);
    $singleClassName = strtolower(str_replace("App\\Controller\\","",$actual));
    include_once '../App/View/'.$singleClassName.'/'.$this->action.'.phtml';
  }

  public function forbidenAccess(){
    header('Location: /gticchla/public/');
  }
}
?>
