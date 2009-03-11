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
require_once ("include/clsBase.inc.php");
require_once ("include/clsCadastro.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/pmieducar/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Escola Ano Letivo" );
		$this->processoAp = "561";
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

	var $ref_cod_escola;
	var $ano;
	var $ref_usuario_cad;
	var $ref_usuario_exc;
	var $andamento;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$this->ano=$_GET["ano"];
		$this->ref_cod_escola=$_GET["cod_escola"];

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 561, $this->pessoa_logada, 7,  "educar_escola_lst.php" );

		$this->nome_url_sucesso = "Continuar";
		$this->url_cancelar = "educar_escola_det.php?cod_escola={$this->ref_cod_escola}";
		$this->nome_url_cancelar = "Cancelar";
		return $retorno;
	}

	function Gerar()
	{
		// primary keys
		$this->campoOculto( "ref_cod_escola", $this->ref_cod_escola );
		$this->campoOculto( "ano", $this->ano );

		$obj_anos = new clsPmieducarEscolaAnoLetivo();
		$lista_ano = $obj_anos->lista($this->ref_cod_escola,null,null,null,2,null,null,null,null,1);

		$ano_array = array();

		if($lista_ano)
		{
			foreach ($lista_ano as $ano) {
				$ano_array["{$ano['ano']}"] = $ano['ano'];
			}
		}


		$ano_atual = date("Y");

		// foreign keys
		$opcoes = array( "" => "Selecione" );
		$lim = 5;
		for ( $i=0; $i < $lim; $i++ )
		{
			$ano = $ano_atual + $i;
			if(!key_exists($ano,$ano_array))
				$opcoes["{$ano}"] = "{$ano}";
			else
				$lim++;
		}
		$this->campoLista( "ano", "Ano", $opcoes, $this->ano );
	}

	function Novo()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 561, $this->pessoa_logada, 7,  "educar_escola_lst.php" );

		header( "Location: educar_ano_letivo_modulo_cad.php?ref_cod_escola={$this->ref_cod_escola}&ano={$this->ano}" );
		die();
		return true;
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