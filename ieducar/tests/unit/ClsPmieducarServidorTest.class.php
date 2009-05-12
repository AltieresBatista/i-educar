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
 * ClsPmieducarServidorTest class
 *
 * @author   Eriksen Costa Paix�o <eriksen.paixao_bs@cobra.com.br>
 * @license  http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package  Test
 * @since    Classe dispon�vel desde a vers�o 1.0.1
 * @version  $Id$
 */

require_once realpath(dirname(__FILE__) . '/../') . '/UnitBaseTest.class.php';
require_once 'include/pmieducar/clsPmieducarServidor.inc.php';

class ClsPmieducarServidorTest extends UnitBaseTest {

  private
    $codServidor    = NULL,
    $codInstituicao = NULL;

  /**
   * @todo  Testes dependentes de dados existentes. Refatorar o teste para usar
   *        mock objects ou dbunit
   */
  protected function setUp() {
    $db = new clsBanco();
    $sql = 'SELECT cod_servidor, ref_cod_instituicao FROM pmieducar.servidor WHERE ativo = 1 LIMIT 1';

    $db->Consulta($sql);
    $db->ProximoRegistro();
    list($this->codServidor, $this->codInstituicao) = $db->Tupla();
  }


  /**
   * Testa se o servidor criado no m�todo setUp() existe
   */
  public function testPmieducarServidorExists() {
    $codServidor    = $this->codServidor;
    $codInstituicao = $this->codInstituicao;

    $servidor = new clsPmieducarServidor(
      $codServidor, NULL, NULL, NULL, NULL, NULL, 1, $codInstituicao);

    $this->assertTrue((boolean) $servidor->existe());
  }


  /**
   * Testa o m�todo getServidorFuncoes() da classe
   */
  public function testGetServidorFuncoes() {
    $codServidor    = $this->codServidor;
    $codInstituicao = $this->codInstituicao;

    $servidor = new clsPmieducarServidor(
      $codServidor, NULL, NULL, NULL, NULL, NULL, 1, $codInstituicao);

    $funcoes = $servidor->getServidorFuncoes();
    $this->assertTrue(is_array($funcoes));
  }


  /**
   * Testa o m�todo isProfessor()
   */
  public function testIsProfessor() {
    $codServidor    = $this->codServidor;
    $codInstituicao = $this->codInstituicao;

    $servidor = new clsPmieducarServidor(
      $codServidor, NULL, NULL, NULL, NULL, NULL, 1, $codInstituicao);

    $professor = $servidor->isProfessor();

    $this->assertTrue($professor);
  }


  /**
   * Stub test para o m�todo getServidorDisciplinasQuadroHorarioHorarios()
   */
  public function testGetServidorDisciplinasQuadroHorarioHorarios() {
    $stub = $this->getMock('clsPmieducarServidor');

    $stub->expects($this->any())
         ->method('getServidorDisciplinasQuadroHorarioHorarios')
         ->will($this->returnValue(array(2, 6)));

    $this->assertEquals(array(2, 6),
      $stub->getServidorDisciplinasQuadroHorarioHorarios(62, 2));
  }


  /**
   * Stub test para o m�todo getServidorDisciplinas()
   */
  public function testGetServidorDisciplinas() {
    $stub = $this->getMock('clsPmieducarServidor');

    $stub->expects($this->any())
         ->method('getServidorDisciplinas')
         ->will($this->returnValue(array(6)));

    $this->assertEquals(array(6),
      $stub->getServidorDisciplinas(57, 2));
  }

}