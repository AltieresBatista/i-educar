<?php

#error_reporting(E_ALL);
#ini_set("display_errors", 1);

require_once("include/portabilis/report.php");

class PortabilisRelacaoAlunosTranporteEscolar extends Report
{
  function setForm()
  {

    $instituicao_obrigatorio = true;
    $this->ano = $ano_atual = date("Y");
    $this->campoNumero( "ano", "Ano", $this->ano, 4, 4, true);

    include("include/pmieducar/educar_campo_lista.php");
    
  }

  function onValidationSuccess()
  {
    $this->addArg('ano', (int)$_POST['ano']);
    $this->addArg('instituicao', (int)$_POST['ref_cod_instituicao']);
  }

}

$report = new PortabilisRelacaoAlunosTranporteEscolar($name = 'Rela��o de Alunos Transporte Escolar', $templateName = 'portabilis_relacao_alunos_transporte_escolar_grafico');

$report->addRequiredField('ano','ano');
$report->addRequiredField('ref_cod_instituicao', 'institui��o');
  
$report->render();
?>
