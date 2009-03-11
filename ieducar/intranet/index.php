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

require_once ("include/clsAgenda.inc.php");

class clsIndex extends clsBase
{

	function Formular()
	{
		$this->SetTitulo( "Prefeitura Municipal de Recreio" );
		$this->processoAp = 0;
	}
}

class indice
{

	function addLeadingZero( $num )
	{
		if( is_numeric( $num ) )
		{
			if( $num < 10 )
			{
				return "0" . $num;
			}
			return $num;
		}
		return 0;
	}
	function RenderHTML()
	{
		@session_start();
		$id_pessoa = $_SESSION['id_pessoa'];
		@session_write_close();
		
		
		if( $id_pessoa )
		{
			$endScript = "<script>var x = new Array();\n";
			
			$height = 70;
			$width = 40;
			
			if( isset( $_GET["negado"] ) )
			{
				$temp .= "<center><h3>Acesso negado para este usu&aacute;rio.</h3><br>Caso persista nas tentativas sua conta na intranet poder&aacute; ser bloqueada por tempo indeterminado.</center>";
			}
	
			$temp .= "<table width='100%' height='400' align='center' border='0' cellspacing='4' cellpadding='0'>";
			$temp .= "<tr>	
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_dom.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"DOM\" title=\"Domingo\"></td>
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_seg.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"SEG\" title=\"Segunda Feira\"></td>
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_ter.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"TER\" title=\"Terca Feira\"></td>
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_qua.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"QUA\" title=\"Quarta Feira\"></td>
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_qui.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"QUI\" title=\"Quinta Feira\"></td>
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_st.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"SEX\" title=\"Sexta Feira\"></td>
					<td class='fundoCalendarioTopo' style=\"padding:0px;\"><img src=\"imagens/nvp_agn_sab.jpg\" width=\"43\" height=\"24\" border=\"0\" alt=\"SAB\" title=\"Sabado\"></td>
				</tr>";
		
			$mes = ($_GET['mes']) ? $_GET['mes'] : date("m");
			$ano = ($_GET['ano']) ? $_GET['ano'] : date("Y");
			$diaDaSemanaUltimo = date("w", mktime(0,0,0,$mes,date("t",mktime(0,0,0,$mes,1,$ano)),$ano));
	
			$temp_var = 0;
	
			if( class_exists( "clsProcesso" ) )
			{
				// Busca os codigos das pastas ativas
				$obj_pastas = new clsProcesso();
				$lista_pastas = $obj_pastas->lista_cod();
				// Verificas se existem pastas ativas
				if($lista_pastas)
				{
					//Buscas os encaminhamentos da pessoa atual
					$obj_encaminha = new clsEncaminha();
					$lista_minhas_pastas = $obj_encaminha->lista_cod_processos(false,false,false,false,false,$id_pessoa,false,false,false,false,false,false,false,false,false,false,$lista_pastas);
					//Verificas se existem encaminhamentos
					if($lista_minhas_pastas)
					{
						// Busca os Processos dentro das pastas da pessoa Atual
						$obj_processo = new clsTramite();
						$lista_processos_ativos = $obj_processo->lista_cod(false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,1,false,false,false,$lista_minhas_pastas);
					}
		
				}
			}
	
			// Faz loop da quantidade de dias do Mês
			$max_comp_dia = 5;
			for ($i=1;$i<= date("t", mktime(0,0,0,$mes,1,$ano));$i++)
			{
				$qtd = 0;
				
				$dataAtual = date("Y/m/d",mktime(0,0,0,$mes,$i,$ano));
				$dataAmanha = date("Y/m/d",mktime(0,0,0,$mes,$i+1,$ano));																																																																																																																																																							
				$diaDaSemana = date("w",strtotime(substr($dataAtual,0,19)));
		
	
				$compromisso_geral = "";
	
				if( class_exists( "clsEncaminha" ) )
				{
					$objEncaminha = new clsEncaminha();
					$lista_encaminha = $objEncaminha->lista_cod_encaminha(false,false,false,false,false,false,false,false,false,false,false,false,$dataAtual, $dataAmanha);
					if($lista_encaminha)
					{
						$lista_encaminha = $obj_encaminha->lista(false,false,false,false,false,$id_pessoa,false,false,false,false,false,false,$dataAtual, $dataAmanha,false,false,false,$lista_encaminha);
					}
					if($lista_encaminha)
					{
						foreach ( $lista_encaminha as $encaminha) 
						{			
							$id = "";
							if($encaminha['ref_cod_juris_processo'] && $encaminha['ref_versao_processo'])
							{
								$objProcesso = new clsProcesso($encaminha['ref_cod_juris_processo'],$encaminha['ref_versao_processo']);
								$detalheProcesso = $objProcesso->detalhe();
								if($detalheProcesso['ativo'] == 1 && !$detalheProcesso['ref_pessoa_finalizadora'] && $qtd < $max_comp_dia )
								{
									if( ! $encaminha['visualizado'] )
									{
										$temp_var++;
										$endScript .= " x[$temp_var]= $temp_var;";
										$id = "id='comp_{$temp_var}'";
									}
									$qtd++;
									$compromisso_geral .= "<a href='juris_processo_det.php?cod_processo={$encaminha['ref_cod_juris_processo']}&versao_processo={$encaminha['ref_versao_processo']}' ><span class='textoAgenda' $id>- Pasta nº {$encaminha['ref_cod_juris_processo']}</span></a><br>";
								}
							}else 
							{
								$objTramite = new clsTramite($encaminha['ref_cod_juris_tramite'],$encaminha['ref_versao_tramite']);
								$detalheTramite = $objTramite->detalhe();
								$objProcesso = new clsProcesso($detalheTramite['ref_cod_juris_processo'],$detalheTramite['ref_versao_processo']);
								$detalheProcesso = $objProcesso->detalhe();					
								if($detalheTramite['ativo'] == 1 && !$detalheProcesso['ref_pessoa_finalizadora'] && $qtd< $max_comp_dia )
								{
									if(!$encaminha['visualizado'])
									{
										$temp_var++;
										$endScript .= " x[$temp_var]= $temp_var;";
										$id = "id='comp_{$temp_var}'";
									}					
									$qtd++;
									$compromisso_geral .= "<a href='juris_tramite_det.php?cod_tramite={$encaminha['ref_cod_juris_tramite']}&versao_tramite={$encaminha['ref_versao_tramite']}' ><span class='textoAgenda' $id>- Processo nº {$encaminha['ref_cod_juris_tramite']}</span></a><br>";
								}					
							}
						}
					}
					
					if($lista_processos_ativos)
					{
						$obj_prazo = new clsJurisTramitePrazo();
						$lista_prazos = $obj_prazo->lista(false,false,false,false,false,false,false,false,$dataAtual,$dataAmanha,false,false,false,false,$lista_processos_ativos);
						if($lista_prazos)
						{
							foreach ($lista_prazos as $prazo) 
							{
					
								if(strlen($prazo['descricao']) > 10)
								{
									$descricao = 	substr($prazo['descricao'],0,10)."...";
								}else 
								{
									$descricao = 	$prazo['descricao'];
								}
								
								if( $qtd < $max_comp_dia )
								{
									$compromisso_geral .= "<a href='juris_tramite_det.php?cod_tramite={$lista_tramite[0]['cod_juris_tramite']}&versao_tramite={$lista_tramite[0]['versao_tramite']}' ><span class='textoAgenda' $id>- Prazo: {$descricao}</span></a><br>";
								}
								$qtd++;
							}
						}
					}
				}
			
			
				$data_array = explode("/",$dataAtual);
				$data_array = "{$data_array[2]}/{$data_array[1]}/{$data_array[0]}";
				
				$db = new clsBanco();
				$db->Consulta( "SELECT ref_cod_agenda FROM agenda_responsavel WHERE ref_ref_cod_pessoa_fj = '{$id_pessoa}' AND principal = 1" );
				if( $db->ProximoRegistro() )
				{
					list( $cod_agenda ) = $db->Tupla();
					$obj_agenda = new clsAgenda( $id_pessoa, false, $cod_agenda );
				}
				else 
				{
					$obj_agenda = new clsAgenda( $id_pessoa, $id_pessoa, false );
					$cod_agenda = $obj_agenda->getCodAgenda();
				}
				
				$nomeAgenda = $obj_agenda->getNome();
				
				$lista_compromissos = $obj_agenda->listaCompromissosDia($data_array);
				if($lista_compromissos)
				{
					//print_r($lista_compromissos);
					foreach ($lista_compromissos as $compromisso)
					{
						if( $qtd < $max_comp_dia )
						{
							$disp_comp = $compromisso['descricao'];
							$titulo = $compromisso['titulo'];
							$qtd_tit_copia_desc = 5;
							
							if( $titulo )
							{
								$disp_titulo = $titulo;
							}
							else 
							{
								// se nao tiver titulo pega as X primeiras palavras da descricao ( X = $qtd_tit_copia_desc )
								$disp_titulo = implode( " ", array_slice( explode( " ", $disp_comp ), 0, $qtd_tit_copia_desc ) );
							}
							
							
							$disp_titulo = "- " . $disp_titulo;
							if(strlen($disp_titulo) > 15)
							{
								$disp_titulo = substr( $disp_titulo, 0, 12 ) . "...";
							}
							
							$temp_var++;
							$compromisso_geral .= "<span class='textoAgenda' id='comp_{$temp_var}'>$disp_titulo</span><br>";
							if( $compromisso["importante"] && strtotime( $compromisso["data_inicio"] ) > time() )
							{
								$endScript .= "x[{$temp_var}]= {$temp_var};";
							}
						}
						$qtd++;
					}
				}
				
				if($compromisso_geral && $dataAtual > date("Y/m/d", time() ) && date("Y/m/d",strtotime(substr($dataAtual,0,19))-3*86400) > date("Y/m/d", time() ))
				{
					$classe = "fundoCalendarioLonge";
				}
				elseif($compromisso_geral && $dataAtual > date("Y/m/d", time() ) && date("Y/m/d",strtotime(substr($dataAtual,0,19))-3*86400) <= date("Y/m/d", time() ) )
				{
					$classe = "fundoCalendarioProximo";
	
				}elseif($compromisso_geral && $dataAtual == date("Y/m/d",time()))
				{
					$classe = "fundoCalendarioUrgente";
	
				}else
				{
					$classe = "fundoCalendario";
				}			
				
				if($qtd == 2) 
					$pulaLinha = "<br>";
				if($qtd == 1) 
					$pulaLinha = "<br><br>";
				
				
				$data_temp = strtotime( substr( $dataAtual, 0, 19 ) );
				$compromisso_geral = ($compromisso_geral) ? "$compromisso_geral<a href='agenda.php?cod_agenda={$cod_agenda}&time=$data_temp' ><div align='center' class='textoAgenda'><b>Ver Todos</b></div>" : $compromisso_geral;
				
				if( $i ==1 )
				{
					$ultimoDiaUltimoMes = date( "t",mktime( 0, 0, 0, $mes-1, 1, $ano ) );
					$temp .= "<tr>";
					for($dias=0; $dias<$diaDaSemana;$dias++)
					{
						$dia = $ultimoDiaUltimoMes-$diaDaSemana+1+$dias;
						$temp .= "<td class='fundoCalendarioMesDiferente' valign='top'><span class='diasMes'>$dia</span></td>";
					}
					$temp .= "<td class='$classe' valign='top' background='imagens/nvp_calend_01.gif' style='background-repeat:no-repeat;'>$compromisso_geral</td>";
					
				}
				else 
				{
					if($diaDaSemana == 0)
					{
						$temp .= "</tr><tr><td class='$classe' valign='top' background='imagens/nvp_calend_" . $this->addLeadingZero( $i ) . ".gif' style='background-repeat:no-repeat;'>$compromisso_geral</td>";
					}else 
					{
						$temp .= "<td class='$classe' valign='top' background='imagens/nvp_calend_" . $this->addLeadingZero( $i ) . ".gif' style='background-repeat:no-repeat;'>$compromisso_geral</td>";
					}
				}
				
				$compromisso = "";
				$compromisso_geral = "";
			
			}
			$endScript .= "setInterval( 'pisca();', 1000 );";
			$dia = 1;
			for($i=$diaDaSemana; $i<6;$i++)
			{
				$temp .= "<td class='fundoCalendarioMesDiferente' valign='top'><span class='diasMes'>$dia</span></td>";
				$dia++;
			}
			$anterior = $mes-1;
			$proximo = $mes+1;
	
			$proximo_ano = date("Y",mktime(0,0,0,$mes+2,0,$ano));
			$ano_anterior = date("Y",mktime(0,0,0,$mes,0,$ano));
			if($proximo > 12)
				$proximo = 1;
			if($anterior < 1)
				$anterior = 12;

			$endScript .= "

				var a = 0;
				function pisca()
				{
					for( var i = 1; i<x.length; i++ )
					{
						if( typeof document.getElementById( 'comp_' + x[i] ) == 'object' )
						{

							obj = document.getElementById( 'comp_' + x[i] );

							obj.className = ( obj.className == 'textoAgenda' ) ? 'textoAgendaVermelho': 'textoAgenda';
						}
					}
					a=1;
				}
			</script>";

			$temp .= "</tr>
			<tr>
				<td colspan=\"6\" align=\"center\" class=\"fundoCalendarioTopo\"><a href='index.php?mes=$anterior&ano=$ano_anterior'><img src=\"imagens/nvp_agn_anterior.jpg\" width=\"84\" height=\"20\" border=\"0\" style=\"padding-top:4px;\"></a> &nbsp;&nbsp;<a href='index.php?mes=$proximo&ano=$proximo_ano'><img src=\"imagens/nvp_agn_proximo.jpg\" width=\"84\" height=\"20\" border=\"0\" style=\"padding-top:4px;\"></a></td>
				<td align=center class='fundoCalendarioTopo'>$mes/$ano</td>
			</tr>
			<tr>
				<td colspan=\"7\" class=\"fundoCalendario\" style=\"height:15px\"><h3 style=\"padding-top:2px;margin:0px\">Agenda do(a): <a href=\"agenda.php?cod_agenda={$cod_agenda}\">$nomeAgenda</a></h3></td>
			</tr>
			</table>{$endScript}";
			return $temp;
		}
		else 
		{
			header( "location: logof.php?login=1" );
			die();
		}
	}
}

$pagina = new clsIndex();
$miolo = new indice();
$pagina->addForm( $miolo );
$pagina->MakeAll();

?>
