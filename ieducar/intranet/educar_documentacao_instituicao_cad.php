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

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Documenta��o padr�o" );
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

	var $cod_instituicao;

	function Inicializar()
	{
		$retorno = "Documenta��o padr�o";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$localizacao = new LocalizacaoSistema();
	    $localizacao->entradaCaminhos( array(
	         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
	         "educar_escola_index.php"                  => "i-Educar - Escola",
	         ""                                  => "Documenta��o padr�o"
	    ));
	    $this->enviaLocalizacao($localizacao->montar());

		$this->cod_instituicao=$_GET["cod_instituicao"];

		return $retorno;
	}

	function Gerar()
	{
		Portabilis_View_Helper_Application::loadJavascript($this, array('/modules/Cadastro/Assets/Javascripts/Instituicao.js'));
		Portabilis_View_Helper_Application::loadStylesheet($this, array('/modules/Cadastro/Assets/Stylesheets/Instituicao.css'));

		$obj_usuario = new clsPmieducarUsuario($this->pessoa_logada);
		$obj_usuario_det = $obj_usuario->detalhe();
 		$this->ref_cod_escola = $obj_usuario_det["ref_cod_escola"];

		$this->campoOculto( "cod_instituicao", $this->cod_instituicao );
		$this->campoOculto( "pessoa_logada", $this->pessoa_logada );
		$this->campoOculto( "ref_cod_escola", $this->ref_cod_escola );


	    $this->campoTexto( "titulo_documento", "T�tulo", $this->titulo_documento, 30, 50, false );

	    $this->campoArquivo('documento','Documenta��o padr�o',$this->documento,40,Portabilis_String_Utils::toLatin1("<span id='aviso_formato'>S�o aceitos apenas arquivos no formato PDF com at� 2MB.</span>", array('escape' => false)));

		$this->array_botao[] = 'Salvar';
        $this->array_botao_url_script[] = "go('educar_instituicao_lst.php')";

        $this->array_botao[] = 'Voltar';
        $this->array_botao_url_script[] = "go('educar_instituicao_lst.php')";
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