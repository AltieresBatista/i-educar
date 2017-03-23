<?php
// error_reporting(E_ERROR);
// ini_set("display_errors", 1);
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
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsListagem.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';

require_once 'CoreExt/View/Helper/UrlHelper.php';

/**
 * clsIndexBase class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
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
    $this->SetTitulo($this->_instituicao . ' i-Educar - Servidor');
    $this->processoAp = 635;
    $this->addEstilo("localizacaoSistema");
  }
}

/**
 * indice class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsListagem
{
  var $pessoa_logada;
  var $titulo;
  var $limite;
  var $offset;

  var $ref_cod_servidor;
  var $ref_cod_funcao;
  var $carga_horaria;
  var $data_cadastro;
  var $data_exclusao;
  var $ref_cod_escola;
  var $ref_cod_instituicao;
  var $ano_letivo;

  function Gerar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    $this->titulo = 'Aloca��o servidor - Listagem';

    // passa todos os valores obtidos no GET para atributos do objeto
    foreach ($_GET AS $var => $val) {
      $this->$var = ($val === '') ? NULL : $val;
    }

    $tmp_obj = new clsPmieducarServidor($this->ref_cod_servidor, NULL, NULL, NULL, NULL, NULL, NULL, $this->ref_cod_instituicao);
    $registro = $tmp_obj->detalhe();

    if (!$registro) {
      header('Location: educar_servidor_lst.php');
      die();
    }

    $this->addCabecalhos( array(
      'Escola',
      'Fun��o',
      'Ano',
      'Per�odo',
      'Carga hor�ria',
      'V�nculo'
    ));

    $fisica = new clsPessoaFisica($this->ref_cod_servidor);
    $fisica = $fisica->detalhe();

    $this->campoOculto('ref_cod_servidor', $this->ref_cod_servidor);
    $this->campoRotulo('nm_servidor', 'Servidor', $fisica['nome']);

    //include 'include/pmieducar/educar_campo_lista.php';
    $this->inputsHelper()->dynamic('instituicao', array('required' => false, 'show-select' => true, 'value' => $this->ref_cod_instituicao));
    $this->inputsHelper()->dynamic('escola', array('required' => false, 'show-select' => true, 'value' => $this->ref_cod_escola));
    $this->inputsHelper()->dynamic('anoLetivo', array('required' => false, 'show-select' => true, 'value' => $this->ano_letivo));

    $parametros = new clsParametrosPesquisas();
    $parametros->setSubmit(0);

    // Paginador
    $this->limite = 20;
    $this->offset = ($_GET['pagina_' . $this->nome]) ?
      $_GET['pagina_' . $this->nome] * $this->limite - $this->limite : 0;

    $obj_servidor_alocacao = new clsPmieducarServidorAlocacao();
    $obj_servidor_alocacao->setOrderby('ano ASC');
    $obj_servidor_alocacao->setLimite($this->limite, $this->offset);

    $lista = $obj_servidor_alocacao->lista(
      null,
      $this->ref_cod_instituicao,
      null,
      null,
      $this->ref_cod_escola,
      $this->ref_cod_servidor,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      null,
      $this->ano_letivo
    );
    $total = $obj_servidor_alocacao->_total;

    // UrlHelper
    $url = CoreExt_View_Helper_UrlHelper::getInstance();

    // Monta a lista
    if (is_array($lista) && count($lista)) {
      foreach ($lista as $registro) {

        $path = 'educar_servidor_alocacao_det.php';
        $options = array(
          'query' => array(
            'cod_servidor_alocacao' => $registro['cod_servidor_alocacao'],
        ));

        //Escola
        $escola = new clsPmieducarEscola($registro['ref_cod_escola']);
        $escola = $escola->detalhe();

        //Periodo
        $periodo = array(
          1  => 'Matutino',
          2  => 'Vespertino',
          3  => 'Noturno'
        );

        //Fun��o
        $funcaoServidor = new clsPmieducarServidorFuncao(null, null, null, null, $registro['ref_cod_servidor_funcao']);
        $funcaoServidor = $funcaoServidor->detalhe();

        $funcao = new clsPmieducarFuncao($funcaoServidor['ref_cod_funcao']);
        $funcao = $funcao->detalhe();

        //Vinculo
        $funcionarioVinculo = new clsPortalFuncionario();
        $funcionarioVinculo = $funcionarioVinculo->getNomeVinculo($registro['ref_cod_funcionario_vinculo']);

        $this->addLinhas(array(
          $url->l($escola['nome'], $path, $options),
          $url->l($funcao['nm_funcao'], $path, $options),
          $url->l($registro['ano'], $path, $options),
          $url->l($periodo[$registro['periodo']], $path, $options),
          $url->l($registro['carga_horaria'], $path, $options),
          $url->l($funcionarioVinculo, $path, $options),
        ));
      }
    }

    $this->addPaginador2('educar_servidor_alocacao_lst.php', $total, $_GET, $this->nome, $this->limite);

    $obj_permissoes = new clsPermissoes();

    $this->array_botao = array();
    $this->array_botao_url = array();
    if( $obj_permissoes->permissao_cadastra( 578, $this->pessoa_logada, 7 ) )
    {
      $this->array_botao_url[]= "educar_servidor_alocacao_cad.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}";
      $this->array_botao[]= "Novo";
    }

    $this->array_botao[] = "Voltar";
    $this->array_botao_url[] = "educar_servidor_det.php?cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}";

    $this->largura = '100%';

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "i-Educar - Escola",
         ""                                  => "Listagem de aloca��es"
    ));
    $this->enviaLocalizacao($localizacao->montar());
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do � p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();
