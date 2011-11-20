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
require_once ("include/clsDetalhe.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/pmieducar/geral.inc.php" );
require_once( "include/alimentacao/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Card�pio" );
		$this->processoAp = "10000";
	}
}

class indice extends clsDetalhe
{
	/**
	 * Titulo no topo da pagina
	 *
	 * @var int
	 */
	var $titulo;

	var $idcar;
	
	function Gerar()
	{
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->titulo = "Card�pio - Detalhe";
		$this->addBanner( "imagens/nvp_top_intranet.jpg", "imagens/nvp_vert_intranet.jpg", "Intranet" );

		$this->idcar=$_GET["idcar"];
		
		$obj_cardapio = new clsAlimentacaoCardapio();
		$lista = $obj_cardapio->lista($this->idcar,null);
		$registro = $lista[0];

		if( ! $registro )
		{
			header( "location: alimentacao_cardapio_lst.php" );
			die();
		}
		
		$this->addDetalhe( array( "Descri��o", $registro["descricao"]) );

		$this->addDetalhe( array( "Arquivo", "<a target=\"blank\" href=\"{$registro["path_arquivo"]}\">{$registro["nm_arquivo"]}</a>") );

        $this->addDetalhe( array( "Data Cadastro", date('d/m/Y',strtotime($registro["dt_cadastro"]))) );
		
		$obj_ref_cod_escola = new clsPmieducarEscola( $registro["ref_escola"] );
		$det_ref_cod_escola = $obj_ref_cod_escola->detalhe();
		$nm_escola = $det_ref_cod_escola["nome"];
				
		$obj_pessoa = new clsPessoa_($registro["ref_usuario_cad"]);
		$det_pessoa = $obj_pessoa->detalhe();
		$nm_pessoa = $det_pessoa["nome"];
		
		$this->addDetalhe( array( "Usu�rio", $nm_pessoa) );
		$this->addDetalhe( array( "Escola", $nm_escola) );
		
		

		$obj_permissoes = new clsPermissoes();
		if( $obj_permissoes->permissao_cadastra( 10000, $this->pessoa_logada, 3 ) )
		{
			$this->url_novo = "alimentacao_cardapio_cad.php";
            if ($registro["ref_usuario_cad"] == $this->pessoa_logada)
            {
                $this->url_editar = "alimentacao_cardapio_cad.php?idcar={$this->idcar}";
            }
		}

		$this->url_cancelar = "alimentacao_cardapio_lst.php";
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