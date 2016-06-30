<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
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
require_once 'include/clsCadastro.inc.php';

/**
 * @author    Paula Bonot <bonot@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     ?
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Nova exporta&ccedil;&atilde;o');
    $this->processoAp = 846;
    $this->addEstilo('localizacaoSistema');
  }
}

class indice extends clsCadastro
{
  var $pessoa_logada;

  var $ano;
  var $ref_cod_instituicao;

  function Inicializar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(846, $this->pessoa_logada, 7,
      'educar_index.php');
    $this->ref_cod_instituicao = $obj_permissoes->getInstituicao($this->pessoa_logada);

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "Administrativo",
         ""                                  => "Exporta&ccedil;&atilde;o de usu&aacute;rios"
    ));
    $this->enviaLocalizacao($localizacao->montar());

    return 'Nova exporta&ccedil;&atilde;o';
  }

  function Gerar()
  {

    $this->inputsHelper()->dynamic(array('instituicao'));
    $this->inputsHelper()->dynamic('escola', array('required' =>  false));

    $resourcesStatus = array(1  => 'Ativo',
                       0  => 'Inativo');
    $optionsStatus   = array('label' => 'Status', 'resources' => $resourcesStatus, 'value' => 1);
    $this->inputsHelper()->select('status', $optionsStatus);

    $opcoes = array( "" => "Selecione" );
    if( class_exists( "clsPmieducarTipoUsuario" ) )
    {
      $objTemp = new clsPmieducarTipoUsuario();
      $objTemp->setOrderby('nm_tipo ASC');

      $obj_libera_menu = new clsMenuFuncionario($this->pessoa_logada,false,false,0);
      $obj_super_usuario = $obj_libera_menu->detalhe();

      // verifica se pessoa logada � super-usuario
      if ($obj_super_usuario) {
        $lista = $objTemp->lista(null,null,null,null,null,null,null,null,1);
      }else{
        $lista = $objTemp->lista(null,null,null,null,null,null,null,null,1,$obj_permissao->nivel_acesso($this->pessoa_logada));
      }

      if ( is_array( $lista ) && count( $lista ) )
      {
        foreach ( $lista as $registro )
        {
          $opcoes["{$registro['cod_tipo_usuario']}"] = "{$registro['nm_tipo']}";
          $opcoes_["{$registro['cod_tipo_usuario']}"] = "{$registro['nivel']}";
        }
      }
    }
    else
    {
      echo "<!--\nErro\nClasse clsPmieducarTipoUsuario n&atilde;o encontrada\n-->";
      $opcoes = array( "" => "Erro na gera��o" );
    }
    $tamanho = sizeof($opcoes_);
    echo "<script>\nvar cod_tipo_usuario = new Array({$tamanho});\n";
    foreach ($opcoes_ as $key => $valor)
      echo "cod_tipo_usuario[{$key}] = {$valor};\n";
    echo "</script>";

    $this->campoLista( "ref_cod_tipo_usuario", "Tipo usu&aacute;rio", $opcoes, $this->ref_cod_tipo_usuario,"",null,null,null,null,false );

    Portabilis_View_Helper_Application::loadJavascript($this, '/modules/ExportarUsuarios/exportarUsuarios.js');

    $this->nome_url_sucesso = "Exportar";
    $this->acao_enviar      = " ";
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
?>
