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
?>

<html>
<head>

	<link rel=stylesheet type='text/css' href='http://nat-bp-spo.cobra.com.br/styles/menu.css' >
	<script type='text/javascript' src='http://nat-bp-spo.cobra.com.br/scripts/dom.js'></script>
	<script type='text/javascript' src='http://nat-bp-spo.cobra.com.br/scripts/menu.js'></script>
</head>
<body>
<script>
/************************************************************
Coolmenus Beta 4.04 - Copyright Thomas Brattli - www.dhtmlcentral.com
Last updated: 03.22.02
*************************************************************/
/*Browsercheck object*/

menu[0] = new Array("Estoque",1,'','', '');
menu[1] = new Array("Higor",2,1,'','');
menu[2] = new Array("Cadastro",4,'','','');


</script>
<table>
<tr>
<td id="as">
assas
</td>

<td id="pega">

<script>
				var posx = DOM_ObjectPosition_getPageOffsetLeft(document.getElementById('pega'));
				var posy = DOM_ObjectPosition_getPageOffsetTop(document.getElementById('pega'));
				MontaMenu(menu, posx,posy);
</script>
<td>
</tr>
</body>

</html>

