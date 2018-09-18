<?php
namespace App\Model;
use SON\Db\Table;
class Local extends Table{
  protected $table = "locais";

  public function getCurrentPlaces() {
    $stmt = $this->db->prepare("Select * from {$this->table} where ativo=1");
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

}
?>
