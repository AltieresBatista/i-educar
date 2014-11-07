<?php
/**
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
require_once 'lib/Portabilis/View/Helper/Application.php';

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Distribui&ccedil;&atilde;o de uniforme" );
		$this->processoAp = "578";
		$this->addEstilo('localizacaoSistema');
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

	var $cod_distribuicao_uniforme;
	var $ref_cod_aluno;
	var $ano;
  var $agasalho_qtd;
  var $camiseta_curta_qtd;
  var $camiseta_longa_qtd;
  var $meias_qtd;
  var $bermudas_tectels_qtd;
  var $bermudas_coton_qtd;
  var $tenis_qtd;

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$this->cod_distribuicao_uniforme=$_GET["cod_distribuicao_uniforme"];
		$this->ref_cod_aluno=$_GET["ref_cod_aluno"];

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 578, $this->pessoa_logada, 7,  "educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );

		if( is_numeric( $this->ref_cod_aluno ) && is_numeric( $this->cod_distribuicao_uniforme ) )
		{
			$obj = new clsPmieducarDistribuicaoUniforme( $this->cod_distribuicao_uniforme );
			$registro  = $obj->detalhe();
			if( $registro )
			{
				foreach( $registro AS $campo => $val )  // passa todos os valores obtidos no registro para atributos do objeto
					$this->$campo = $val;

				$this->kit_completo = dbBool($this->kit_completo);

				if( $obj_permissoes->permissao_excluir( 578, $this->pessoa_logada, 7 ) )
				{
					$this->fexcluir = true;
				}
				$retorno = "Editar";
			}
		}
		$this->url_cancelar = ($retorno == "Editar") ? "educar_distribuicao_uniforme_det.php?ref_cod_aluno={$registro["ref_cod_aluno"]}&cod_distribuicao_uniforme={$registro["cod_distribuicao_uniforme"]}" : "educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}";
		$this->nome_url_cancelar = "Cancelar";

    $nomeMenu = $retorno == "Editar" ? $retorno : "Cadastrar";
    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_index.php"                  => "i-Educar - Escola",
         ""        => "{$nomeMenu} distribui&ccedil;&atilde;o de uniforme"             
    ));
    $this->enviaLocalizacao($localizacao->montar());

		return $retorno;
	}

	function Gerar()
	{
		if( $_POST )
			foreach( $_POST AS $campo => $val )
				$this->$campo = ( !$this->$campo ) ?  $val : $this->$campo ;

		// primary keys
		$this->campoOculto( "ref_cod_aluno", $this->ref_cod_aluno );
		$this->campoOculto( "cod_distribuicao_uniforme", $this->cod_distribuicao_uniforme );

		$this->campoNumero( "ano", "Ano", $this->ano, 4, 4, true );
		$this->inputsHelper()->checkbox('kit_completo', array( 'label' => "Kit completo", 'value' => $this->kit_completo));		
		$this->campoNumero( "agasalho_qtd", "Quantidade de agasalhos (jaqueta e cal�a)", $this->agasalho_qtd, 2, 2, false );
		$this->campoNumero( "camiseta_curta_qtd", "Quantidade de camisetas (manga curta)", $this->camiseta_curta_qtd, 2, 2, false);
		$this->campoNumero( "camiseta_longa_qtd", "Quantidade de camisetas (manga longa)", $this->camiseta_longa_qtd, 2, 2, false);
		$this->campoNumero( "meias_qtd", "Quantidade de meias", $this->meias_qtd, 2, 2, false);
		$this->campoNumero( "bermudas_tectels_qtd", "Bermudas tectels (masculino)", $this->bermudas_tectels_qtd, 2, 2, false);
		$this->campoNumero( "bermudas_coton_qtd", "Bermudas coton (feminino)", $this->bermudas_coton_qtd, 2, 2, false);
		$this->campoNumero( "tenis_qtd", "T�nis", $this->tenis_qtd, 2, 2, false);

	}

	function Novo()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 578, $this->pessoa_logada, 7,  "educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );
		
		$obj_tmp = $obj = new clsPmieducarDistribuicaoUniforme();
		$lista_tmp = $obj_tmp->lista($this->ref_cod_aluno, $this->ano);

		if($lista_tmp){
			$this->mensagem = "J� existe uma distribui��o cadastrada para este ano, por favor, verifique.<br>";
			return false;
		}

		$obj = new clsPmieducarDistribuicaoUniforme( null, $this->ref_cod_aluno, $this->ano, !is_null($this->kit_completo), $this->agasalho_qtd, $this->camiseta_curta_qtd, $this->camiseta_longa_qtd, $this->meias_qtd, $this->bermudas_tectels_qtd, $this->bermudas_coton_qtd, $this->tenis_qtd);
		$cadastrou = $obj->cadastra();
		if( $cadastrou )
		{
		
				$this->mensagem .= "Cadastro efetuado com sucesso.<br>";
				header( "Location: educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );
				die();
				return true;
		}

		$this->mensagem = "Cadastro n&atilde;o realizado.<br>";
		return false;
	}

	function Editar()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 578, $this->pessoa_logada, 7,  "educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );
		
		$obj_tmp = $obj = new clsPmieducarDistribuicaoUniforme();
		$lista_tmp = $obj_tmp->lista($this->ref_cod_aluno, $this->ano);

		if($lista_tmp){
			foreach ($lista_tmp as $reg) {
				if ($reg['cod_distribuicao_uniforme'] != $this->cod_distribuicao_uniforme){
					$this->mensagem = "J� existe uma distribui��o cadastrada para este ano, por favor, verifique.<br>";
					return false;
				}
			}
		}

		$obj = new clsPmieducarDistribuicaoUniforme( $this->cod_distribuicao_uniforme, $this->ref_cod_aluno, $this->ano, !is_null($this->kit_completo), $this->agasalho_qtd, $this->camiseta_curta_qtd, $this->camiseta_longa_qtd, $this->meias_qtd, $this->bermudas_tectels_qtd, $this->bermudas_coton_qtd, $this->tenis_qtd);
		$editou = $obj->edita();
		if( $editou )
		{
		
				$this->mensagem .= "Ed&ccedil;&atilde;o efetuada com sucesso.<br>";
				header( "Location: educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );
				die();
				return true;
		}

		$this->mensagem = "Ed&ccedil;&atilde;o n&atilde;o realizada.<br>";
		return false;
	}

	function Excluir()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_excluir( 578, $this->pessoa_logada, 7,  "educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );


		$obj = new clsPmieducarDistribuicaoUniforme( $this->cod_distribuicao_uniforme);
		$excluiu = $obj->excluir();
		if( $excluiu )
		{
			$this->mensagem .= "Exclus&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}" );
			die();
			return true;			
		}

		$this->mensagem = "Exclus&atilde;o n&atilde;o realizada.<br>";
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

<script type="text/javascript">
	function bloqueiaCamposQuantidade(){
		$j('#agasalho_qtd').val('').attr('disabled', 'disabled');
		$j('#camiseta_curta_qtd').val('').attr('disabled', 'disabled');
		$j('#camiseta_longa_qtd').val('').attr('disabled', 'disabled');
		$j('#meias_qtd').val('').attr('disabled', 'disabled');
		$j('#bermudas_tectels_qtd').val('').attr('disabled', 'disabled');
		$j('#bermudas_coton_qtd').val('').attr('disabled', 'disabled');		
		$j('#tenis_qtd').val('').attr('disabled', 'disabled');
		return true;
	}

	function liberaCamposQuantidade(){
		$j('#agasalho_qtd').removeAttr('disabled');
		$j('#camiseta_curta_qtd').removeAttr('disabled');
		$j('#camiseta_longa_qtd').removeAttr('disabled');
		$j('#meias_qtd').removeAttr('disabled');
		$j('#bermudas_tectels_qtd').removeAttr('disabled');
		$j('#bermudas_coton_qtd').removeAttr('disabled');		
		$j('#tenis_qtd').removeAttr('disabled');
	}

	$j(document).ready(function(){
		if($j('#kit_completo').is(':checked'))
			bloqueiaCamposQuantidade();

		console.log('vsf');

		$j('#kit_completo').on('change', function(){
			if($j('#kit_completo').is(':checked'))
				bloqueiaCamposQuantidade();
			else
				liberaCamposQuantidade();
		});
	})
</script>
