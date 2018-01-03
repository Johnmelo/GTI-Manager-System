<?php
namespace App\Model;
use SON\Db\Table;
class SolicitarAcesso extends Table{
  protected $table = "solicitacao_cadastro";

  public function save($nome,$email,$login,$setor,$matricula){
    $query = "Insert into ".$this->table." (nome,email,login,setor,matricula) values ('{$nome}','{$email}','{$login}','{$setor}','{$matricula}')";
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
