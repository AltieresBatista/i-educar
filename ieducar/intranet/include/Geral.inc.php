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

require_once ("include/pessoa/clsPessoa_.inc.php");
require_once ("include/pessoa/clsPessoaFj.inc.php");
require_once ("include/pessoa/clsPessoaJuridica.inc.php");
require_once ("include/pessoa/clsPessoaFisica.inc.php");
require_once ("include/pessoa/clsPessoaTelefone.inc.php");
require_once ("include/pessoa/clsEnderecoPessoa.inc.php");
require_once ("include/pessoa/clsEnderecoExterno.inc.php");
require_once ("include/pessoa/clsEndereco.inc.php");
require_once ("include/pessoa/clsFisicaCpf.inc.php");
require_once ("include/pessoa/clsFisica.inc.php");
require_once ("include/pessoa/clsJuridica.inc.php");
require_once ("include/pessoa/clsCepLogradouroBairro.inc.php");
require_once ("include/pessoa/clsCepLogradouro.inc.php");
require_once ("include/pessoa/clsLogradouro.inc.php");
require_once ("include/pessoa/clsBairro.inc.php");
require_once ("include/pessoa/clsMunicipio.inc.php");
require_once ("include/pessoa/clsUf.inc.php");
require_once ("include/pessoa/clsPais.inc.php");
require_once ("include/pessoa/clsVila.inc.php");
require_once ("include/pessoa/clsTipoLogradouro.inc.php");
require_once ("include/pessoa/clsFuncionario.inc.php");
require_once ("include/pessoa/clsEscolaridade.inc.php");
require_once ("include/pessoa/clsEstadoCivil.inc.php");
require_once ("include/pessoa/clsOcupacao.inc.php");
require_once ("include/pessoa/clsFisica.inc.php");
require_once ("include/pessoa/clsOrgaoEmissorRg.inc.php");
require_once ("include/pessoa/clsDocumento.inc.php");
require_once ("include/pessoa/clsRegiao.inc.php");
require_once ("include/pessoa/clsEscolaridade.inc.php");
require_once ("include/pessoa/clsCadastroEscolaridade.inc.php");
require_once ("include/pessoa/clsCadastroDeficiencia.inc.php");
require_once ("include/pessoa/clsCadastroFisicaDeficiencia.inc.php");
require_once( "include/pmidrh/clsSetor.inc.php" );



require_once ("include/pmidrh/geral.inc.php");
require_once ("include/pessoa/clsBairroRegiao.inc.php");
require_once ("include/funcoes.inc.php");
require_once ( "include/clsParametrosPesquisas.inc.php" );
require_once ("include/portal/geral.inc.php");
require_once ("include/public/geral.inc.php");
require_once ("include/urbano/geral.inc.php");


?>