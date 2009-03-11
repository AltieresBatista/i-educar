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
require_once ("include/clsPDF.inc.php");

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Alunos Matriculados - Sint&eacute;tico" );
		$this->processoAp = "707";
		$this->renderMenu = false;
		$this->renderMenuSuspenso = false;
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
	var $ref_cod_curso;
	var $ref_ref_cod_serie;
	var $ref_cod_serie;

	var $ano;

	var $nm_escola;
	var $nm_instituicao;
	var $nm_curso;
	var $nm_municipio;
	var $nm_localidade;

	var $pdf;

	var $page_y = 139;

	var $get_link;

	var $primeiro_dia_semana;
	var $ultimo_dia_semana;

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

	function renderHTML()
	{

		if($_POST){
			foreach ($_POST as $key => $value) {
				$this->$key = $value;

			}
		}
		if($this->ref_ref_cod_serie)
			$this->ref_cod_serie = $this->ref_ref_cod_serie;

		$fonte = 'arial';
		$corTexto = '#000000';

		$this->pdf = new clsPDF("Alunos Matriculados - {$this->ano}", "Alunos Matriculados - Sint�tico", "A4", "", false, false);

		$disciplinas = array('DEMONSTRA COMPREENS�O DO PROCESSO DE LEITURA E DA SUA FUN��O SOCIAL.',
						 	'DEMONSTRA COMPREENS�O DO PROCESSO DE ESCRITA E DA SUA FUN��O SOCIAL.',
							'EXPRESSA-SE, ESTABELECENDO DI�LOGO E INTERA��O COM O GRUPO',
							'CONCEITUA E IDENTIFICA OS NUMERAIS NOS DIFERENTES CONTEXTOS EM QUE SE ENCONTRAM, ESTABELECENDO RELA��O ENTRE NUMERAIS E QUANTIDADES.',
							'RESOLVE OPERA��ES E SITUA��ES-PROBLEMA ENVOLVENDO O CONCEITO DE ADI��O, POR MEIO DE  ESTRAT�GIAS PR�PRIAS OU POR T�CNICAS CONVENCIONAIS.',
							'RESOLVE OPERA��ES E SITUA��ES-PROBLEMA ENVOLVENDO O CONCEITO DE SUBTRA��O, POR MEIO DE  ESTRAT�GIAS PR�PRIAS OU POR T�CNICAS CONVENCIONAIS.',
							'REPRESENTA A POSI��O DE OBJETOS OU PESSOAS E IDENTIFICA AS FORMAS E PROPRIEDADES DAS FIGURAS GEOM�TRICAS, UTILIZANDO VOCABUL�RIO PERTINENTE NAS DIVERSAS SITUA��ES A QUE EST� EXPOSTA.',
							'DEMONSTRA COMPREENS�O DO CONCEITO DE MEDIDAS POR MEIO DE UNIDADES CONVENCIONAIS E N�O CONVENCIONAIS.',
							'L�, INTERPRETA E ORGANIZA REGISTROS DE INFORMA��ES EM GR�FICOS,  TABELAS E CALEND�RIOS.',
							'RECONHECE MODOS DE SER, VIVER E TRABALHAR DE ALGUNS GRUPOS SOCIAIS, PERCEBENDO A SI PR�PRIO COMO SUJEITO DO MEIO EM QUE VIVE.',
							'ESTABELECE RELA��O ENTRE A NATUREZA E OS SERES VIVOS, COMPREENDENDO A IMPORTANCIA DA PRESERVA��O DO ECO-SISTEMA.',
							'INTERPRETA INFORMA��ES, ESTABELECENDO RELA��ES COM SUAS EXPERI�NCIAS COTIDIANAS E DEMONSTRANDO MUDAN�AS DE ATITUDES A PARTIR DO SEU APRENDIZADO.',
							'PARTICIPA DA REALIZA��O DE TAREFAS QUE ENVOLVAM A�OES DE COOPERA��O, SOLIDARIEDADE E AJUDA NA RELA��O COM O OUTRO, PERCEBENDO SEUS  DIREITOS E DEVERES.',
							'DEMONSTRA  COORDENA��O MOTORA, EQUIL�BRIO, LATERALIDADE, LOCALIZA��O NO TEMPO E NO ESPA�O, VELOCIDADE, RESIST�NCIA E FLEXIBILIDADE NA REALIZA��O DE SUAS ATIVIDADES.',
							'ESTABELECE RELA��O ENTRE A SUA PRODU��O E A DO OUTRO, DEMONSTRANDO COMPREENS�O DAS DIFERENTES LINGUAGENS ART�STICAS.'
							);




			$page_open = false;

			$obj_escola_instituicao = new clsPmieducarEscola();
			$lst_escola_instituicao = $obj_escola_instituicao->lista($this->ref_cod_escola, null, null, $this->ref_cod_instituicao, null, null, null, null, null, null,1);
			$this->ref_cod_escola = $escola['cod_escola'];


			$obj_escola = new clsPmieducarEscola($this->ref_cod_escola);
			$det_escola = $obj_escola->detalhe();
			$this->nm_escola = $det_escola['nome'];

			$obj_instituicao = new clsPmieducarInstituicao($this->ref_cod_instituicao);
			$det_instituicao = $obj_instituicao->detalhe();
			$this->nm_instituicao = $det_instituicao['nm_instituicao'];



			foreach ($disciplinas as $disciplina)
			{
				if(!$page_open)
				{
					$x_quadrado = 30;
					$this->page_y = 95;
					$altura_caixa = 85;
					$this->pdf->OpenPage();
					$this->addCabecalho();
					$this->addCabecalho2();

					$page_open = true;
				}
				$altura_caixa = 15 + (int)((strlen($disciplina) / 60 ) * 7) ;
				$this->pdf->quadrado_relativo( 30, $this->page_y, 535, $altura_caixa );
				$this->pdf->linha_relativa( 440, $this->page_y, 0, $altura_caixa, '0.1');
				$this->pdf->escreve_relativo($disciplina,35,$this->page_y + 5,400,120,"arial","8","#000000","justify");

				$x_bim = 440 + 31;
				for ($i=1;$i <= 4;$i++)
				{
					if($i<=3)
						$this->pdf->linha_relativa( $x_bim, $this->page_y, 0, $altura_caixa, '0.1');
					$this->pdf->escreve_relativo("PD",$x_bim-31,$this->page_y + ($altura_caixa / 3),31,120,"arial","10","#000000","center");
					$x_bim += 31;
				}

				$this->page_y += $altura_caixa;
			}

			$this->page_y += 15;

			$this->pdf->escreve_relativo( "LEGENDA: \n
	D   = Desenvolvida
	PD = Parcialmente Desenvolvida
	ID   = Iniciando o Desenvolvimento
	ND = N�o Desenvolvida
	CNA = Compet�ncia N�o Avaliada", 36,$this->page_y, 200, 50, $fonte, 7, $corTexto, 'left' );

			$this->page_y += 75;
			$altura_obs = 60;

			$this->pdf->quadrado_relativo( 30, $this->page_y , 535, $altura_obs,0.1,"#000000","#FFFFFF" );
			$this->pdf->escreve_relativo( "OBS: ",33, $this->page_y + 3 , 545, 60, $fonte, 8, $corTexto, 'justify' );



		$this->pdf->CloseFile();
		$this->get_link = $this->pdf->GetLink();


		echo "<script>window.onload=function(){parent.EscondeDiv('LoadImprimir');window.location='download.php?filename=".$this->get_link."'}</script>";

		echo "<html><center>Se o download n�o iniciar automaticamente <br /><a target='_blank' href='" . $this->get_link  . "' style='font-size: 16px; color: #000000; text-decoration: underline;'>clique aqui!</a><br><br>
			<span style='font-size: 10px;'>Para visualizar os arquivos PDF, � necess�rio instalar o Adobe Acrobat Reader.<br>

			Clique na Imagem para Baixar o instalador<br><br>
			<a href=\"http://www.adobe.com.br/products/acrobat/readstep2.html\" target=\"new\"><br><img src=\"imagens/acrobat.gif\" width=\"88\" height=\"31\" border=\"0\"></a>
			</span>
			</center>";
	}

	function addCabecalho()
	{
		// variavel que controla a altura atual das caixas
		$altura = 30;
		$fonte = 'arial';
		$corTexto = '#000000';

		// cabecalho
		$this->pdf->quadrado_relativo( 30, $altura, 535, 85 );
		$this->pdf->InsertJpng( "gif", "imagens/brasao.gif", 50, 95, 0.30 );

		//titulo principal
		$this->pdf->escreve_relativo( "PREFEITURA COBRA TECNOLOGIA", 30, 30, 535, 80, $fonte, 18, $corTexto, 'center' );

		//dados escola
		$this->pdf->escreve_relativo( "Institui��o:$this->nm_instituicao", 120, 50, 300, 80, $fonte, 10, $corTexto, 'left' );
		$this->pdf->escreve_relativo( "Escola:{$this->nm_escola}",136, 62, 300, 80, $fonte, 10, $corTexto, 'left' );
		$this->pdf->escreve_relativo( "Curso:{$this->nm_escola}\t\t\t\t\tTurma:",136, 74, 300, 80, $fonte, 10, $corTexto, 'left' );
		$this->pdf->escreve_relativo( "Aluno:Haissam yebahi do brasil",136, 86, 300, 80, $fonte, 10, $corTexto, 'left' );

		//titulo
		$this->pdf->escreve_relativo( "B O L E T I M  E S C O L A R", 30, 98, 535, 80, $fonte, 14, $corTexto, 'center' );

		//Data
		$mes = date('n');
		//$this->pdf->escreve_relativo( "{$this->meses_do_ano[$mes]}", 45, 100, 535, 80, $fonte, 10, $corTexto, 'left' );


	}

	function addCabecalho2()
	{
		$fonte = 'arial';
		$corTexto = '#000000';
		$x_quadrado = 30;
		$altura_caixa = 20;

		$this->page_y += $altura_caixa;

		$this->pdf->quadrado_relativo( $x_quadrado, $this->page_y+10, 535, $altura_caixa );
		$this->pdf->escreve_relativo( "COMPET�NCIAS", 30, $this->page_y + 15, 405, $altura_caixa, $fonte, 9, $corTexto, 'center' );

		$this->pdf->linha_relativa( 440, $this->page_y + 10, 0, $altura_caixa, '0.1');

		$x_bim = 440 + 31;
		for ($i=1;$i <= 4;$i++)
		{
			if($i <= 3)
				$this->pdf->linha_relativa( $x_bim, $this->page_y + 10, 0, $altura_caixa, '0.1');
			$this->pdf->escreve_relativo("{$i}�BIM",$x_bim-31,$this->page_y + 15,31,120,"arial","10","#000000","center");
			$x_bim += 31;
		}

		$this->page_y += $altura_caixa + 10;

	}

	function Editar()
	{
		return false;
	}

	function Excluir()
	{
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
