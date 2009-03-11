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

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Tipo Avaliacao" );
		$this->processoAp = "560";
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

	var $cod_tipo_avaliacao;
	var $ref_usuario_exc;
	var $ref_usuario_cad;
	var $nm_tipo;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;
	var $ref_cod_instituicao;

	function Gerar()
	{
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->titulo = "Tipo Avaliacao - Detalhe";
		///$this->addBanner( "imagens/nvp_top_intranet.jpg", "imagens/nvp_vert_intranet.jpg", "Intranet" );

		$this->cod_tipo_avaliacao=$_GET["cod_tipo_avaliacao"];

		$tmp_obj = new clsPmieducarTipoAvaliacao( $this->cod_tipo_avaliacao );
		$registro = $tmp_obj->detalhe();

		if( ! $registro )
		{
			header( "location: educar_tipo_avaliacao_lst.php" );
			die();
		}

		if( class_exists( "clsPmieducarUsuario" ) )
		{
			$obj_ref_usuario_exc = new clsPmieducarUsuario( $registro["ref_usuario_exc"] );
			$det_ref_usuario_exc = $obj_ref_usuario_exc->detalhe();
			$registro["ref_usuario_exc"] = $det_ref_usuario_exc["data_cadastro"];
		}
		else
		{
			$registro["ref_usuario_exc"] = "Erro na geracao";
			echo "<!--\nErro\nClasse nao existente: clsPmieducarUsuario\n-->";
		}

		if( class_exists( "clsPmieducarUsuario" ) )
		{
			$obj_ref_usuario_cad = new clsPmieducarUsuario( $registro["ref_usuario_cad"] );
			$det_ref_usuario_cad = $obj_ref_usuario_cad->detalhe();
			$registro["ref_usuario_cad"] = $det_ref_usuario_cad["data_cadastro"];
		}
		else
		{
			$registro["ref_usuario_cad"] = "Erro na geracao";
			echo "<!--\nErro\nClasse nao existente: clsPmieducarUsuario\n-->";
		}


		if( $registro["cod_tipo_avaliacao"] )
		{
			$this->addDetalhe( array( "Tipo Avaliac&atilde;o", "{$registro["cod_tipo_avaliacao"]}") );
		}
		if( $registro["nm_tipo"] )
		{
			$this->addDetalhe( array( "Nome Tipo", "{$registro["nm_tipo"]}") );
		}
		if( $registro["ref_cod_instituicao"] )
		{
			if( class_exists( "clsPmieducarInstituicao" ) )
			{
				$obj_cod_instituicao = new clsPmieducarInstituicao( $registro["ref_cod_instituicao"] );
				$obj_cod_instituicao_det = $obj_cod_instituicao->detalhe();
				$registro["ref_cod_instituicao"] = $obj_cod_instituicao_det["nm_instituicao"];
			}
			else
			{
				$registro["ref_cod_instituicao"] = "Erro na gera&ccedil;&atilde;o";
				echo "<!--\nErro\nClasse n&atilde;o existente: clsPmieducarInstituicao\n-->";
			}
			$this->addDetalhe( array( "Institui&ccedil;&atilde;o", "{$registro["ref_cod_instituicao"]}") );
		}
		if( is_numeric( $registro["conceitual"] ) ) {
			$conceitual = ( $registro["conceitual"] == 0 ) ? "N�o" : "Sim";
			$this->addDetalhe( array( "Conceitual", "{$conceitual}" ) );
		}
		$obj = new clsPmieducarTipoAvaliacaoValores( $this->cod_tipo_avaliacao );
		$lst = $obj->lista( $this->cod_tipo_avaliacao );
		if($lst)
		{
			$tabela = "<TABLE>
					       <TR align=center>
					           <TD bgcolor=#A1B3BD><B>Nome</B></TD>
					           <TD bgcolor=#A1B3BD><B>Nota</B></TD>
					           <TD bgcolor=#A1B3BD><B>Nota M�n.</B></TD>
					           <TD bgcolor=#A1B3BD><B>Nota M�x.</B></TD>
					       </TR>";
			$cont = 0;
			foreach ( $lst AS $valor ) {
				if ( ($cont % 2) == 0 ) {
					$color = " bgcolor=#FFFFFF ";
				}
				else {
					$color = "";
				}
				$tabela .= "<TR>
							    <TD {$color} align=left>{$valor["nome"]}</TD>
							    <TD {$color} align=right>{$valor["valor"]}</TD>
							    <TD {$color} align=right>{$valor["valor_min"]}</TD>
							    <TD {$color} align=right>{$valor["valor_max"]}</TD>
							</TR>";
				$cont++;
			}
			$tabela .= "</TABLE>";

			$this->addDetalhe( array( "Tipo de Avalia��o (Valores)", $tabela ) );
		}
		$obj_permissoes = new clsPermissoes();
		if ( $obj_permissoes->permissao_cadastra( 560, $this->pessoa_logada, 3 ) ) {
			$this->url_novo = "educar_tipo_avaliacao_cad.php";
			$this->url_editar = "educar_tipo_avaliacao_cad.php?cod_tipo_avaliacao={$registro["cod_tipo_avaliacao"]}";
		}
		$this->url_cancelar = "educar_tipo_avaliacao_lst.php";
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