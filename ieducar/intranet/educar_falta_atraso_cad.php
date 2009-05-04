<?php
/**
 *
 * @author  Prefeitura Municipal de Itaja�
 * @version SVN: $Id$
 *
 * Pacote:  i-PLB Software P�blico Livre e Brasileiro
 *
 * Copyright (C) 2006 PMI - Prefeitura Municipal de Itaja�
 *            ctima@itajai.sc.gov.br
 *
 * Este  programa  �  software livre, voc� pode redistribu�-lo e/ou
 * modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme
 * publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da
 * Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.
 *
 * Este programa  � distribu�do na expectativa de ser �til, mas SEM
 * QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-
 * ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-
 * sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.
 *
 * Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU
 * junto  com  este  programa. Se n�o, escreva para a Free Software
 * Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA
 * 02111-1307, USA.
 *
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Falta Atraso" );
		$this->processoAp = "635";
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

	var $cod_falta_atraso;
	var $ref_cod_escola;
	var $ref_cod_instituicao;
	var $ref_usuario_exc;
	var $ref_usuario_cad;
	var $ref_cod_servidor;
	var $tipo;
	var $data_falta_atraso;
	var $qtd_horas;
	var $qtd_min;
	var $justificada;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$this->cod_falta_atraso    = $_GET["cod_falta_atraso"];
		$this->ref_cod_servidor    = $_GET["ref_cod_servidor"];
		$this->ref_cod_escola	   = $_GET["ref_cod_escola"];
		$this->ref_cod_instituicao = $_GET["ref_cod_instituicao"];

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 635, $this->pessoa_logada, 7,  "educar_falta_atraso_lst.php" );

		if( is_numeric( $this->cod_falta_atraso ) )
		{

			$obj = new clsPmieducarFaltaAtraso( $this->cod_falta_atraso );
			$registro  = $obj->detalhe();
			if( $registro )
			{
				foreach( $registro AS $campo => $val )	// passa todos os valores obtidos no registro para atributos do objeto
					$this->$campo = $val;
				$this->data_falta_atraso = dataFromPgToBr( $this->data_falta_atraso );

			$obj_permissoes = new clsPermissoes();
			if( $obj_permissoes->permissao_excluir( 635, $this->pessoa_logada, 7 ) )
			{
				$this->fexcluir = true;
			}

				$retorno = "Editar";
			}
		}
		$this->url_cancelar = ($retorno == "Editar") ? "educar_falta_atraso_det.php?cod_falta_atraso={$registro["cod_falta_atraso"]}" : "educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}";
		$this->nome_url_cancelar = "Cancelar";
		return $retorno;
	}

	function Gerar()
	{
		// primary keys
		$this->campoOculto( "cod_falta_atraso", $this->cod_falta_atraso );
		$this->campoOculto( "ref_cod_servidor", $this->ref_cod_servidor );

		// foreign keys
		$obrigatorio 	 = true;
		$get_instituicao = true;
		$get_escola		 = true;
		include("include/pmieducar/educar_campo_lista.php");

		// text
		$opcoes = array( "" => "Selecione", "1" => "Atraso", "2" => "Falta" );
		$this->campoLista( "tipo", "Tipo", $opcoes, $this->tipo );

		$this->campoNumero( "qtd_horas", "Quantidade de Horas", $this->qtd_horas, 30, 255, false );
		$this->campoNumero( "qtd_min", "Quantidade de Minutos", $this->qtd_min, 30, 255, false );

		$opcoes = array( "" => "Selecione", "0" => "Sim", "1" => "N�o" );
		$this->campoLista( "justificada", "Justificada", $opcoes, $this->justificada );

		// data
		$this->campoData( "data_falta_atraso", "Dia", $this->data_falta_atraso, true );
	}

	function Novo()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 635, $this->pessoa_logada, 7,  "educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}" );

		if ( $this->tipo == 1 )
			$obj = new clsPmieducarFaltaAtraso( null, $this->ref_cod_escola, $this->ref_cod_instituicao, null, $this->pessoa_logada, $this->ref_cod_servidor, $this->tipo, $this->data_falta_atraso, $this->qtd_horas, $this->qtd_min, $this->justificada, null, null, 1 );
		elseif ( $this->tipo == 2 )
		{
			$db = new clsBanco();
			$dia_semana = $db->CampoUnico( "SELECT EXTRACT ( DOW FROM ( date '".dataToBanco( $this->data_falta_atraso )."' ) + 1 )" );

			$obj_ser = new clsPmieducarServidor();
			$horas   = $obj_ser->qtdhoras( $this->ref_cod_servidor, $this->ref_cod_escola, $this->ref_cod_instituicao, $dia_semana );
			if ( $horas )
			{
				$obj = new clsPmieducarFaltaAtraso( null, $this->ref_cod_escola, $this->ref_cod_instituicao, null, $this->pessoa_logada, $this->ref_cod_servidor, $this->tipo, $this->data_falta_atraso, $horas["hora"], $horas["min"], $this->justificada, null, null, 1 );
			}
		}
		$cadastrou = $obj->cadastra();
		if( $cadastrou )
		{
			$this->mensagem .= "Cadastro efetuado com sucesso.<br>";
			header( "Location: educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}" );
			die();
			return true;
		}

		$this->mensagem = "Cadastro n&atilde;o realizado.<br>";
		echo "<!--\nErro ao cadastrar clsPmieducarFaltaAtraso\nvalores obrigatorios\nis_numeric( $this->ref_cod_escola ) && is_numeric( $this->ref_ref_cod_instituicao ) && is_numeric( $this->ref_usuario_exc ) && is_numeric( $this->ref_usuario_cad ) && is_numeric( $this->ref_cod_servidor ) && is_numeric( $this->tipo ) && is_string( $this->data_falta_atraso ) && is_numeric( $this->justificada )\n-->";
		return false;
	}

	function Editar()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 635, $this->pessoa_logada, 7,  "educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}" );

		if ( $this->tipo == 1 ) {
			$obj = new clsPmieducarFaltaAtraso( null, $this->ref_cod_escola, $this->ref_cod_instituicao, $this->pessoa_logada, null, $this->ref_cod_servidor, $this->tipo, $this->data_falta_atraso, $this->qtd_horas, $this->qtd_min, $this->justificada, null, null, 1 );
		}
		elseif ( $this->tipo == 2 ) {
			$obj_ser = new clsPmieducarServidor( $this->ref_cod_servidor, null, null, null, null, null, 1, $this->ref_cod_instituicao );
			$det_ser = $obj_ser->detalhe();
			$horas   = floor( $det_ser["carga_horaria"] );
			$minutos = ( $det_ser["carga_horaria"] - $horas ) * 60;
			$obj = new clsPmieducarFaltaAtraso( null, $this->ref_cod_escola, $this->ref_cod_instituicao, $this->pessoa_logada, null, $this->ref_cod_servidor, $this->tipo, $this->data_falta_atraso, $horas, $minutos, $this->justificada, null, null, 1 );
		}
		$editou = $obj->edita();
		if( $editou )
		{
			$this->mensagem .= "Edi&ccedil;&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}" );
			die();
			return true;
		}

		$this->mensagem = "Edi&ccedil;&atilde;o n&atilde;o realizada.<br>";
		echo "<!--\nErro ao editar clsPmieducarFaltaAtraso\nvalores obrigatorios\nif( is_numeric( $this->cod_falta_atraso ) && is_numeric( $this->ref_usuario_exc ) )\n-->";
		return false;
	}

	function Excluir()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_excluir( 635, $this->pessoa_logada, 7,  "educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}" );


		$obj = new clsPmieducarFaltaAtraso($this->cod_falta_atraso, $this->ref_cod_escola, $this->ref_ref_cod_instituicao, $this->pessoa_logada, $this->pessoa_logada, $this->ref_cod_servidor, $this->tipo, $this->data_falta_atraso, $this->qtd_horas, $this->qtd_min, $this->justificada, $this->data_cadastro, $this->data_exclusao, 0);
		$excluiu = $obj->excluir();
		if( $excluiu )
		{
			$this->mensagem .= "Exclus&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_falta_atraso_lst.php?ref_cod_servidor={$this->ref_cod_servidor}&ref_cod_instituicao={$this->ref_cod_instituicao}" );
			die();
			return true;
		}

		$this->mensagem = "Exclus&atilde;o n&atilde;o realizada.<br>";
		echo "<!--\nErro ao excluir clsPmieducarFaltaAtraso\nvalores obrigatorios\nif( is_numeric( $this->cod_falta_atraso ) && is_numeric( $this->ref_usuario_exc ) )\n-->";
		return false;
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
<script>
 	var obj_tipo = document.getElementById( 'tipo' );
 	obj_tipo.onchange = function() {
 		if ( document.getElementById( 'tipo' ).value == 1 ) {
 			setVisibility( 'tr_qtd_horas', true );
 			setVisibility( 'tr_qtd_min', true );
 		}
 		else if ( document.getElementById( 'tipo' ).value == 2 ) {
 			setVisibility( 'tr_qtd_horas', false );
 			setVisibility( 'tr_qtd_min', false );
 		}
 	}
</script>