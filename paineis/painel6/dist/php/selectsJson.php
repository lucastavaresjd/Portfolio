<?php

require('./conexao.php');// REQUSIÇÃO DO BANCO

$parametro =$_GET['parametro'];//PARAMETRO

if (isset($_GET['setor'])) {
    $setor =$_GET['setor'];//PARAMETRO
} else {
    $setor = "";//PARAMETRO
}


//selects

$select_dos_setores = "SELECT id,servico AS setor FROM servicos";//lista de serviços

$agendamentos_do_dia = "SELECT count(distinct(nome_paciente)) as agendamento_do_dia
FROM agendamento where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()";

$agendamentos_do_dia_por_setor = "SELECT count(distinct(nome_paciente)) as agendamento_do_dia
FROM agendamento where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()";

$lista_dos_intevalos_por_hora_do_dia_do_hospital_inteiro = "SELECT  CONCAT(HOUR(hora_servico_selecionado), ':00-', HOUR(hora_servico_selecionado)+1, ':00')  intervalo_de_horas, COUNT(*) as `usage`
FROM agendamento
where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()
GROUP BY HOUR(hora_servico_selecionado)";

//este select traz o horario de maior valor porem se 2 horarios tiverem o mesma quantidade de pessoas ele lista os dois

//o primeiro e o segundo select  traz a lista com todos os horarios e sua devida quantidade depois 3 e 4 select traz o valor com a maior hora detre todos e faz a comparação com o primeiro

$horario_de_maior_fluxo = "SELECT  qtd_por_hora, intervalo_de_horas  FROM (
                                                          SELECT  CONCAT(HOUR(hora_servico_selecionado), ':00-', HOUR(hora_servico_selecionado)+1, ':00') as intervalo_de_horas, 
                                                          COUNT(*) as qtd_por_hora
                                                          FROM agendamento
                                                          where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') = CURDATE() and codigo_servico_atual = '$setor'
                                                          GROUP BY HOUR(intervalo_de_horas) 
                                                                    ) as lista_geral_de_horas  where qtd_por_hora = ( 
                                                                      SELECT  max(qtd_por_hora) as maior_qtd FROM(
                                                                                SELECT  CONCAT(HOUR(hora_servico_selecionado), ':00-', HOUR(hora_servico_selecionado)+2, ':00') as intervalo_de_horas, 
                                                                                COUNT(*) as qtd_por_hora
                                                                                FROM agendamento
                                                                                where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') = CURDATE() and codigo_servico_atual = '$setor'
                                                                                GROUP BY HOUR(intervalo_de_horas)
                                                                                ) as maior_valor
                                                                    );";//intervalo com maior fluxo de pessoas no setor


$lista_de_setores = "SELECT servico as setor FROM servicos;";

$lista_do_setor = "SELECT 
distinct(a.nome_paciente) as paciente,
left(a.hora_servico_selecionado, 5) as hora, 
a.codigo_agenda as atividade,
a.ih_paciente as IH,
a.servico_atual,
s.servico as setor,
a.proximo_servico,
a.cod_cor_status
FROM agendamento as a INNER JOIN servicos as s on a.codigo_servico_atual = s.id
where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()  and a.codigo_servico_atual = $setor order by hora";

$lista_do_setor_paciente = "SELECT 
a.nome_paciente as paciente,
left(a.hora_servico_selecionado, 5) as hora, 
a.ih_paciente as IH,
a.cod_cor_status
FROM agendamento as a INNER JOIN servicos as s on a.codigo_servico_atual = s.id
where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()  and a.codigo_servico_atual =  $setor group by ih_paciente";


$qtd_por_setor = "SELECT 
count(distinct(a.nome_paciente)) as qtd_paciente
FROM agendamento as a INNER JOIN servicos as s on a.codigo_servico_atual = s.id
where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()  and s.id  = " .$setor .  " order by  servico";

$procedimentos = "SELECT 
count(a.nome_paciente) as qtd_procedimentos
FROM agendamento as a INNER JOIN servicos as s on a.codigo_servico_atual = s.id
where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE()  and s.id  = " .$setor .  " order by  servico";

//Consolidado
$contagem_de_Pacientes_do_dia = "select count(paciente) as totaldePacientes from (
                                                          SELECT 
                                                          distinct(a.nome_paciente) as paciente,
                                                          a.servico_atual,
                                                          s.servico as setor
                                                          FROM agendamento as a INNER JOIN servicos as s on a.codigo_servico_atual = s.id
                                                          where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE() order by  servico and servico_atual
                                                        ) as contagemDePacientes";

$contagem_de_Procedimento_do_dia = "select count(paciente) as total_procedimento from (
                                                          SELECT 
                                                          a.nome_paciente as paciente,
                                                          a.servico_atual,
                                                          s.servico as setor
                                                          FROM agendamento as a INNER JOIN servicos as s on a.codigo_servico_atual = s.id
                                                          where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE() order by  servico and servico_atual
                                                        ) as contagemDeProcedimento";



$contagem_de_pacientes_por_setor = "SELECT a.codigo_servico_atual as id,s.servico as setor ,count(distinct(a.nome_paciente)) as agendamento_do_dia, count(a.nome_paciente) as exames
                                                                    FROM agendamento as a 
                                                                    inner join servicos as s on a.codigo_servico_atual = s.id
                                                                    where STR_TO_DATE(data_servico_atual, '%d/%m/%Y') =  CURDATE() group by(codigo_servico_atual);";


//agendamentos
$notificacao = "SELECT * FROM notificacao;";


//parametro passado
if ($parametro === 'agendamentos_do_dia') {
    geraJson($agendamentos_do_dia, $conexao);
} elseif ($parametro === 'lista_dos_intevalos_por_hora_do_dia') {
    geraJson($lista_dos_intevalos_por_hora_do_dia, $conexao);
} elseif ($parametro === 'horario_de_maior_fluxo') {
    geraJson($horario_de_maior_fluxo, $conexao);
} elseif ($parametro === 'agendamentos_do_dia_por_setor') {
    geraJson($agendamentos_do_dia_por_setor, $conexao);
} elseif ($parametro === 'lista_de_setores') {
    geraJson($select_dos_setores, $conexao);
} elseif ($parametro === 'qtd_por_setor') {
    geraJson($qtd_por_setor, $conexao);
} elseif ($parametro === 'lista_do_setor') {
    geraJson($lista_do_setor, $conexao);
} elseif ($parametro === 'paciente_do_dia') {
    geraJson($contagem_de_Pacientes_do_dia, $conexao);
} elseif ($parametro === 'procedimento_do_dia') {
    geraJson($contagem_de_Procedimento_do_dia, $conexao);
} elseif ($parametro === 'consolidado_cards_com_dados') {
    geraJson($contagem_de_pacientes_por_setor, $conexao);
} elseif ($parametro === 'qtd_procedimentos') {
    geraJson($procedimentos, $conexao);
} elseif ($parametro === 'notificacao') {
    geraJson($notificacao, $conexao);
} elseif ($parametro === 'lista_do_setor_paciente') {
    geraJson($lista_do_setor_paciente, $conexao);
}

 

//retorna e exibe o json
  function geraJson($select, $conexao)
  {
      $sql = $select;
      $stmt = $conexao->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $novo = array();
      foreach ($result as $key => $value) {
          foreach ($value as $k => $v) {
              $novo[$key][$k] = $v;
          }
      }
      $json = json_encode($novo);
      echo $json;
  }
?>




