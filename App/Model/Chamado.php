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

  public function save($service_id, $request_id, $open_date, $id_open_technician, $id_client, $description){
    $query = "Insert into ".$this->table." (id_servico,id_solicitacao,data_abertura,id_tecnico_abertura,id_cliente_solicitante,descricao) values ('{$service_id}','{$request_id}','{$open_date}','{$id_open_technician}','{$id_client}','{$description}')";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
  }

  public function updateColumnById($columnName, $value, $id){
    $query = "update {$this->table} SET {$columnName}='{$value}' WHERE id=".$id;
    $stmt = $this->db->prepare($query);
    if($stmt->execute() === TRUE){
      echo $stmt->rowCount() . " records UPDATED successfully";
    }else{
      echo "\nPDO::errorInfo():\n";
      print_r($stmt->errorInfo());
      //echo "Error updating record: " . $stmt->error;
    }
  }
}
?>
