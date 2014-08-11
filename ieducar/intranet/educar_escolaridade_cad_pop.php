<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	*																	     *
	*	@author Prefeitura Municipal de Itaja�								 *
	*	@updated 29/03/2007													 *
	*   Pacote: i-PLB Software P�blico Livre e Brasileiro					 *
	*																		 *
	*	Copyright (C) 2006	PMI - Prefeitura Municipal de Itaja�			 *
	*						ctima@itajai.sc.gov.br					    	 *
	*																		 *
	*	Este  programa  �  software livre, voc� pode redistribu�-lo e/ou	 *
	*	modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme	 *
	*	publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da	 *
	*	Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.	 *
	*																		 *
	*	Este programa  � distribu�do na expectativa de ser �til, mas SEM	 *
	*	QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-	 *
	*	ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-	 *
	*	sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.	 *
	*																		 *
	*	Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU	 *
	*	junto  com  este  programa. Se n�o, escreva para a Free Software	 *
	*	Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA	 *
	*	02111-1307, USA.													 *
	*																		 *
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/**
 * @author Adriano Erik Weiguert Nagasava
 */
require_once ("include/clsBase.inc.php");
require_once ("include/clsCadastro.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/Geral.inc.php" );
require_once( "include/pmieducar/geral.inc.php" );
require_once 'lib/Portabilis/String/Utils.php';

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Escolaridade" );
		$this->processoAp = "632";
		$this->renderBanner = false;
		$this->renderMenu = false;
		$this->renderMenuSuspenso = false;
	}
}

class indice extends clsCadastro
{
	/**
	 * Referencia pega da session para o idpes do usuario atual
	 *
	 * @var int
	 */
	var $pessoa_logada;

	var $idesco;
	var $descricao;
	var $escolaridade;

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$this->idesco=$_GET["idesco"];

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 632, $this->pessoa_logada, 4,  "educar_escolaridade_lst.php" );

		if( is_numeric( $this->idesco ) )
		{

			$obj = new clsCadastroEscolaridade( $this->idesco );
			$registro  = $obj->detalhe();
			if( $registro )
			{
				foreach( $registro AS $campo => $val )	// passa todos os valores obtidos no registro para atributos do objeto
					$this->$campo = $val;

				if( $obj_permissoes->permissao_excluir( 632, $this->pessoa_logada, 3 ) )
				{
					$this->fexcluir = true;
				}

				$retorno = "Editar";
			}
		}
//		$this->url_cancelar = ($retorno == "Editar") ? "educar_escolaridade_det.php?idesco={$registro["idesco"]}" : "educar_escolaridade_lst.php";
		$this->nome_url_cancelar = "Cancelar";
		$this->script_cancelar = "window.parent.fechaExpansivel(\"div_dinamico_\"+(parent.DOM_divs.length-1));";
		return $retorno;
	}

	function Gerar()
	{
		// primary keys
		$this->campoOculto( "idesco", $this->idesco );	

		// text
		$this->campoTexto( "descricao", "Descri&ccedil;&atilde;o", $this->descricao, 30, 255, true );
		
  		$resources = array(1 => 'Fundamental incompleto',
		                   2 => 'Fundamental completo',
		                   3 => 'Ensino m�dio - Normal/Magist�rio',
		                   4 => 'Ensino m�dio - Normal/Magist�rio Ind�gena',
		                   5 => 'Ensino m�dio',
		                   6 => 'Superior');

	    $options = array('label' => Portabilis_String_Utils::toLatin1('Escolaridade'), 'resources' => $resources, 'value' => $this->escolaridade);
	    $this->inputsHelper()->select('escolaridade', $options);


	}

	function Novo()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj = new clsCadastroEscolaridade( null, $this->descricao, $this->escolaridade );
		$cadastrou = $obj->cadastra();
		if( $cadastrou )
		{
			echo "<script>
						parent.document.getElementById('ref_idesco').options[parent.document.getElementById('ref_idesco').options.length] = new Option('$this->descricao', '$cadastrou', false, false);
						parent.document.getElementById('ref_idesco').value = '$cadastrou';
						window.parent.fechaExpansivel('div_dinamico_'+(parent.DOM_divs.length-1));
			     	</script>";
			die();
			return true;
		}

		$this->mensagem = "Cadastro n&atilde;o realizado.<br>";
		echo "<!--\nErro ao cadastrar clsCadastroEscolaridade\nvalores obrigatorios\nis_numeric( $this->idesco ) && is_string( $this->descricao )\n-->";
		return false;
	}

	function Editar()
	{
	}

	function Excluir()
	{		
	}
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm( $miolo );
// gera o html
$pagina->MakeAll();
?>