<?php
namespace App\Model;
use SON\Db\Table;
class Cliente extends Table{
  protected $table = "clientes";

  public function save($nome,$email,$login,$setor,$matricula){
    $pass = ''.$login.$matricula;
    $query = "Insert into ".$this->table." (nome,email,login,password,setor,matricula) values ('{$nome}','{$email}','{$login}','{$pass}','{$setor}','{$matricula}')";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
  }
}
?>
