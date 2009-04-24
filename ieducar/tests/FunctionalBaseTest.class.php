<?php

/**
 * FunctionBaseTest class.
 *
 * Cont�m as configura��es de acesso ao servidor Selenium RC, a conta de usu�rio
 * a ser utilizada no teste e alguns m�todos auxiliares.
 *
 * Muda o diret�rio atual para que os testes possam ser facilmente invocados
 * em qualquer subdiret�rio do sistema.
 *
 * Abstrai o PHPUnit, diminuindo a depend�ncia de seu uso. Inclui a classe
 * de banco de dados para facilitar no tearDown de dados de teste.
 *
 * @author  Eriksen Costa <eriksen.paixao_bs@cobra.com.br>
 * @since   1.0.1
 * @version SVN: $Id$
 */

chdir(realpath(dirname(__FILE__) . '/../') . '/intranet');
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'include/clsBanco.inc.php';

abstract class FunctionalBaseTest extends PHPUnit_Extensions_SeleniumTestCase {

  // Configura��es do Selenium RC
  static protected
    $slBrowserUrl = 'http://ieducar.local',
    $slBrowser    = '*firefox',
    $slPort       = 4444,
    $slHost       = 'localhost';

  // Conta de usu�rio para testes funcionais
  protected
    $slUsuarioLogin = 'ieducar',
    $slUsuarioSenha = '12345678';

  protected function setUp() {
    $this->setBrowser(self::$slBrowser);
    $this->setHost(self::$slHost);
    $this->setPort(self::$slPort);
    $this->setBrowserUrl(self::$slBrowserUrl);
  }

  protected function doLogin() {
    $this->open('/intranet');
    $this->type('login', $this->slUsuarioLogin);
    $this->type('senha', $this->slUsuarioSenha);
    $this->clickAndWait("//input[@value='Entrar']");
  }

  protected function doLogout() {
    $this->click("//img[@alt='Logout']");
  }

}