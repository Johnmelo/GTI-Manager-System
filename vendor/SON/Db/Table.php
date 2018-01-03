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

}
?>
