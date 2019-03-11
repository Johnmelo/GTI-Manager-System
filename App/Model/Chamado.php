<?php
namespace App\Model;
use SON\Db\Table;
class Chamado extends Table{
  protected $table = "chamados";

  public function getTicketById($ticketId) {
    $stmt = $this->db->prepare(
      "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`, ".
      "`u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`, ".
      "`c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`, ".
      "`c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico` ".
      "FROM `{$this->table}` AS `c` ".
      "LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao` ".
      "LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico` ".
      "LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante` ".
      "LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura` ".
      "LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local` ".
      "WHERE `c`.`id` = :ticketId ".
      "ORDER BY `c`.`data_abertura` "
    );
    $stmt->bindParam(":ticketId", $ticketId);
    $stmt->execute();
    $res = $stmt->fetch(\PDO::FETCH_ASSOC);

    // Include the technicians sharing tickets
    $stmt = $this->db->prepare(
        "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade`, `ct_xref`.`status` ".
        "FROM `chamado_tecnico_xref` AS `ct_xref` ".
        "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
        "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
        "WHERE `c`.`id` = :ticketId"
    );
    $stmt->bindParam(":ticketId", $ticketId);
    $stmt->execute();
    $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $temp_arr = [$res];
    $this->insertResponsibleTechniciansInTickets($temp_arr, $tickets_technicians_xref);
    return $temp_arr[0];
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
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND (`c`.`status` = 'AGUARDANDO' OR `c`.`status` = 'ATENDIMENTO')"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $stmt = $this->db->prepare(
          "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade` ".
          "FROM `chamado_tecnico_xref` AS `ct_xref` ".
          "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
          "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
          "WHERE `c`.`id_cliente_solicitante` = :userId AND (`c`.`status` = 'AGUARDANDO' OR `c`.`status` = 'ATENDIMENTO') AND `ct_xref`.`status` = 1"
      );
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function getUsersInQueueTickets($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
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
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` = 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_assumido`");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $stmt = $this->db->prepare(
          "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade`, `ct_xref`.`status` ".
          "FROM `chamado_tecnico_xref` AS `ct_xref` ".
          "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
          "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
          "WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` = 'ATENDIMENTO' AND (`ct_xref`.`status` = 1 OR `ct_xref`.`status` = 2)"
      );
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function getUsersInactiveTickets($userId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` <> 'AGUARDANDO' AND `c`.`status` <> 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_abertura` DESC");
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $stmt = $this->db->prepare(
          "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade` ".
          "FROM `chamado_tecnico_xref` AS `ct_xref` ".
          "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
          "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
          "WHERE `c`.`id_cliente_solicitante` = :userId AND `c`.`status` <> 'AGUARDANDO' AND `c`.`status` <> 'ATENDIMENTO' AND `ct_xref`.`status` <> -1 AND `ct_xref`.`status` <> 0"
      );
      $stmt->bindParam(":userId", $userId);
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function getTechniciansClosedTickets($technicianId) {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." LEFT JOIN `chamado_tecnico_xref` AS `ct_xref` ON `ct_xref`.`id_chamado` = `c`.`id`"
          ." WHERE `ct_xref`.`id_tecnico` = :technicianId AND `ct_xref`.`status` <> -1 AND `ct_xref`.`status` <> 0 AND `c`.`status` = 'FINALIZADO'"
          ." ORDER BY `c`.`data_abertura` DESC");
      $stmt->bindParam(":technicianId", $technicianId);
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $stmt = $this->db->prepare(
          "SELECT * FROM `chamado_tecnico_xref` ".
          "WHERE `chamado_tecnico_xref`.`id_chamado` ".
          "IN (".
              "SELECT `c`.`id` AS `id_chamado` ".
              "FROM `chamados` AS `c` ".
              "LEFT JOIN `chamado_tecnico_xref` AS `ct_xref` ON `ct_xref`.`id_chamado` = `c`.`id` ".
              "WHERE `ct_xref`.`id_tecnico` = {$technicianId} AND `ct_xref`.`status` <> -1 AND `ct_xref`.`status` <> 0 AND `c`.`status` = 'FINALIZADO'".
          ") AND `chamado_tecnico_xref`.`status` <> -1 AND `chamado_tecnico_xref`.`status` <> 0"
      );
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function getOpenTickets() {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` = 'AGUARDANDO' OR `c`.`status` = 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $stmt = $this->db->prepare(
          "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade` ".
          "FROM `chamado_tecnico_xref` AS `ct_xref` ".
          "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
          "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
          "WHERE `c`.`status` = 'AGUARDANDO' OR `c`.`status` = 'ATENDIMENTO'"
      );
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function getInQueueTickets() {
      $stmt = $this->db->prepare(
        "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
        ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
        ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
        ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` = 'AGUARDANDO'"
          ." ORDER BY `c`.`data_abertura`");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $res;
  }

  public function getInProcessTickets($includePendingTechnicians) {
    // Get the tickets
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` = 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_assumido`");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $statusCondition = (!$includePendingTechnicians) ?  " AND `ct_xref`.`status` = 1" : "";
      $query =
          "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade`, `ct_xref`.`status` ".
          "FROM `chamado_tecnico_xref` AS `ct_xref` ".
          "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
          "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
          "WHERE `c`.`status` = 'ATENDIMENTO'" . $statusCondition;
      $stmt = $this->db->prepare($query);
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function getInactiveTickets() {
      $stmt = $this->db->prepare(
          "SELECT `c`.`id` AS `id_chamado`, `sc`.`id` AS `id_solicitacao`, `sc`.`id_cliente`,"
          ." `u1`.`nome` AS `cliente`, `l`.`nome` AS `local`, `s`.`nome` AS `servico`, `c`.`descricao`,"
          ." `c`.`status`, `sc`.`data_solicitacao`, `c`.`data_abertura`, `c`.`data_finalizado`, `c`.`data_assumido`,"
          ." `c`.`prazo`, `u2`.`nome` AS `tecnico_abertura`, `c`.`parecer_tecnico`"
          ." FROM `{$this->table}` AS `c`"
          ." LEFT JOIN `solicitacao_chamado` AS `sc` ON `sc`.`id` = `c`.`id_solicitacao`"
          ." LEFT JOIN `servicos` AS `s` ON `s`.`id` = `sc`.`id_servico`"
          ." LEFT JOIN `usuarios` AS `u1` ON `u1`.`id` = `c`.`id_cliente_solicitante`"
          ." LEFT JOIN `usuarios` AS `u2` ON `u2`.`id` = `c`.`id_tecnico_abertura`"
          ." LEFT JOIN `locais` AS `l` ON `l`.`id` = `sc`.`id_local`"
          ." WHERE `c`.`status` <> 'AGUARDANDO' AND `c`.`status` <> 'ATENDIMENTO'"
          ." ORDER BY `c`.`data_abertura` DESC");
      $stmt->execute();
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

      // Include the technicians sharing tickets
      $stmt = $this->db->prepare(
          "SELECT `ct_xref`.`id_chamado`, `ct_xref`.`id_tecnico`, `u`.`nome` AS `tecnico`, `ct_xref`.`atividade` ".
          "FROM `chamado_tecnico_xref` AS `ct_xref` ".
          "LEFT JOIN `usuarios` AS `u` ON `u`.`id` = `ct_xref`.`id_tecnico` ".
          "LEFT JOIN `chamados` AS `c` ON `c`.`id` = `ct_xref`.`id_chamado` ".
          "WHERE `c`.`status` <> 'AGUARDANDO' AND `c`.`status` <> 'ATENDIMENTO'"
      );
      $stmt->execute();
      $tickets_technicians_xref = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $this->insertResponsibleTechniciansInTickets($res, $tickets_technicians_xref);
      return $res;
  }

  public function setTicketTechnicians($ticketID, $technicianID, $technicianResponsibility, $status = 0) {
      $this->deleteTicketTechnicianResponsibility($ticketID, $technicianID);
      $query = "INSERT INTO `chamado_tecnico_xref` (id_chamado, id_tecnico, atividade, status) VALUES (?, ?, ?, ?)";
      $params = [$ticketID, $technicianID, $technicianResponsibility, $status];
      $stmt = $this->db->prepare($query);
      if ($stmt->execute($params) && $stmt->rowCount() > 0) {
        return $this->db->lastInsertId();
      } else {
        return false;
      }
  }

  public function deleteTicketTechnicianResponsibility($ticketID, $technicianID) {
      $stmt = $this->db->prepare('DELETE FROM `chamado_tecnico_xref` WHERE `id_chamado` = :ticketID AND `id_tecnico` = :technicianID');
      $stmt->bindParam(':ticketID', $ticketID);
      $stmt->bindParam(':technicianID', $technicianID);
      $stmt->execute();
  }

  public function deleteTicketTechnicianAssociations($ticketID) {
    $stmt = $this->db->prepare("DELETE FROM `chamado_tecnico_xref` WHERE `chamado_tecnico_xref`.`id_chamado` = :ticketID");
    $stmt->bindParam(":ticketID", $ticketID);
    return $stmt->execute();
  }

  public function save($service_id, $place_id, $request_id, $id_open_technician, $id_client, $deadline, $description, $tombo){
    $query = "Insert into ".$this->table." (id_servico,id_local,id_solicitacao,id_tecnico_abertura,id_cliente_solicitante,prazo,descricao,tombo) values (?,?,?,?,?,?,?,?)";
    $params = array($service_id, $place_id, $request_id, $id_open_technician, $id_client, $deadline, $description, $tombo);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

  public function setResponsibilityDone($ticketID, $technicianID) {
    $query = "UPDATE `chamado_tecnico_xref` SET `status` = '2' WHERE `chamado_tecnico_xref`.`id_chamado` = ? AND `chamado_tecnico_xref`.`id_tecnico` = ?";
    $params = array($ticketID, $technicianID);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

  public function saveResponsibilityChange($ticketID, $technicianID, $responsibility) {
    $query = "UPDATE `chamado_tecnico_xref` SET `atividade` = ? WHERE `chamado_tecnico_xref`.`id_chamado` = ? AND `chamado_tecnico_xref`.`id_tecnico` = ?";
    $params = array($responsibility, $ticketID, $technicianID);
    $stmt = $this->db->prepare($query);
    if ($stmt->execute($params) && $stmt->rowCount() > 0) {
      return $this->db->lastInsertId();
    } else {
      return false;
    }
  }

  protected function insertResponsibleTechniciansInTickets(&$tickets, &$respTechniciansData) {
    \array_walk($respTechniciansData, function($xref)use(&$tickets) {
      $ticketIndex = array_search($xref['id_chamado'], array_column($tickets, 'id_chamado'));
      if ($tickets[$ticketIndex]['responsaveis'] === null) {
        $tickets[$ticketIndex]['responsaveis'] = [];
      }
      \array_push(
        $tickets[$ticketIndex]['responsaveis'],
        array(
          "id_tecnico" => $xref['id_tecnico'],
          "tecnico" => $xref['tecnico'],
          "atividade" => $xref['atividade'],
          "status" => $xref['status']
        )
      );
    });
  }

}
?>
