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
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Rela&ccedil;&atilde;o de alunos/falta bimestres" );
		$this->processoAp = "811";
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
	var $ref_cod_serie;
	var $ref_cod_turma;
	var $ref_cod_curso;
	var $ref_cod_modulo;

	var $ano;
	
	var $is_padrao;
	var $semestre;

	var $cursos = array();

	var $get_link;


	function renderHTML()
	{

		if($_POST){
			foreach ($_POST as $key => $value) {
				$this->$key = $value;

			}
		}
		if($this->ref_ref_cod_serie)
			$this->ref_cod_serie = $this->ref_ref_cod_serie;

		$this->ref_cod_modulo = explode("-",$this->ref_cod_modulo);
		$this->ref_cod_modulo = array_pop($this->ref_cod_modulo);

		$fonte = 'arial';
		$corTexto = '#000000';

		if(empty($this->ref_cod_turma))
		{
	     	echo '<script>
	     			alert("Erro ao gerar relat�rio!\nNenhuma turma selecionada!");
	     			window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));
	     		</script>';
	     	return true;
		}

		if($this->ref_cod_escola){

			$obj_escola = new clsPmieducarEscola($this->ref_cod_escola);
			$det_escola = $obj_escola->detalhe();
			$this->nm_escola = $det_escola['nome'];

			$obj_instituicao = new clsPmieducarInstituicao($det_escola['ref_cod_instituicao']);
			$det_instituicao = $obj_instituicao->detalhe();
			$this->nm_instituicao = $det_instituicao['nm_instituicao'];

		}

	     $obj_calendario = new clsPmieducarEscolaAnoLetivo();
	     $lista_calendario = $obj_calendario->lista($this->ref_cod_escola,$this->ano,null,null,null,null,null,null,null,1,null);

	     $obj_turma = new clsPmieducarTurma($this->ref_cod_turma);
	     $det_turma = $obj_turma->detalhe();
	     $this->nm_turma = $det_turma['nm_turma'];

	     $obj_serie = new clsPmieducarSerie($this->ref_cod_serie);
	     $det_serie = $obj_serie->detalhe();
	     $this->nm_serie = $det_serie['nm_serie'];

		 $obj_pessoa = new clsPessoa_($det_turma["ref_cod_regente"]);
		 $det = $obj_pessoa->detalhe();
		 $this->nm_professor = $det["nome"];

	     if(!$lista_calendario)
	     {
	     	echo '<script>
	     			alert("Escola n�o possui calend�rio definido para este ano");
	     			window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));
	     		</script>';
	     	return true;
	     }

		$obj = new clsPmieducarSerie();
		$obj->setOrderby('cod_serie,etapa_curso');
		$lista_serie_curso = $obj->lista(null,null,null,$this->ref_cod_curso,null,null,null,null,null,null,null,null,1,$this->ref_cod_instituicao);

		$obj_curso = new clsPmieducarCurso($this->ref_cod_curso);
		$det_curso = $obj_curso->detalhe();
		$this->nm_curso = $det_curso['nm_curso'];

		$obj_tipo_avaliacao = new clsPmieducarTipoAvaliacao($det_curso['ref_cod_tipo_avaliacao']);
		$det_tipo_avaliacao = $obj_tipo_avaliacao->detalhe();
		$conceitual = $det_tipo_avaliacao['conceitual'];

		if ($this->is_padrao || $this->semestre == 2007) {
			$this->semestre = null;
		}
		
		$obj_matricula_turma = new clsPmieducarMatriculaTurma();
		$obj_matricula_turma->setOrderby('nome');
//		$lst_matricula_turma = $obj_matricula_turma->lista($this->ref_cod_matricula, $this->ref_cod_turma, null, null, null, null, null, null, 1, $this->ref_cod_serie, $this->ref_cod_curso, $this->ref_cod_escola,$this->ref_cod_instituicao,null,null,array(1,2,3),null,null,$this->ano,null,null,null,null,true);
		$lst_matricula_turma = $obj_matricula_turma->lista($this->ref_cod_matricula, $this->ref_cod_turma, null, null, null, null, null, null, 1, $this->ref_cod_serie, $this->ref_cod_curso, $this->ref_cod_escola,$this->ref_cod_instituicao,null,null,array(1,2,3),null,null,$this->ano,null,null,null,null,true, null, null, true, null, $this->semestre);
		//$obj_disciplinas = new clsPmieducarDisciplinaSerie();
		$obj_disciplinas = new clsPmieducarEscolaSerieDisciplina();
		$lst_disciplinas = $obj_disciplinas->lista($this->ref_cod_serie,$this->ref_cod_escola,null,1);

		if($lst_matricula_turma)
		{

			$relatorio = new relatorios("Espelho de Faltas Bimestral {$this->ref_cod_modulo}� Bimestre Ano {$this->ano}", 210, false, "Espelho de Faltas Bimestral", "A4", "{$this->nm_instituicao}\n{$this->nm_escola}\n{$this->nm_curso}\n{$this->nm_serie} -  Turma: $this->nm_turma           ".date("d/m/Y"));
			$relatorio->setMargem(20,20,50,50);
			$relatorio->exibe_produzido_por = false;


			//$relatorio->novalinha( array( "C�d. Aluno", "Nome do Aluno", "1M�",  "M.Parcial", "Exame", "M.Final", "Faltas"),0,16,true,"arial",array( 75, 160, 120, 55, 50, 50),"#515151","#d3d3d3","#FFFFFF",false,true);
			//$relatorio->novalinha( array( "C�d. Aluno", "Nome do Aluno", "1M�", "2M�",  "M.Parcial", "Exame", "M.Final", "Faltas"),0,16,true,"arial",array( 75, 160, 60, 60, 55, 50, 50),"#515151","#d3d3d3","#FFFFFF",false,true);
			//$relatorio->novalinha( array( "C�d. Aluno", "Nome do Aluno", "1M�", "2M�", "3M�",  "M.Parcial", "Exame", "M.Final", "Faltas"),0,16,true,"arial",array( 75, 160, 40, 40, 40, 55, 50, 50),"#515151","#d3d3d3","#FFFFFF",false,true);

			$db = new clsBanco();

			if(!$det_curso['falta_ch_globalizada'])
			{
				foreach ($lst_disciplinas as $disciplina)
				{
					$obj_disciplina = new clsPmieducarDisciplina($disciplina['ref_cod_disciplina']);
					$det_disciplina = $obj_disciplina->detalhe();

					$array_disc[$det_disciplina['cod_disciplina']] = ($det_disciplina['abreviatura']);
					$array_cab[] = str2upper($det_disciplina['abreviatura']);
				}
			}
			else
			{
				$array_disc[] = "FALTAS";
				$array_cab[] = "FALTAS";

			}

			//if($conceitual)
			{
				asort($array_disc);
				sort($array_cab);
				$array_cab = array_merge(array( "C�d.", "Nome do Aluno"  ),$array_cab);
			}


			$divisoes = array( 40, 165 );
			$divisoes_texto = array( 40, 165 );

			if(!$conceitual)
			{
				$tamanho_divisao = 35 + ( 10 - count($array_disc) ) * 5;
				for($ct=0;$ct<20;$ct++)
				{
					$divisoes[] = $tamanho_divisao ;
					$divisoes_texto[] = $tamanho_divisao;
				}
			}
			else
			{
				$divisoes = null ;
				$divisoes_texto = null;
			}



			$relatorio->novalinha( $array_cab ,0,16,true,"arial",$divisoes,"#515151","#d3d3d3","#ffffff",false,true);

			/*if($conceitual)
			{
				$tam_fonte = 8;
				$tam_linha = 11;
			}
			else
			{*/
				$tam_fonte = null;
				$tam_linha = 16;
			//}
			foreach ($lst_matricula_turma as $matricula)
			{

				if(!$det_curso['falta_ch_globalizada'])
				{
					$consulta = " SELECT ref_cod_disciplina
							       		 ,faltas
								    FROM pmieducar.falta_aluno
								   WHERE ref_cod_matricula  = {$matricula['ref_cod_matricula']}
								     AND ref_cod_escola     = {$this->ref_cod_escola}
								     AND ref_cod_serie      = {$this->ref_cod_serie}
								     AND modulo 		   = {$this->ref_cod_modulo}
								     AND falta_aluno.ativo   = 1
								   ORDER BY modulo ASC ";

					$db->Consulta($consulta);
					$notas = null;
					while ($db->ProximoRegistro())
					{
						$registro = $db->Tupla();

						$notas[$registro['ref_cod_disciplina']] = $registro['faltas'];

					}
				}
				else
				{
					$obj_falta = new clsPmieducarFaltas();
					$obj_falta->setOrderby("sequencial asc");
					$det_falta = $obj_falta->lista($matricula['ref_cod_matricula'],null,null,null,null,null);
					$notas = null;
					if(is_array($det_falta))
					{
						$total_faltas = 0;
						foreach ($det_falta as $key => $value)
						{
							$total_faltas += $det_falta[$key]['faltas'] = $value['falta'];
						}

						$det_falta['total'] = $total_faltas;

					}

					$notas[] = $det_falta['total'];
				}

				if( strlen( $matricula['nome'] ) > 24 )
				{
					$matricula['nome'] = explode(" ",$matricula['nome']);
					if(is_array($matricula['nome'] ))
					{
						$nome_aluno = array_shift($matricula['nome']);
					}
					if(is_array($matricula['nome'] ))
					{
						$nome_aluno .= " ".array_shift($matricula['nome']);
					}
					if(is_array($matricula['nome'] ))
					{
						$nome_aluno .= " ".array_pop($matricula['nome']);
					}
					$matricula['nome'] = $nome_aluno;
				}

				$array_val = array();
				$array_val[] = $matricula['ref_cod_aluno'];
				$array_val[] = $matricula['nome'];

				foreach ($array_disc as $cod_disc => $disc)
				{
					//if(!$conceitual)
					//	$array_val[] = $notas[$cod_disc] ? number_format( $notas[$cod_disc] ,2,'.','') : $notas[$cod_disc];
					//else
						$array_val[] = $notas[$cod_disc];

				}

				$relatorio->novalinha($array_val,0,$tam_linha,false,"arial",$divisoes_texto,"#515151","#d3d3d3","#FFFFFF",false,true,null,$tam_fonte);

			}

			$this->get_link = $relatorio->fechaPdf();
		}


		echo "<script>window.onload=function(){parent.EscondeDiv('LoadImprimir');window.location='download.php?filename=".$this->get_link."'}</script>";

		echo "<html><center>Se o download n�o iniciar automaticamente <br /><a target='blank' href='" . $this->get_link  . "' style='font-size: 16px; color: #000000; text-decoration: underline;'>clique aqui!</a><br><br>
			<span style='font-size: 10px;'>Para visualizar os arquivos PDF, � necess�rio instalar o Adobe Acrobat Reader.<br>

			Clique na Imagem para Baixar o instalador<br><br>
			<a href=\"http://www.adobe.com.br/products/acrobat/readstep2.html\" target=\"new\"><br><img src=\"imagens/acrobat.gif\" width=\"88\" height=\"31\" border=\"0\"></a>
			</span>
			</center>";
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
