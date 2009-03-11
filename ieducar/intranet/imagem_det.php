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
$desvio_diretorio = "";
require_once ("include/clsBase.inc.php");
require_once ("include/clsDetalhe.inc.php");
require_once ("include/clsBanco.inc.php");
require_once ("include/imagem/clsPortalImagemTipo.inc.php");
require_once ("include/imagem/clsPortalImagem.inc.php");
class clsIndex extends clsBase
{
	
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} Banco de Imagens" );
		$this->processoAp = "473";
	}
}

class indice extends clsDetalhe
{
	function Gerar()
	{
		$this->titulo = "Detalhe da Imagem";
		$this->addBanner( "imagens/nvp_top_intranet.jpg", "imagens/nvp_vert_intranet.jpg", "Intranet" );

		$cod_imagem = @$_GET['cod_imagem'];

		$objimagem = new clsPortalImagem($cod_imagem);
		$detalheImagem = $objimagem->detalhe();
		$objimagemTipo = new clsPortalImagemTipo($detalheImagem['ref_cod_imagem_tipo']);
		$detalheImagemTipo = $objimagemTipo->detalhe();
		
		$this->addDetalhe( array("Tipo da Imagem", $detalheImagemTipo['nm_tipo']));
		$this->addDetalhe( array("Nome", $detalheImagem['nm_imagem']));
		$this->addDetalhe( array("Imagem", "<img src='banco_imagens/{$detalheImagem['caminho']}' alt='{$detalheImagem['nm_imagem']}' title='{$detalheImagem['nm_imagem']}'>"));
		$this->addDetalhe( array("Extens�o", "{$detalheImagem['extensao']}"));
		$this->addDetalhe( array("Largura", "{$detalheImagem['largura']}"));
		$this->addDetalhe( array("Altura", "{$detalheImagem['altura']}"));
		$this->addDetalhe( array("Data de Cadastro", date("d/m/Y", strtotime(substr($detalheImagem['altura'],0,19)) )));		
		$this->url_novo = "imagem_cad.php";
		$this->url_editar = "imagem_cad.php?cod_imagem={$cod_imagem}";
		$this->url_cancelar = "imagem_lst.php";

		$this->largura = "100%";
	}
}


$pagina = new clsIndex();

$miolo = new indice();
$pagina->addForm( $miolo );

$pagina->MakeAll();

?>