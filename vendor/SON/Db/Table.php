<?php
namespace SON\Db;

abstract class Table{
  protected $db;
  protected $table;

  public function __construct(\PDO $db){
    $this->db = $db;
  }

  public function fetchAll(){
    $query = "Select * from {$this->table}";
    return $this->db->query($query);
  }

  public function findById($id){
    $stmt = $this->db->prepare("Select * from {$this->table} where id=:id");
    $stmt->bindParam(":id",$id);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
  }

  public function updateColumnById($columnName, $value, $id){
    $query = "update {$this->table} SET {$columnName} = ? WHERE id = ?";
    $params = array($value, $id);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function deleteById($id){
    $query = "DELETE FROM {$this->table} WHERE id=".$id;
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
