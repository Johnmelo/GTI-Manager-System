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

}
?>
