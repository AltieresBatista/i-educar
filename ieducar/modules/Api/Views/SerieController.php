<?php
#error_reporting(E_ALL);
#ini_set("display_errors", 1);
/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Api
 * @subpackage  Modules
 * @since   Arquivo disponível desde a versão ?
 * @version   $Id$
 */

require_once 'Portabilis/Controller/ApiCoreController.php';
require_once 'Portabilis/Array/Utils.php';
require_once 'Portabilis/String/Utils.php';
require_once 'Portabilis/Array/Utils.php';
require_once 'Portabilis/Date/Utils.php';
require_once 'include/pmieducar/geral.inc.php';

class SerieController extends ApiCoreController
{

  protected function canGetSeries(){
    return $this->validatesPresenceOf('instituicao_id') && $this->validatesPresenceOf('escola_id') && $this->validatesPresenceOf('curso_id');
  }

  protected function getSeries(){
    if ($this->canGetSeries()){
      $instituicaoId = $this->getRequest()->instituicao_id;
      $escolaId = $this->getRequest()->escola_id;
      $cursoId = $this->getRequest()->curso_id;

      if(is_array($escolaId))
        $escolaId = implode(",", $escolaId);

      if(is_array($cursoId))
        $cursoId = implode(",", $cursoId);

      $sql = "SELECT distinct s.cod_serie, s.nm_serie, s.idade_ideal
                FROM pmieducar.serie s
                INNER JOIN pmieducar.escola_serie es ON es.ref_cod_serie = s.cod_serie
                INNER JOIN pmieducar.curso c ON s.ref_cod_curso = c.cod_curso
                WHERE es.ativo = 1
                AND s.ativo = 1
                AND c.ativo = 1
                AND es.ref_cod_escola IN ({$escolaId})
                AND c.ref_cod_instituicao = $1
                AND c.cod_curso IN ({$cursoId})
                ORDER BY s.nm_serie ASC ";

      $params     = array($this->getRequest()->instituicao_id);

      $series = $this->fetchPreparedQuery($sql, $params);

      foreach ($series as &$serie) {
        $serie['nm_serie'] = mb_strtoupper($serie['nm_serie'], 'UTF-8');
      }

      $attrs = array(
        'cod_serie'       => 'id',
        'nm_serie'        => 'nome',
        'idade_ideal'     => 'idade_padrao'
      );

      $series = Portabilis_Array_Utils::filterSet($series, $attrs);

      return array('series' => $series );
    }
  }

  protected function getSeriesPorCurso(){
    $cursoId = $this->getRequest()->curso_id;

    $sql = "SELECT distinct s.cod_serie, s.nm_serie
              FROM pmieducar.serie s
              WHERE s.ativo = 1
              AND s.ref_cod_curso = $1
              ORDER BY s.nm_serie ASC ";
  
    $params = array($cursoId);

    $series = $this->fetchPreparedQuery($sql, $params);

    foreach ($series as &$serie) {
      $serie['nm_serie'] = mb_strtoupper($serie['nm_serie'], 'UTF-8');
    }

    $attrs = array(
      'cod_serie'       => 'id',
      'nm_serie'        => 'nome'
    );

    $series = Portabilis_Array_Utils::filterSet($series, $attrs);

    return array('series' => $series );
  }

  protected function canGetBloqueioFaixaEtaria(){
    return $this->validatesPresenceOf('instituicao_id') && $this->validatesPresenceOf('serie_id') && $this->validatesPresenceOf('data_nascimento');
  }

  protected function getBloqueioFaixaEtaria(){
    if($this->canGetBloqueioFaixaEtaria()){
      $instituicaoId  = $this->getRequest()->instituicao_id;
      $serieId        = $this->getRequest()->serie_id;
      $dataNascimento = $this->getRequest()->data_nascimento;
      $ano = isset($this->getRequest()->ano) ? $this->getRequest()->ano : date("Y");

      $objSerie = new clsPmieducarSerie($serieId);
      $detSerie = $objSerie->detalhe();

      $permiteFaixaEtaria = $objSerie->verificaPeriodoCorteEtarioDataNascimento($dataNascimento, $ano);

      $alertaFaixaEtaria = $detSerie['alerta_faixa_etaria'] == "t";
      $bloquearMatriculaFaixaEtaria = $detSerie['bloquear_matricula_faixa_etaria'] == "t";

      $retorno = array('bloqueado' => false, 'mensagem_bloqueio' => '');
      if(!$permiteFaixaEtaria){
        if($alertaFaixaEtaria || $bloquearMatriculaFaixaEtaria){
          $retorno['bloqueado'] = $bloquearMatriculaFaixaEtaria;
          $retorno['mensagem_bloqueio'] = 'A idade do aluno encontra-se fora da faixa etária pré-definida para esta série.';

        }
      }
      return $retorno;
    }
  }

  public function Gerar() {
    if ($this->isRequestFor('get', 'series'))
      $this->appendResponse($this->getSeries());
    elseif ($this->isRequestFor('get', 'series-curso'))
      $this->appendResponse($this->getSeriesPorCurso());
    elseif ($this->isRequestFor('get', 'bloqueio-faixa-etaria'))
      $this->appendResponse($this->getBloqueioFaixaEtaria());
    else
      $this->notImplementedOperationError();
  }
}
