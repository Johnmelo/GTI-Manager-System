<?php
namespace App\Model;
use SON\Db\Table;
class SolicitarChamado extends Table{
  protected $table = "solicitacao_chamado";

  public function save($id_usuario,$id_servico,$descricao,$data){
    $query = "Insert into ".$this->table." (data_solicitacao,id_cliente,id_servico,descricao) values ('{$data}','{$id_usuario}','{$id_servico}','{$data}')";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
  }

  public function getChamadosByStatus($status){
    $stmt = $this->db->prepare("Select * from {$this->table} where status=:status");
    $stmt->bindParam(":status",$status);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
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
