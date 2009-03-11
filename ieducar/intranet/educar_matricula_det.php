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
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Matr&iacute;cula" );
		$this->processoAp = "578";
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

	var $ref_cod_matricula;
	var $ref_cod_reserva_vaga;
	var $ref_ref_cod_escola;
	var $ref_ref_cod_serie;
	var $ref_usuario_exc;
	var $ref_usuario_cad;
	var $ref_cod_aluno;
	var $aprovado;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;

	function Gerar()
	{
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->titulo = "Matr&iacute;cula - Detalhe";
		$this->addBanner( "imagens/nvp_top_intranet.jpg", "imagens/nvp_vert_intranet.jpg", "Intranet" );

		$this->ref_cod_matricula=$_GET["cod_matricula"];

		$obj_matricula = new clsPmieducarMatricula();
		$lst_matricula = $obj_matricula->lista( $this->ref_cod_matricula );
		if($lst_matricula)
			$registro = array_shift($lst_matricula);

		if( !$registro )
		{
			header( "location: educar_matricula_lst.php?ref_cod_aluno={$registro['ref_cod_aluno']}" );
			die();
		}

		if( class_exists( "clsPmieducarCurso" ) )
		{
			$obj_ref_cod_curso = new clsPmieducarCurso( $registro["ref_cod_curso"] );
			$det_ref_cod_curso = $obj_ref_cod_curso->detalhe();
			$registro["ref_cod_curso"] = $det_ref_cod_curso["nm_curso"];
		}
		else
		{
			$registro["ref_cod_curso"] = "Erro na geracao";
			echo "<!--\nErro\nClasse nao existente: clsPmieducarCurso\n-->";
		}
		if( class_exists( "clsPmieducarSerie" ) )
		{
			$obj_serie = new clsPmieducarSerie( $registro["ref_ref_cod_serie"] );
			$det_serie = $obj_serie->detalhe();
			$registro["ref_ref_cod_serie"] = $det_serie["nm_serie"];
		}
		else
		{
			$registro["ref_ref_cod_serie"] = "Erro na geracao";
			echo "<!--\nErro\nClasse nao existente: clsPmieducarSerie\n-->";
		}
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
		if( class_exists( "clsPmieducarEscola" ) )
		{
			$obj_ref_cod_escola = new clsPmieducarEscola( $registro["ref_ref_cod_escola"] );
			$det_ref_cod_escola = $obj_ref_cod_escola->detalhe();
			$registro["ref_ref_cod_escola"] = $det_ref_cod_escola["nome"];
		}
		else
		{
			$registro["ref_ref_cod_escola"] = "Erro na gera&ccedil;&atilde;o";
			echo "<!--\nErro\nClasse n&atilde;o existente: clsPmieducarEscola\n-->";
		}
		if( class_exists( "clsPmieducarAluno" ) )
		{
			$obj_aluno = new clsPmieducarAluno();
			$lst_aluno = $obj_aluno->lista( $registro["ref_cod_aluno"],null,null,null,null,null,null,null,null,null,1 );
			if ( is_array($lst_aluno) )
			{
				$det_aluno = array_shift($lst_aluno);
				$nm_aluno = $det_aluno["nome_aluno"];
			}
		}
		else
		{
			$nm_aluno = "Erro na geracao";
			echo "<!--\nErro\nClasse nao existente: clsPmieducarAluno\n-->";
		}


		$obj_mat_turma = new clsPmieducarMatriculaTurma();
		$det_mat_turma = $obj_mat_turma->lista($this->ref_cod_matricula,null,null,null,null,null,null,null,1);

		if($det_mat_turma){
			$det_mat_turma = array_shift($det_mat_turma);
			$obj_turma = new clsPmieducarTurma($det_mat_turma['ref_cod_turma']);
			$det_turma = $obj_turma->detalhe();
			$nm_turma = $det_turma['nm_turma'];
		}

		if( $registro["cod_matricula"] )
		{
			$this->addDetalhe( array( "N&uacute;mero Matr&iacute;cula", "{$registro["cod_matricula"]}") );
		}
		if( $nm_aluno )
		{
			$this->addDetalhe( array( "Aluno", "{$nm_aluno}") );
		}
		if( $registro["ref_cod_instituicao"] )
		{
			$this->addDetalhe( array( "Institui&ccedil;&atilde;o", "{$registro["ref_cod_instituicao"]}") );
		}
		if( $registro["ref_ref_cod_escola"] )
		{
			$this->addDetalhe( array( "Escola", "{$registro["ref_ref_cod_escola"]}") );
		}
		if( $registro["ref_cod_curso"] )
		{
			$this->addDetalhe( array( "Curso", "{$registro["ref_cod_curso"]}") );
		}
		if( $registro["ref_ref_cod_serie"] )
		{
			$this->addDetalhe( array( "S&eacute;rie", "{$registro["ref_ref_cod_serie"]}") );
		}
		if($nm_turma)
		{
			$this->addDetalhe( array( "Turma", "{$nm_turma}") );
		}
		if( $registro["ref_cod_reserva_vaga"] )
		{
			$this->addDetalhe( array( "N&uacute;mero Reserva Vaga", "{$registro["ref_cod_reserva_vaga"]}") );
		}
		if( $registro["aprovado"] )
		{
			if ($registro["aprovado"] == 1)
			{
				$aprovado = "Aprovado";
			}
			elseif ($registro["aprovado"] == 2)
			{
				$aprovado = "Reprovado";
			}
			elseif ($registro["aprovado"] == 3)
			{
				$aprovado = "Em Andamento";
			}
			elseif ($registro["aprovado"] == 4)
			{
				$aprovado = "Transferido";
			}
			elseif ($registro["aprovado"] == 5)
			{
				$aprovado = "Reclassificado";
			}
			elseif ($registro["aprovado"] == 6)
			{
				$aprovado = "Abandono";
			}
			elseif ($registro["aprovado"] == 7)
			{
				$aprovado = "Em Exame";
			}
			$this->addDetalhe( array( "Situa&ccedil;&atilde;o", "{$aprovado}") );
		}

		$this->addDetalhe( array( "Formando", $registro["formando"] == 0 ? "N&atilde;o" : "Sim" ));

		$obj_permissoes = new clsPermissoes();
		if( $obj_permissoes->permissao_cadastra( 578, $this->pessoa_logada, 7 ) )
		{
//			$this->array_botao = array("Nova Matr&iacute;cula");
//			$this->array_botao_url = array("educar_matricula_cad.php?ref_cod_aluno={$registro['ref_cod_aluno']}");

			/**
			 * verifica se existe transferencia
			 */
			if ($registro["aprovado"] != 4 && $registro["aprovado"] != 6)
			{
				$obj_transferencia = new clsPmieducarTransferenciaSolicitacao();
				$lst_transferencia = $obj_transferencia->lista( null,null,null,null,null,$registro['cod_matricula'],null,null,null,null,null,1,null,null,$registro['ref_cod_aluno'],false);
				// verifica se existe uma solicitacao de transferencia INTERNA
				if(is_array($lst_transferencia))
					$det_transferencia = array_shift($lst_transferencia);
				$data_transferencia = $det_transferencia["data_transferencia"];
			}
						
			if ($registro["aprovado"] == 3 && (!is_array($lst_transferencia) && !isset($data_transferencia) ))
			{
				$this->array_botao[] = "Cancelar Matr&iacute;cula";
				$this->array_botao_url_script[] = "if(confirm(\"Deseja realmente cancelar esta matr�cula?\"))go(\"educar_matricula_cad.php?cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}\")";

				$this->array_botao[] = "Ocorr&ecirc;ncias Disciplinares";
				$this->array_botao_url_script[] = "go(\"educar_matricula_ocorrencia_disciplinar_lst.php?ref_cod_matricula={$registro['cod_matricula']}\")";

				if ($registro["ref_ref_cod_serie"])
				{
					$this->array_botao[] = "Dispensa de Disciplinas";
					$this->array_botao_url_script[] = "go(\"educar_dispensa_disciplina_lst.php?ref_cod_matricula={$registro['cod_matricula']}\")";
				}

				$this->array_botao[] = "Enturmar";
				$this->array_botao_url_script[] = "go(\"educar_matricula_turma_lst.php?ref_cod_matricula={$registro['cod_matricula']}\")";

				$this->array_botao[] = "Abandono";
				$this->array_botao_url_script[] = "if(confirm(\"Deseja confirmar o abandono desta matr�cula?\"))go(\"educar_matricula_abandono_cad.php?ref_cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}\");";

				if ($registro["ref_ref_cod_serie"])
				{
					$this->array_botao[] = "Reclassificar";
					$this->array_botao_url_script[] = "go(\"educar_matricula_reclassificar_cad.php?ref_cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}\")";
				}

			}

			if ($registro["aprovado"] != 4 && $registro["aprovado"] != 6)
			{
				if ( is_array($lst_transferencia) && !isset($data_transferencia))
				{
					// verifica se existe uma solicitacao de transferencia INTERNA
//					if ( !isset($data_transferencia) )
///					{
						$this->array_botao[] = "Cancelar Solicita&ccedil;&atilde;o Transfer&ecirc;ncia";
						$this->array_botao_url_script[] = "go(\"educar_transferencia_solicitacao_cad.php?ref_cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}&cancela=true\")";

//					}
				}
				else
				{
					if ($registro["ref_ref_cod_serie"])
					{
						$this->array_botao[] = "Solicitar Transfer&ecirc;ncia";
						$this->array_botao_url_script[] = "go(\"educar_transferencia_solicitacao_cad.php?ref_cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}\")";
					}
				}

				if($registro["aprovado"] ==3 && (!is_array($lst_transferencia) && !isset($data_transferencia) ))
				{
					if($registro["formando"] == 0 )
					{
						$this->array_botao[] = "Formando";
						$this->array_botao_url_script[] = "if(confirm(\"Deseja marcar a matr�cula como formando?\"))go(\"educar_matricula_formando_cad.php?ref_cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}&formando=1\")";
					}
					else
					{
						$this->array_botao[] = "Desmarcar como Formando";
						$this->array_botao_url_script[] = "if(confirm(\"Deseja desmarcar a matr�cula como formando?\"))go(\"educar_matricula_formando_cad.php?ref_cod_matricula={$registro['cod_matricula']}&ref_cod_aluno={$registro['ref_cod_aluno']}&formando=0\")";
					}
				}
			}
			if($registro['aprovado'] == 4 || $det_transferencia)
			{
				$this->array_botao[] = "Imprimir Atestado Freq��ncia";
				//$this->array_botao_url_script[] = "go(\"educar_relatorio_atestado_frequencia.php?cod_matricula={$registro['cod_matricula']}\")";
				$this->array_botao_url_script[] = "showExpansivelImprimir(400, 200,  \"educar_relatorio_atestado_frequencia.php?cod_matricula={$registro['cod_matricula']}\",[], \"Relat�rio Atestado de Freq��ncia\")";

			}

		}

		$this->url_cancelar = "educar_matricula_lst.php?ref_cod_aluno={$registro['ref_cod_aluno']}";
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