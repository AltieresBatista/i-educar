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
require_once ("include/clsListagem.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/pmieducar/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Projeto" );
		$this->processoAp = "21250";
		$this->addEstilo("localizacaoSistema");
	}
}

class indice extends clsListagem
{
	/**
	 * Referencia pega da session para o idpes do usuario atual
	 *
	 * @var int
	 */
	var $pessoa_logada;

	/**
	 * Titulo no topo da pagina
	 *
	 * @var int
	 */
	var $titulo;

	/**
	 * Quantidade de registros a ser apresentada em cada pagina
	 *
	 * @var int
	 */
	var $limite;

	/**
	 * Inicio dos registros a serem exibidos (limit)
	 *
	 * @var int
	 */
	var $offset;

	var $cod_projeto;
	var $nome;
	var $observacao;

	function Gerar()
	{
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->titulo = "Projetos - Listagem";

		foreach( $_GET AS $var => $val )
			$this->$var = ( $val === "" ) ? null: $val;



		$this->addCabecalhos( array(
			"Nome do projeto",
			'Observa��o'
		) );

		$this->campoTexto( "nome", "Nome do projeto", $this->nome, 30, 255, false );


		// Paginador
		$this->limite = 20;
		$this->offset = ( $_GET["pagina_{$this->nome}"] ) ? $_GET["pagina_{$this->nome}"]*$this->limite-$this->limite: 0;

		$obj_projeto = new clsPmieducarProjeto();
		$obj_projeto->setOrderby( "nome ASC" );
		$obj_projeto->setLimite( $this->limite, $this->offset );

		$lista = $obj_projeto->lista(
			null,
			$this->nome
		);

		$total = $obj_projeto->_total;

		// monta a lista
		if( is_array( $lista ) && count( $lista ) )
		{
			foreach ( $lista AS $registro )
			{

				$this->addLinhas( array(
					"<a href=\"educar_projeto_det.php?cod_projeto={$registro["cod_projeto"]}\">{$registro["nome"]}</a>",
					"<a href=\"educar_projeto_det.php?cod_projeto={$registro["cod_projeto"]}\">{$registro["observacao"]}</a>"
				) );
			}
		}
		$this->addPaginador2( "educar_projeto_lst.php", $total, $_GET, $this->nome, $this->limite );


		//** Verificacao de permissao para cadastro
		$obj_permissao = new clsPermissoes();

		if($obj_permissao->permissao_cadastra(21250, $this->pessoa_logada,3))
		{
			$this->acao = "go(\"educar_projeto_cad.php\")";
			$this->nome_acao = "Novo";
		}
		//**

		$this->largura = "100%";

	    $localizacao = new LocalizacaoSistema();
	    $localizacao->entradaCaminhos( array(
	         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
	         "educar_index.php"                  => "i-Educar - Escola",
	         ""                                  => "Listagem de projetos"
	    ));
	    $this->enviaLocalizacao($localizacao->montar());
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