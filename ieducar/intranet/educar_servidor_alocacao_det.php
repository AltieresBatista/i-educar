<?php
// error_reporting(E_ALL);
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
require_once 'include/clsDetalhe.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';

require_once 'ComponenteCurricular/Model/ComponenteDataMapper.php';
require_once 'Educacenso/Model/DocenteDataMapper.php';

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
class clsIndexBase extends clsBase {
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Servidor aloca��o');
    $this->processoAp = 635;
    $this->addEstilo('localizacaoSistema');
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
class indice extends clsDetalhe
{
  var $titulo;

  /**
   * Atributos de dados
   */
  var $cod_servidor_alocacao = null;
  var $ref_cod_servidor = null;
  var $ref_cod_instituicao = null;
  var $ref_cod_servidor_funcao = null;
  var $ref_cod_funcionario_vinculo = null;
  var $ano = null;
  /**
   * Implementa��o do m�todo Gerar()
   */
  function Gerar()
  {
    session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    $this->titulo = 'Servidor aloca��o - Detalhe';
    $this->addBanner('imagens/nvp_top_intranet.jpg', 'imagens/nvp_vert_intranet.jpg', 'Intranet');

    $this->cod_servidor_alocacao = $_GET['cod_servidor_alocacao'];

    $tmp_obj = new clsPmieducarServidorAlocacao($this->cod_servidor_alocacao, $this->ref_cod_instituicao);

    $registro = $tmp_obj->detalhe();

    if (!$registro) {
      header('Location: educar_servidor_lst.php');
      die();
    }

    $this->ref_cod_servidor            = $registro['ref_cod_servidor'];
    $this->ref_cod_instituicao         = $registro['ref_ref_cod_instituicao'];
    $this->ref_cod_servidor_funcao     = $registro['ref_cod_servidor_funcao'];
    $this->ref_cod_funcionario_vinculo = $registro['ref_cod_funcionario_vinculo'];
    $this->ano                         = $registro['ano'];

    //Nome do servidor
    $fisica = new clsPessoaFisica($this->ref_cod_servidor);
    $fisica = $fisica->detalhe();

    $this->addDetalhe(array("Servidor", "{$fisica["nome"]}"));

    //Escola
    $escola = new clsPmieducarEscola($registro['ref_cod_escola']);
    $escola = $escola->detalhe();

    $this->addDetalhe(array("Escola", "{$escola["nome"]}"));

    //Ano
    $this->addDetalhe(array("Ano", "{$registro['ano']}"));

    //Periodo
    $periodo = array(
      1  => 'Matutino',
      2  => 'Vespertino',
      3  => 'Noturno'
    );

    $this->addDetalhe(array("Periodo", "{$periodo[$registro['periodo']]}"));

    //Carga hor�ria
    $this->addDetalhe(array("Carga hor�ria", "{$registro['carga_horaria']}"));

    //Fun��o
    if ($this->ref_cod_servidor_funcao) {
      $funcaoServidor = new clsPmieducarServidorFuncao(null, null, null, null, $this->ref_cod_servidor_funcao);
      $funcaoServidor = $funcaoServidor->detalhe();

      $funcao = new clsPmieducarFuncao($funcaoServidor['ref_cod_funcao']);
      $funcao = $funcao->detalhe();

      $this->addDetalhe(array("Fun��o", "{$funcao['nm_funcao']}"));
    }

    //Vinculo
    if ($this->ref_cod_funcionario_vinculo) {
      $funcionarioVinculo = new clsPortalFuncionario();
      $funcionarioVinculo = $funcionarioVinculo->getNomeVinculo($registro['ref_cod_funcionario_vinculo']);

      $this->addDetalhe(array("Vinculo", "{$funcionarioVinculo}"));
    }

    $obj_permissoes = new clsPermissoes();
    if ($obj_permissoes->permissao_cadastra(635, $this->pessoa_logada, 7)) {

      $this->url_novo   = "educar_servidor_alocacao_cad.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}";
      $this->url_editar = "educar_servidor_alocacao_cad.php?cod_servidor_alocacao={$this->cod_servidor_alocacao}";
    }

    $this->url_cancelar = "educar_servidor_alocacao_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}";
    $this->largura = '100%';

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos(array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php" => "i-Educar - Escola",
         "" => "Detalhe da aloca��o"
    ));

    $this->enviaLocalizacao($localizacao->montar());
  }
}

// Instancia o objeto da p�gina
$pagina = new clsIndexBase();

// Instancia o objeto de conte�do
$miolo = new indice();

// Passa o conte�do para a p�gina
$pagina->addForm($miolo);

// Gera o HTML
$pagina->MakeAll();