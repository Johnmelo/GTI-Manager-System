<?php
namespace App\Model;
use SON\Db\Table;
class Chamado extends Table{
  protected $table = "chamados";

  public function getChamadosByStatus($status){
    $stmt = $this->db->prepare("Select * from {$this->table} where status=:status");
    $stmt->bindParam(":status",$status);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

  public function getChamadosByColumn($columnName, $value){
    $stmt = $this->db->prepare("Select * from {$this->table} where {$columnName}=:value");
    $stmt->bindParam(":value",$value);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

  public function save($service_id, $place_id, $request_id, $id_open_technician, $id_client, $description){
    $query = "Insert into ".$this->table." (id_servico,id_local,id_solicitacao,id_tecnico_abertura,id_cliente_solicitante,descricao) values (?,?,?,?,?,?)";
    $params = array($service_id, $place_id, $request_id, $id_open_technician, $id_client, $description);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

}
?>
