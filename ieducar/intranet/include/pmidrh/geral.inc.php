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
	require_once( "include/clsBanco.inc.php" );
	require_once( "include/Geral.inc.php" );

	require_once( "include/pmidrh/clsPmidrhCargos.inc.php" );
	require_once( "include/pmidrh/clsPmidrhDiaria.inc.php" );
	require_once( "include/pmidrh/clsPmidrhDiariaGrupo.inc.php" );
	require_once( "include/pmidrh/clsPmidrhDiariaValores.inc.php" );
	require_once( "include/pmidrh/clsPmidrhLogVisualizacaoOlerite.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortaria.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortariaCamposEspeciaisValor.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortariaCamposTabela.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortariaFuncionario.inc.php" );
	require_once( "include/pmidrh/clsPmidrhStatus.inc.php" );
	require_once( "include/pmidrh/clsPmidrhTipoPortaria.inc.php" );
	require_once( "include/pmidrh/clsPmidrhTipoPortariaCamposEspeciais.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortariaCamposTabelaValor.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortariaResponsavel.inc.php" );
	require_once( "include/pmidrh/clsPmidrhPortariaAssinatura.inc.php" );
	require_once( "include/pmidrh/clsPmidrhUsuario.inc.php" );
	require_once( "include/pmidrh/clsPmidrhInstituicao.inc.php" );
	require_once( "include/pmidrh/clsSetor.inc.php" );

?>