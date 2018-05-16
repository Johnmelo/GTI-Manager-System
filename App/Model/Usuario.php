<?php
namespace App\Model;
use SON\Db\Table;
class Usuario extends Table{
  protected $table = "usuarios";

  public function save($nome,$email,$login,$setor,$matricula){
    $query = "Insert into ".$this->table." (nome,email,login,password,setor,matricula) values (?,?,?,?,?,?)";
    $pass = ''.$login.''.$matricula;
    $params = array($nome, $email, $login, $pass, $setor, $matricula);
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
