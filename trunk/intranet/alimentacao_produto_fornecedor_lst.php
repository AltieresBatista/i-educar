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
require_once( "include/alimentacao/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Produto Fornecedor " );
		$this->processoAp = "10004";
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
	
	var $ref_produto;
	var $ref_fornecedor;

	function Gerar()
	{
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->titulo = "Produto Fornecedor - Listagem";

		foreach( $_GET AS $var => $val ) // passa todos os valores obtidos no GET para atributos do objeto
			$this->$var = ( $val === "" ) ? null: $val;

		$this->addBanner( "imagens/nvp_top_intranet.jpg", "imagens/nvp_vert_intranet.jpg", "Intranet" );

		$obj_permissao = new clsPermissoes();
		
		$lista_busca = array(
					"Produto",
					"Fornecedor",
					"Ano",
					"M�s In�cio",
					"M�s Fim",
					"Quantidade por Un.",
					"Pre�o Un."
		);

		$this->addCabecalhos($lista_busca);

		$opcoes = array();
		$obj_produto = new clsAlimentacaoProduto();
		$lista = $obj_produto->lista();
		
		$opcoes["0"] = "Todos"; 
		if( is_array( $lista ) && count( $lista ) )
		{
			foreach ( $lista AS $registro )
			{
				$opcoes[$registro["idpro"]] = $registro["nm_produto"];
			}
			
		}
		
		$this->campoLista( "ref_produto", "Produto", $opcoes, $this->ref_produto,"",false,"","","",false );
		
		$opcoes = array();
		$obj_fornecedor = new clsAlimentacaoFornecedor();
		$lista = $obj_fornecedor->lista();
		
		$opcoes["0"] = "Todos"; 
		if( is_array( $lista ) && count( $lista ) )
		{
			foreach ( $lista AS $registro )
			{
				$opcoes[$registro["idpes"]] = $registro["fantasia"];
			}
			
		}
		
		$this->campoLista( "ref_fornecedor", "Fornecedor", $opcoes, $this->ref_fornecedor,"",false,"","","",false );
		
		
		$obj_produto_fornecedor = new clsAlimentacaoProdutoFornecedor();
		$filtro_produto = null;
		if($this->ref_produto > 0)
		{
			$filtro_produto = $this->ref_produto;
		}
		$filtro_fornecedor = null;
		if($this->ref_fornecedor > 0)
		{
			$filtro_fornecedor = $this->ref_fornecedor;
		}
		$lista = $obj_produto_fornecedor->lista(null,$filtro_produto,$filtro_fornecedor);
		
		if( is_array( $lista ) && count( $lista ) )
		{
			foreach ( $lista AS $registro )
			{
				
				$lista_busca = array();
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">{$registro["nm_produto"]}</a>";
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">{$registro["fantasia"]}</a>";
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">{$registro["ano"]}</a>";
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">{$obj_produto_fornecedor->getMes($registro["mes_inicio"])}</a>";
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">{$obj_produto_fornecedor->getMes($registro["mes_fim"])}</a>";
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">".number_format($registro["pesoouvolume_un"],2,',','')." {$registro["unidade"]}</a>";
				$lista_busca[] = "<a href=\"alimentacao_produto_fornecedor_det.php?idpf={$registro["idpf"]}\">R$".str_replace(".",",",$registro["preco_un"])."</a>";
				$this->addLinhas($lista_busca);
			}
			
		}

		if( $obj_permissao->permissao_cadastra( 10004, $this->pessoa_logada, 3 ) )
		{		
			$this->acao = "go(\"alimentacao_produto_fornecedor_cad.php\")";
			$this->nome_acao = "Novo";
		}
		
		$this->largura = "100%";
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