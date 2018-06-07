<?php
namespace App\Model;
use SON\Db\Table;
use \App\Model\PasswordUtil;

class Usuario extends Table{
  protected $table = "usuarios";

  public function save($nome,$email,$login,$setor,$matricula){
    // Hash password derived from the default pattern
    $pass = ''.$login.''.$matricula;
    $pwhash = PasswordUtil::hash($pass);
    // Insert the user
    $query = "Insert into ".$this->table." (nome,email,login,setor,matricula,password_hash) values (?,?,?,?,?,?)";
    $params = array($nome, $email, $login, $setor, $matricula, $pwhash);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
  }

  public function findByLogin($login){
    $stmt = $this->db->prepare("Select * from {$this->table} where login=:login");
    $stmt->bindParam(":login",$login);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
  }
}
?>
