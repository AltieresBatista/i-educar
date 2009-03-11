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
require_once ("include/relatorio.inc.php");

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Quadro Alunos Idade x Sexo" );
		$this->processoAp = "774";
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
	var $ref_ref_cod_escola;
	var $ref_cod_curso;
	var $ref_ref_cod_serie;
	var $ref_cod_turma;

	var $ano;
	var $link;

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
		$get_escola = true;
		$get_curso = true;
		$get_escola_curso_serie = true;
		$instituicao_obrigatorio = true;
		$escola_obrigatorio = true;
		$curso_obrigatorio = true;
		
//		$get_semestre = true;

		$this->campoNumero("ano", "Ano", date("Y"), 4, 4, true);
		include("include/pmieducar/educar_campo_lista.php");

		if ( $this->ref_cod_escola )
		{
			$this->ref_ref_cod_escola = $this->ref_cod_escola;
		}
		$this->acao_enviar = false;

		$this->array_botao = array( "Gerar Relat&oacute;rio" );
//		$this->array_botao_url_script = array( "showExpansivelImprimir(400, 200,  'educar_relatorio_quadro_idade_sexo_serie_proc.php',['ref_cod_escola', 'ref_cod_curso', 'ref_ref_cod_serie'], 'Relat�rio i-Educar' )" );
		$this->array_botao_url_script = array( "geraRelatorio()" );
	}

	function Novo()
	{
		return true;
	}
}
$pagina = new clsIndexBase();
$miolo = new indice();
$pagina->addForm( $miolo );
$pagina->MakeAll();
?>
<script>

document.getElementById('ref_cod_escola').onchange = function()
{
	getEscolaCurso();
}

document.getElementById('ref_cod_curso').onchange = function()
{
	getEscolaCursoSerie();
//	verifica_curso();
}

function geraRelatorio() {	
	if ($F('ano') != '') {
		/*if ($F('is_padrao') == 0)
		{
			if ($F('sem1'))
				showExpansivelImprimir(400, 200,  'educar_relatorio_quadro_idade_sexo_serie_proc.php',['ref_cod_escola', 'ref_cod_curso', 'ref_ref_cod_serie', 'ano', 'sem1', 'is_padrao'], 'Relat�rio i-Educar' );
			else if ($F('sem2'))
				showExpansivelImprimir(400, 200,  'educar_relatorio_quadro_idade_sexo_serie_proc.php',['ref_cod_escola', 'ref_cod_curso', 'ref_ref_cod_serie', 'ano', 'sem2', 'is_padrao'], 'Relat�rio i-Educar' );
			else
			{
				alert("O campo 'Semestre' deve ser preenchido corretamente!");
				document.getElementById('sem1').focus();
			}
			return;
		}*/
		showExpansivelImprimir(400, 200,  'educar_relatorio_quadro_idade_sexo_serie_proc.php',['ref_cod_escola', 'ref_cod_curso', 'ref_ref_cod_serie', 'ano'], 'Relat�rio i-Educar' )
	} else {
		alert('Preencha o campo \'Ano\' corretamente')
	}
}

</script>