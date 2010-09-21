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
require_once( "include/clsMenuFuncionario.inc.php" );
require_once ("include/clsPDF.inc.php");

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Levantamento Turma Per�odo" );
		$this->processoAp = "933";
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


	var $ref_cod_instituicao;
	var $ref_cod_escola;
	var $ref_cod_serie;
	var $ref_cod_turma;

	var $ano;

	var $ref_cod_curso;
	var $escola_sem_avaliacao;

	var $meses_do_ano = array(
							 "1" => "JANEIRO"
							,"2" => "FEVEREIRO"
							,"3" => "MAR�O"
							,"4" => "ABRIL"
							,"5" => "MAIO"
							,"6" => "JUNHO"
							,"7" => "JULHO"
							,"8" => "AGOSTO"
							,"9" => "SETEMBRO"
							,"10" => "OUTUBRO"
							,"11" => "NOVEMBRO"
							,"12" => "DEZEMBRO"
						);

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		return $retorno;

	}

	function Gerar()
	{

		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();


		$this->campoNumero( "ano", "Ano", date("Y"), 4, 4, true );
//		$this->campoLista( "mes", "M&ecirc;s",$this->meses_do_ano, $this->mes,"",false );

		$get_escola = true;
		$instituicao_obrigatorio = true;
//		$escola_obrigatorio = true;
		$exibe_nm_escola = true;
		$get_curso = true;
		$curso_obrigatorio = true;
//		$get_escola_curso_serie = true;
//		$get_turma = true;
//		$get_semestre = true;

		include("include/pmieducar/educar_campo_lista.php");

//		$this->campoCheck("escola_sem_avaliacao", "CEI", $this->escola_sem_avaliacao ? true : false);
		$this->campoRadio("escola_sem_avaliacao", "Somente", array("1" => "CEI", "2" => "Escolas", "3" => "CEI e Escolas"), $this->escola_sem_avaliacao);

		$this->url_cancelar = "educar_index.php";
		$this->nome_url_cancelar = "Cancelar";

		$this->acao_enviar = 'acao2()';
		$this->acao_executa_submit = false;
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

function acao2()
{
	if(!acao())
		return false;

	showExpansivelImprimir(400, 200,'',[], "Di�rio de Classe");

	document.formcadastro.target = 'miolo_'+(DOM_divs.length-1);

	document.formcadastro.submit();
}

document.formcadastro.action = 'educar_relatorio_levantamento_turma_periodo_aluno_proc.php';

document.getElementById('ref_cod_escola').onchange = function()
{
	if (this.value == '')
		getCurso();
	else
		getEscolaCurso();
}

</script>