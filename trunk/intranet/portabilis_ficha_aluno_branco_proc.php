<?php

/*
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu�do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl�cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 */

/**
 * Hist�rico escolar.
 *
 * @author      Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @license     http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package     Core
 * @subpackage  Aluno
 * @since       Arquivo dispon�vel desde a vers�o 1.0.0
 * @version     $Id: educar_relatorio_historico_escolar_proc.php 58 2009-07-17 18:57:29Z eriksen.paixao_bs@cobra.com.br $
 */
require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'include/clsPDF.inc.php';

require_once 'relatorios/phpjasperxml/class/fpdf/fpdf.php';
require_once 'relatorios/phpjasperxml/class/PHPJasperXML.inc';


class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Ficha de Aluno em Branco" );
		$this->processoAp = "999204";
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
	var $ref_cod_aluno;

	var $nm_escola;
	var $nm_instituicao;
	var $nm_curso;
	var $nm_municipio;

	var $pdf;

	var $page_y = 195;

	var $get_link;
	var $cor_fundo;
	var $endereco;

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
	
	$xml =  simplexml_load_file("relatorios/jasperreports/portabilis_ficha_aluno_branco.jrxml");
	

/*	print "instituicao: ";
	print $_POST['ref_cod_instituicao'];
	print "escola: ";
	print $_POST['ref_cod_escola'];
	print "aluno: ";
	print $_POST['ref_cod_aluno'];
	print "serie: ";
	print $_POST['ref_ref_cod_serie'];
	print "aluno: ";
	print $_POST['nm_aluno'];
	print $_POST['ano'];
	print $_POST['data_validade'];
	print "passei";
	
*/

	$PHPJasperXML = new PHPJasperXML();
	$PHPJasperXML->debugsql=false;
	$PHPJasperXML->arrayParameter=array("instituicao"=>$_POST['ref_cod_instituicao'],"escola"=>$_POST['ref_cod_escola']); 
    $PHPJasperXML->xml_dismantle($xml);

	$PHPJasperXML->transferDBtoArray($server,$user,$pass,$db,$port);
	$PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file

		if($_POST){
			$query = "";
			foreach ($_POST as $key => $value) {
				//$query .= $key . '=' . $value . '&';
				//$this->$key = $value;
			}
		}
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