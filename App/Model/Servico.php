<?php
namespace App\Model;
use SON\Db\Table;
class Servico extends Table{
  protected $table = "servicos";

  public function save($nome,$descricao){
    $query = "Insert into ".$this->table." (nome,descricao) values ('{$nome}','{$descricao}')";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
  }
}
?>
