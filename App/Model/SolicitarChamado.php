<?php
namespace App\Model;
use SON\Db\Table;
class SolicitarChamado extends Table{
  protected $table = "solicitacao_chamado";

  public function save($id_usuario,$id_servico,$id_local,$descricao){
    $query = "Insert into ".$this->table." (id_cliente,id_servico,id_local,descricao) values (?,?,?,?)";
    $params = array($id_usuario, $id_servico, $id_local, $descricao);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

  public function getChamadosByStatus($status){
    $stmt = $this->db->prepare("Select * from {$this->table} where status=:status");
    $stmt->bindParam(":status",$status);
    $stmt->execute();
    $res = $stmt->fetchAll();
    return $res;
  }

  public function getChamadosById($request_id){
      $query = "SELECT s.id AS id_solicitacao, c.id AS id_chamado, s.data_solicitacao, l.id AS id_local, s.id_cliente, s.id_servico, s.descricao, s.status AS solicitacao_chamado_status, c.status AS chamado_status, c.data_abertura, c.data_finalizado, c.prazo, c.id_tecnico_responsavel, c.id_tecnico_abertura, c.parecer_tecnico FROM {$this->table} AS s LEFT JOIN chamados AS c ON c.id_solicitacao = s.id LEFT JOIN locais AS l ON l.id = s.id_local WHERE c.id = :request_id";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(":request_id",$request_id);
      $stmt->execute();
      $res = $stmt->fetchAll();
      return $res;
  }

  public function getSolicitacoesById($call_request_id){
      $query = "SELECT s.id AS id_solicitacao, s.data_solicitacao, l.id AS id_local, s.id_cliente, s.id_servico, s.descricao, s.status AS solicitacao_chamado_status FROM {$this->table} AS s LEFT JOIN locais AS l ON l.id = s.id_local WHERE s.id = :call_request_id";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(":call_request_id",$call_request_id);
      $stmt->execute();
      $res = $stmt->fetchAll();
      return $res;
  }


}
?>
