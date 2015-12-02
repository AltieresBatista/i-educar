<?php

/**
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu�do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl�cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Adriano Erik Weiguert Nagasava <ctima@itajai.sc.gov.br>
 * @author    Haissam Yebahi <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once "lib/Portabilis/String/Utils.php";

/**
 * clsIndexBase class.
 *
 * @author    Adriano Erik Weiguert Nagasava <ctima@itajai.sc.gov.br>
 * @author    Haissam Yebahi <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Servidor Aloca��o');
    $this->processoAp = 635;
    $this->addEstilo('localizacaoSistema');
  }
}

/**
 * indice class.
 *
 * @author    Adriano Erik Weiguert Nagasava <ctima@itajai.sc.gov.br>
 * @author    Haissam Yebahi <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsCadastro
{
  var $pessoa_logada;
  var $cod_servidor_alocacao;
  var $ref_ref_cod_instituicao;
  var $ref_usuario_exc;
  var $ref_usuario_cad;
  var $ref_cod_escola;
  var $ref_cod_servidor;
  var $data_cadastro;
  var $data_exclusao;
  var $ativo;
  var $carga_horaria_alocada;
  var $carga_horaria_disponivel;
  var $periodo;
  var $ref_cod_funcionario_vinculo;
  var $ano;
  var $alocacao_array          = array();
  var $alocacao_excluida_array = array();

  static $escolasPeriodos = array();
  static $periodos = array();

  function Inicializar()
  {
    $retorno = 'Novo';
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $ref_cod_servidor        = $_GET['ref_cod_servidor'];
    $ref_ref_cod_instituicao = $_GET['ref_cod_instituicao'];
    $cod_servidor_alocacao   = $_GET['cod_servidor_alocacao'];

    if (is_numeric($cod_servidor_alocacao)) {
      $this->cod_servidor_alocacao = $cod_servidor_alocacao;

      $servidorAlocacao = new clsPmieducarServidorAlocacao($this->cod_servidor_alocacao);
      $servidorAlocacao = $servidorAlocacao->detalhe();

      $this->ref_ref_cod_instituicao     = $servidorAlocacao['ref_ref_cod_instituicao'];
      $this->ref_cod_servidor            = $servidorAlocacao['ref_cod_servidor'];
      $this->ref_cod_escola              = $servidorAlocacao['ref_cod_escola'];
      $this->periodo                     = $servidorAlocacao['periodo'];
      $this->carga_horaria_alocada       = $servidorAlocacao['carga_horaria'];
      $this->cod_servidor_funcao         = $servidorAlocacao['ref_cod_servidor_funcao'];
      $this->ref_cod_funcionario_vinculo = $servidorAlocacao['ref_cod_funcionario_vinculo'];
      $this->ativo                       = $servidorAlocacao['ativo'];
      $this->ano                         = $servidorAlocacao['ano'];

    } else if (is_numeric($ref_cod_servidor) && is_numeric($ref_ref_cod_instituicao)) {
      $this->ref_ref_cod_instituicao = $ref_ref_cod_instituicao;
      $this->ref_cod_servidor        = $ref_cod_servidor;
    } else {
      header('Location: educar_servidor_lst.php');
      die();
    }

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7,
      'educar_servidor_lst.php');

    if ($obj_permissoes->permissao_excluir(635, $this->pessoa_logada, 7)) {
      $this->fexcluir = TRUE;
    }

    $this->url_cancelar      = sprintf(
      'educar_servidor_alocacao_lst.php?ref_cod_servidor=%d&ref_cod_instituicao=%d',
      $this->ref_cod_servidor, $this->ref_ref_cod_instituicao
    );
    $this->nome_url_cancelar = 'Cancelar';

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "i-Educar - Escola",
         ""                                  => "Alocar servidor"
    ));
    $this->enviaLocalizacao($localizacao->montar());

    return $retorno;
  }

  function Gerar()
  {

    $obj_inst = new clsPmieducarInstituicao($this->ref_ref_cod_instituicao);
    $inst_det = $obj_inst->detalhe();

    $this->campoRotulo('nm_instituicao', 'Institui��o', $inst_det['nm_instituicao']);
    $this->campoOculto("ref_ref_cod_instituicao", $this->ref_ref_cod_instituicao);
    $this->campoOculto("cod_servidor_alocacao", $this->cod_servidor_alocacao);

    // Dados do servidor
    $objTemp = new clsPmieducarServidor($this->ref_cod_servidor, NULL,
        NULL, NULL, NULL, NULL, 1, $this->ref_ref_cod_instituicao);
    $det = $objTemp->detalhe();

    if ($det) {
      $this->carga_horaria_disponivel = $det['carga_horaria'];
    }

    if ($this->ref_cod_servidor) {
      $objTemp = new clsPessoaFisica($this->ref_cod_servidor);
      $detalhe = $objTemp->detalhe();
      //$detalhe = $detalhe['idpes']->detalhe();
      $nm_servidor = $detalhe['nome'];
    }

    $this->campoRotulo('nm_servidor', 'Servidor', $nm_servidor);

    $this->campoOculto('ref_cod_servidor', $this->ref_cod_servidor);

    // Carga hor�ria
    $carga = $this->carga_horaria_disponivel;
    $this->campoRotulo('carga_horaria_disponivel', 'Carga hor�ria do servidor', $carga . ':00');

    $this->inputsHelper()->integer('ano', array('value' => $this->ano, 'max_length' => 4));

    // Escolas
    $obj_escola = new clsPmieducarEscola();

    $lista_escola = $obj_escola->lista(NULL, NULL, NULL, $this->ref_ref_cod_instituicao, NULL, NULL, NULL, NULL, NULL, NULL, 1);

    $opcoes = array('' => 'Selecione');

    if ($lista_escola) {
      foreach ($lista_escola as $escola) {
        $opcoes[$escola['cod_escola']] = $escola['nome'];
      }
    }

    $this->campoLista('ref_cod_escola', 'Escola', $opcoes, $this->ref_cod_escola, '', FALSE, '', '', FALSE, TRUE);

    // Per�odos
    $periodo = array(
      1  => 'Matutino',
      2  => 'Vespertino',
      3  => 'Noturno'
    );
    self::$periodos = $periodo;

    $this->campoLista('periodo', 'Per�odo', $periodo, $this->periodo, NULL, FALSE, '', '', FALSE, TRUE);

    // Carga hor�ria
    $this->campoHora('carga_horaria_alocada', 'Carga hor�ria', $this->carga_horaria_alocada, TRUE);

    // Fun��es
    $obj_funcoes = new clsPmieducarServidorFuncao();

    $lista_funcoes = $obj_funcoes->funcoesDoServidor($this->ref_ref_cod_instituicao, $this->ref_cod_servidor);

    $opcoes = array('' => 'Selecione');

    if ($lista_funcoes) {
      foreach ($lista_funcoes as $funcao) {
        $opcoes[$funcao['cod_servidor_funcao']] = ( !empty($funcao['matricula']) ? "{$funcao['funcao']} - {$funcao['matricula']}" : $funcao['funcao'] );
      }
    }

    $this->campoLista('cod_servidor_funcao', 'Fun��o', $opcoes, $this->cod_servidor_funcao, '', FALSE, '', '', FALSE, FALSE);

    // V�nculos
    $opcoes = array("" => "Selecione", 5 => "Comissionado", 4 => "Contratado", 3 => "Efetivo", 6 => "Estagi&aacute;rio");

    $this->campoLista("ref_cod_funcionario_vinculo", "V&iacute;nculo", $opcoes, $this->ref_cod_funcionario_vinculo, NULL, FALSE, '', '', FALSE, FALSE);
  }

  function Novo()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7,
        "educar_servidor_alocacao_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_ref_cod_instituicao}");

    $servidorAlocacao = new clsPmieducarServidorAlocacao($this->cod_servidor_alocacao,
                                                 $this->ref_ref_cod_instituicao,
                                                 null,
                                                 null,
                                                 null,
                                                 $this->ref_cod_servidor,
                                                 null,
                                                 null,
                                                 null,
                                                 null,
                                                 null,
                                                 null,
                                                 null,
                                                 $this->ano);

    $carga_horaria_disponivel = $this->hhmmToMinutes($this->carga_horaria_disponivel);
    $carga_horaria_alocada    = $this->hhmmToMinutes($this->carga_horaria_alocada);
    $carga_horaria_alocada   += $this->hhmmToMinutes($servidorAlocacao->getCargaHorariaAno());

    if ($carga_horaria_disponivel >= $carga_horaria_alocada){

    $obj_novo = new clsPmieducarServidorAlocacao(null,
                                                 $this->ref_ref_cod_instituicao,
                                                 null,
                                                 $this->pessoa_logada,
                                                 $this->ref_cod_escola,
                                                 $this->ref_cod_servidor,
                                                 null,
                                                 null,
                                                 $this->ativo,
                                                 $this->carga_horaria_alocada,
                                                 $this->periodo,
                                                 $this->cod_servidor_funcao,
                                                 $this->ref_cod_funcionario_vinculo,
                                                 $this->ano);

      $cadastrou = $obj_novo->cadastra();

      if (!$cadastrou) {
        $this->mensagem = 'Cadastro n�o realizado.<br />';
        echo "<!--\nErro ao cadastrar clsPmieducarServidorAlocacao\nvalores obrigatorios\nis_numeric($this->ref_ref_cod_instituicao) &&
              is_numeric($this->ref_usuario_cad) && is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_servidor) &&
              is_numeric($this->periodo) && ($this->carga_horaria_alocada)\n-->";
        return FALSE;
      }

      // Exclu� aloca��o existente
      if ($this->cod_servidor_alocacao) {
        $obj_tmp = new clsPmieducarServidorAlocacao($this->cod_servidor_alocacao, null, $this->pessoa_logada);
        $obj_tmp->excluir();
      }

      // Atualiza c�digo da aloca��o
      $this->cod_servidor_alocacao = $cadastrou;
    }else{
      $this->mensagem = 'N�o � poss�vel alocar quantidade superior de horas do que o dispon�vel.<br />';
      $this->alocacao_array = null;

      return false;
    }

    $this->mensagem .= 'Cadastro efetuado com sucesso.<br />';
    header('Location: ' . sprintf('educar_servidor_alocacao_det.php?cod_servidor_alocacao=%d', $this->cod_servidor_alocacao));
    die();
  }

  function Editar()
  {
    return FALSE;
  }

  function Excluir()
  {

    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    if ($this->cod_servidor_alocacao) {
      $obj_tmp = new clsPmieducarServidorAlocacao($this->cod_servidor_alocacao, null, $this->pessoa_logada);
      $excluiu = $obj_tmp->excluir();

      if ($excluiu) {
        $this->mensagem = "Exclus�o efetuada com sucesso.<br>";
        header("Location: ". sprintf(
              'educar_servidor_alocacao_lst.php?ref_cod_servidor=%d&ref_cod_instituicao=%d',
              $this->ref_cod_servidor, $this->ref_ref_cod_instituicao));
        die();
      }
    }

    $this->mensagem = 'Exclus�o n�o realizada.<br>';
    return false;
  }

  function hhmmToMinutes($hhmm){
  	list($hora, $minuto) = split(':', $hhmm);
  	return (((int)$hora * 60) + $minuto);
  }

  function arrayHhmmToMinutes($array){
    $total = 0;
    foreach ($array as $key => $value) {
      $total += $this->hhmmToMinutes($value);
    }
    return $total;
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do �  p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();
?>