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

	require_once( "include/pmicontrolesis/clsMenuSuspenso.inc.php" );
	require_once( "include/pmicontrolesis/clsTutormenu.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisAcontecimento.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisTipoAcontecimento.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisPortais.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisServicos.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisItinerario.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisTelefones.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisSistema.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisArtigo.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisTopoPortal.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisMenuPortal.inc.php" );

	require_once( "include/pmicontrolesis/clsPmicontrolesisSoftware.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisSoftwareAlteracao.inc.php" );
	require_once( "include/pmicontrolesis/clsPmicontrolesisSoftwarePatch.inc.php" );

?>