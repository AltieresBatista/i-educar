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
 * @author    Alan Felipe Farias <alan@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */



require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/public/geral.inc.php';

require_once 'App/Date/Utils.php';

require_once 'ComponenteCurricular/Model/TurmaDataMapper.php';


class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Unifica&ccedil;&atilde;o de alunos');
    $this->processoAp = "999847";
    $this->addEstilo("localizacaoSistema");
  }
}


class indice extends clsCadastro
{
  var $pessoa_logada;

  var $tabela_alunos = array();
  var $aluno_duplicado; 

  function Inicializar()
  {
    $retorno = 'Novo';

    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(999847, $this->pessoa_logada, 7,
      'index.php'); 

    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         ""        => "Unifica&ccedil;&atilde;o de alunos"             
    ));
    $this->enviaLocalizacao($localizacao->montar());

    return $retorno;
  }

  function Gerar()
  {

      $this->inputsHelper()->dynamic('ano', array('required' => false, 'max_length' => 4));
      $this->inputsHelper()->dynamic('instituicao',  array('required' =>  false, 'show-select' => true));
      $this->inputsHelper()->dynamic('escola',  array('required' =>  false, 'show-select' => true, 'value' => 0));
      $this->inputsHelper()->simpleSearchAluno(null,array('label' => 'Aluno principal' ));
      $this->campoTabelaInicio("tabela_alunos","",array("Aluno duplicado"),$this->tabela_alunos);
      $this->campoTexto( "aluno_duplicado", "Aluno duplicado", $this->aluno_duplicado, 50, 255, false, true, false, '', '', '', 'onfocus' );
      $this->campoTabelaFim();

  }

  function Novo()
  {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $obj_permissoes = new clsPermissoes();
    $obj_permissoes->permissao_cadastra(999847, $this->pessoa_logada, 7,
      'index.php');

    $cod_aluno_principal = $this->aluno_id;

    if (!$cod_aluno_principal) return;

    //Monta um array com o c�digo dos alunos selecionados na tabela
    foreach ($this->aluno_duplicado as $key => $value) {
      $explode = explode(" ", $value);

      if($explode[0] == $cod_aluno_principal){
        $this->mensagem = 'Impossivel de unificar alunos iguais.<br />';
        return false;
      }

      $cod_alunos[] = $explode[0];
    }

    $cod_alunos = implode(",", $cod_alunos);

    $db = new clsBanco();
    $db->consulta("UPDATE pmieducar.historico_escolar
                      SET ref_cod_aluno = {$cod_aluno_principal},
                          sequencial = he.seq+he.max_seq
                      FROM
                        (SELECT ref_cod_aluno AS aluno,
                                sequencial AS seq,
                           COALESCE((SELECT max(sequencial)
                            FROM pmieducar.historico_escolar
                            WHERE ref_cod_aluno = {$cod_aluno_principal}),0) AS max_seq
                         FROM pmieducar.historico_escolar
                         WHERE ref_cod_aluno IN ({$cod_alunos})) AS he
                      WHERE sequencial = he.seq
                        AND ref_cod_aluno = he.aluno");
    $db->consulta("UPDATE pmieducar.matricula SET ref_cod_aluno = {$cod_aluno_principal} where ref_cod_aluno in ({$cod_alunos})");
    $db->consulta("UPDATE pmieducar.aluno SET ativo = 0, data_exclusao = now(), ref_usuario_exc = {$this->pessoa_logada} where cod_aluno in ({$cod_alunos})");

    $this->mensagem = "<span class='success'>Alunos unificados com sucesso.</span>";
    return true;
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
<script type="text/javascript">

  var handleSelect = function(event, ui){
    $j(event.target).val(ui.item.label);
    return false;
  };

  var search = function(request, response) {
    var searchPath = '/module/Api/Aluno?oper=get&resource=aluno-search';
    var params     = { query : request.term };

    $j.get(searchPath, params, function(dataResponse) {
      simpleSearch.handleSearch(dataResponse, response);
    });
  };

  function setAutoComplete() {
    $j.each($j('input[id^="aluno_duplicado"]'), function(index, field) {

      $j(field).autocomplete({
        source    : search,
        select    : handleSelect,
        minLength : 1,
        autoFocus : true
      });

    });
  }

  setAutoComplete();  

  // bind events

  var $addPontosButton = $j('#btn_add_tab_add_1');

  $addPontosButton.click(function(){
    setAutoComplete();
  });

$j('#btn_enviar').val('Unificar');


</script>