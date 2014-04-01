<?php

/**
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
 *
 * @author      Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     ComponenteCurricular
 * @subpackage  Modules
 * @since       ?
 * @version     $Id$
 */

require_once 'CoreExt/Enum.php';

/**
 * ComponenteCurricular_Model_CodigoEducacenso class.
 *
 * @author      Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     ComponenteCurricular
 * @subpackage  Modules
 * @since       ?
 * @version     @@package_version@@
 */
class ComponenteCurricular_Model_CodigoEducacenso extends CoreExt_Enum
{

  protected $_data = array(
    1    => 'Qu�mica',
    2    => 'F�sica',
    3    => 'Matem�tica',
    4    => 'Biologia',
    5    => 'Ci�ncias',
    6    => 'L�ngua/Literatura portuguesa',
    7    => 'L�ngua/Literatura extrangeira - Ingl�s',
    8    => 'L�ngua/Literatura extrangeira - Espanhol',
    30   => 'L�ngua/Literatura extrangeira - Franc�s',
    9    => 'L�ngua/Literatura extrangeira - Outra',
    10   => 'Artes (educa��o art�stica, teatro, dan�a, m�sica, artes pl�sticas e outras)',
    11   => 'Educa��o f�sica',
    12   => 'Hist�ria',
    13   => 'Geografia',
    14   => 'Filosofia',
    28   => 'Estudos sociais',
    29   => 'Sociologia',
    16   => 'Inform�tica/Computa��o',
    17   => 'Discilpinas profissionalizantes',
    20   => 'Disciplinas voltadas ao atendimento �s necessidades educacionais especificas dos alunos que s�o alvo da educa��o 
           especial e �s praticas educacionais inclusivas',
    21   => 'Disciplinas voltadas � diversidade sociocultural',
    23   => 'LIBRAS',
    25   => 'Disciplinas pedag�gicas',
    26   => 'Ensino religioso',
    27   => 'L�ngua ind�gena',
    99   => 'Outras disciplinas'
  );

  public static function getInstance()
  {
    return self::_getInstance(__CLASS__);
  }
}