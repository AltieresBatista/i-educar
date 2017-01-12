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
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
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
    $this->SetTitulo($this->_instituicao . ' i-Educar - Exporta&ccedil;&atilde;o Educacenso');
    $this->processoAp = ($_REQUEST['fase2'] == 1 ? 9998845 : 846);
    $this->addEstilo('localizacaoSistema');
  }
}

class indice extends clsCadastro
{
  var $pessoa_logada;

  var $ano;
  var $ref_cod_instituicao;
  var $segunda_fase = false;

  function Inicializar()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $this->segunda_fase = ($_REQUEST['fase2'] == 1);

    $codigoMenu = ($this->segunda_fase ? 9998845 : 846);

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra($codigoMenu, $this->pessoa_logada, 7,
      'educar_index.php');
    $this->ref_cod_instituicao = $obj_permissoes->getInstituicao($this->pessoa_logada);

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "Educacenso",
         ""                                  => "Exporta&ccedil;&atilde;o para o Educacenso"
    ));
    $this->enviaLocalizacao($localizacao->montar());

    $exportacao = $_POST["exportacao"];

    if ($exportacao) {
      $decoded_a = urldecode($exportacao);
      $converted_to_latin = Portabilis_String_Utils::toLatin1($exportacao);

      header('Content-type: text/plain');
      header('Content-Length: ' . strlen($converted_to_latin));
      header('Content-Disposition: attachment; filename=exportacao.txt');
      echo $converted_to_latin;
      die();
    }

    return 'Nova exporta��o';
  }

  function Gerar()
  {
    $fase2 = $_REQUEST['fase2'];

    $this->inputsHelper()->dynamic(array('ano', 'instituicao', 'escola'));
    $this->inputsHelper()->date('data_ini',array( 'label' => Portabilis_String_Utils::toLatin1('Data in�cio'), 'value' => $this->data_ini, 'dica' => ($fase2 == 1) ? 'A data informada neste campo, dever� ser a mesma informada na 1� fase da exporta��o (Matr�cula inicial).' : 'dd/mm/aaaa'));
    $this->inputsHelper()->date('data_fim',array( 'label' => 'Data fim', 'value' => $this->data_fim, 'dica' => ($fase2 == 1) ? 'A data informada neste campo, dever� ser a mesma informada na 1� fase da exporta��o (Matr�cula inicial).' : 'dd/mm/aaaa'));

    Portabilis_View_Helper_Application::loadJavascript($this, '/modules/Educacenso/Assets/Javascripts/Educacenso.js');

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
<script type="text/javascript">

function marcarCheck(idValue) {
    // testar com formcadastro
    var contaForm = document.formcadastro.elements.length;
    var campo = document.formcadastro;
    var i;

    for (i=0; i<contaForm; i++) {
        if (campo.elements[i].id == idValue) {

            campo.elements[i].checked = campo.CheckTodos.checked;
        }
    }
}
</script>
