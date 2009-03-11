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
		$this->SetTitulo( " i-Educar - Rela&ccedil;&atilde;o Servidores por N&iacute;vel" );
		$this->processoAp = "831";
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

	var $get_link;


	function renderHTML()
	{

		if($_POST){
			foreach ($_POST as $key => $value) {
				$this->$key = $value;

			}
		}

		$fonte = 'arial';
		$corTexto = '#000000';

		if(empty($this->ref_cod_instituicao))
		{
	     	echo '<script>
	     			alert("Erro ao gerar relat�rio!\nNenhuma institui&ccedil;&atilde;o selecionada!");
	     			window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));
	     		</script>';
	     	return true;
		}

		$obj_instituicao = new clsPmieducarInstituicao($this->ref_cod_instituicao);
		$det_instituicao = $obj_instituicao->detalhe();
		$this->nm_instituicao = $det_instituicao['nm_instituicao'];

		if($this->ref_cod_escola)
		{

			$obj_escola = new clsPmieducarEscola($this->ref_cod_escola);
			$det_escola = $obj_escola->detalhe();
			$this->nm_escola = $det_escola['nome'];
		}

		if($this->ref_cod_escola)
		{
			$sql = "SELECT s.cod_servidor
					       ,p.nome
					       ,sn.nm_subnivel
					       ,sn.salario
					       ,n.nm_nivel
					  FROM pmieducar.servidor s
					       ,pmieducar.subnivel sn
					       ,pmieducar.nivel n
					       ,cadastro.pessoa p
					       ,pmieducar.servidor_alocacao a
					 WHERE 
					   s.cod_servidor = p.idpes
					   AND s.ref_cod_subnivel IS NOT NULL
					   AND s.ref_cod_subnivel = sn.cod_subnivel
					   AND sn.ref_cod_nivel = n.cod_nivel
					   AND s.ref_cod_instituicao = {$this->ref_cod_instituicao}
					   AND a.ref_cod_servidor = s.cod_servidor
					   AND a.ref_cod_escola = {$this->ref_cod_escola}
					   $where
					   AND s.ativo  = 1
					 ORDER BY p.nome
					";
		}
		else
		{

			$sql = "SELECT s.cod_servidor
					       ,p.nome
					       ,sn.nm_subnivel
					       ,sn.salario
					       ,n.nm_nivel
					  FROM pmieducar.servidor s
					       ,pmieducar.subnivel sn
					       ,pmieducar.nivel n
					       ,cadastro.pessoa p
					 WHERE 
					   s.cod_servidor = p.idpes
					   AND s.ref_cod_subnivel IS NOT NULL
					   AND s.ref_cod_subnivel = sn.cod_subnivel
					   AND sn.ref_cod_nivel = n.cod_nivel
					   AND s.ref_cod_instituicao = {$this->ref_cod_instituicao}
					   $where
					   AND s.ativo  = 1
					 ORDER BY p.nome
					";
		}

		$db = new clsBanco();
		$db->Consulta($sql);

		if($db->Num_Linhas())
		{

			$relatorio = new relatorios("Servidores por N�vel", 210, false, "Servidores por N�vel", "A4", "{$this->nm_instituicao}\n{$this->nm_escola}");
			$relatorio->setMargem(20,20,50,50);
			$relatorio->exibe_produzido_por = false;

			$relatorio->novalinha( array(  "Nome", "N�vel", "Sal�rio"),0,16,true,"arial",array( 75, 320, 100),"#515151","#d3d3d3","#FFFFFF",false,true);

			while ($db->ProximoRegistro())
			{
				list($cod_servidor, $nome, $subnivel, $salario, $nivel) = $db->Tupla();				

				$relatorio->novalinha( array(  "{$nome}","{$nivel{$subnivel}}", "{$salario}"),0,16,false,"arial",array( 75, 330, 80),"#515151","#d3d3d3","#FFFFFF",false,false);
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
