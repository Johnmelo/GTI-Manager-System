<?php
namespace App\Model;
use SON\Db\Table;
class UsuarioRole extends Table{
  protected $table = "usuarios_roles";

  public function save($id_user,$cliente,$tecnico,$gerente){
    $query = "Insert into ".$this->table." (id_usuario, cliente, tecnico, gerente) values (?,?,?,?)";
    $params = array($id_user, $cliente, $tecnico, $gerente);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function findByIdUser($id){
    $stmt = $this->db->prepare("Select * from {$this->table} where id_usuario=:id");
    $stmt->bindParam(":id",$id);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
  }
}
?>
