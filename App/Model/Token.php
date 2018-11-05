<?php
namespace App\Model;
use \SON\Di\Container;

class Token {
  public $data;

  public function __construct($userID) {
    // Get the user info used as token
    $Usuario = Container::getClass("Usuario");
    $user = $Usuario->findById($userID);
    $this->data = [
      "username" => $user["login"],
      "last_login_date" => $user["data_ultimo_login"],
      "timestamp" => time()
    ];
  }

}

?>
