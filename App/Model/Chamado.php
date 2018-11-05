<?php
namespace App\Model;
use SON\Db\Table;
class Chamado extends Table{
  protected $table = "chamados";

  public function getTicketById($ticketId) {
    $stmt = $this->db->prepare(
      "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`, ".
      "`c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`, ".
      "`c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_assumido`, `c`.`data_finalizado`, ".
      "`c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico` ".
      "FROM `{$this->table}` AS `c` ".
      "LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao` ".
      "LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico` ".
      "LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante` ".
      "LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura` ".
      "LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel` ".
      "LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local` ".
      "WHERE `c`.`id` = :ticketId ".
      "ORDER BY `c`.`data_abertura` "
    );
    $stmt->bindParam(":ticketId", $ticketId);
    $stmt->execute();
    $res = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $res;
  }

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

  public function getUsersOpenTickets($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND (`c`.`status` = 'AGUARDANDO' OR `c`.`status` = 'ATENDIMENTO')"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getUsersInQueueTickets($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` = 'AGUARDANDO'"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getUsersInProcessTickets($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` = 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_assumido`");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getUsersInactiveTickets($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` <> 'AGUARDANDO' AND `c`.`status` <> 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_abertura` DESC");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getTechniciansClosedTickets($technicianId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_tecnico_responsavel` = :technicianId AND `c`.`status` = 'FINALIZADO'"
          ." ORDER BY `c`.`data_abertura` DESC");
      $stmt->bindParam(":technicianId", $technicianId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getOpenTickets() {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` = 'AGUARDANDO' OR `c`.`status` = 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getInQueueTickets() {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` = 'AGUARDANDO'"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getInProcessTickets() {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` = 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_assumido`");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getInactiveTickets() {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, `u1`.`nome` AS `cliente`,"
          ." `c`.`id_tecnico_responsavel`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `u3`.`nome` AS `tecnico_responsavel`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `usuarios` AS `u3` ON `u3`.`id` = `c`.`id_tecnico_responsavel`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` <> 'AGUARDANDO' AND `c`.`status` <> 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_abertura` DESC");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function save($service_id, $place_id, $request_id, $id_open_technician, $id_client, $deadline, $description){
    $query = "Insert into ".$this->table." (id_servico,id_local,id_solicitacao,id_tecnico_abertura,id_cliente_solicitante,prazo,descricao) values (?,?,?,?,?,?,?)";
    $params = array($service_id, $place_id, $request_id, $id_open_technician, $id_client, $deadline, $description);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

}
?>
