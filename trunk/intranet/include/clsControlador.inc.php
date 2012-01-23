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

require_once 'include/clsBanco.inc.php';


/**
 * clsControlador class.
 *
 * @author   Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @license  http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package  Core
 * @since    Classe dispon�vel desde a vers�o 1.0.0
 * @version  $Id: /ieducar/branches/1.1.0-avaliacao/ieducar/intranet/include/clsControlador.inc.php 662 2009-11-17T18:28:48.404882Z eriksen  $
 */
class clsControlador
{

  /**
   * @var boolean
   */
  public $logado;

  /**
   * @var string
   */
  public $erroMsg;


  /**
   * Construtor.
   */
  public function clsControlador()
  {

    /*
      Desabilitado esta linha para usar o valor setado no php.ini > session.cookie_lifetime  
      @session_set_cookie_params(200);
    */

    @session_start();

    if ('logado' == $_SESSION['itj_controle']) {
      $this->logado = TRUE;
    }
    else {
      $this->logado = FALSE;
    }

    // Controle dos menus
    if (isset($_GET['mudamenu']) && isset($_GET['categoria']) && isset($_GET['acao']))
    {
      if ($_GET['acao']) {
        $_SESSION['menu_opt'][$_GET['categoria']] = 1;
        $_SESSION['menu_atual'] = $_GET['categoria'];
      }
      else {
        // Est� apagando vari�vel session com o �ndice dado por $_GET
        unset($_SESSION['menu_opt'][$_GET['categoria']]);
        if ($_SESSION['menu_atual'] == $_GET['categoria']) {
          unset($_SESSION['menu_atual']);
        }
      }

      $db = new clsBanco();
      if (isset($_SESSION['id_pessoa'])) {
        $db->Consulta("UPDATE funcionario SET opcao_menu = '" . serialize( $_SESSION['menu_opt'] ) . "' WHERE ref_cod_pessoa_fj = '" . $_SESSION['id_pessoa'] . "'");
      }
    }

    session_write_close();
  }

  /**
   * Retorna TRUE para usu�rio logado
   * @return  boolean
   */
  public function Logado()
  {
    return $this->logado;
  }

  
  /**
   * Executa o login do usu�rio.
   */
  public function obriga_Login()
  {
    if ($_POST['login'] && $_POST['senha']) {
      $this->logar(TRUE);
    }
    if (!$this->logado) {
      $this->logar(FALSE);
    }
  }

  //novo metodo login
  public function Logar($validateCredentials) {
    $this->_loginMsgs = array();

    if ($validateCredentials) {
      $username = @$_POST['login'];
      $password = md5(@$_POST['senha']);
      $userId = $this->validateUser($username, $password);

      if ($this->canStartLoginSession($userId))
        $this->startLoginSession($userId);
      else {
        $this->validateHumanAccess();
        $this->renderLoginPage();
      }
    }
    else
      $this->renderLoginPage();
  }


  //metodos usados pelo novo metodo de login
  protected function validateUser($username, $password) {
    $sql = "SELECT ref_cod_pessoa_fj FROM portal.funcionario WHERE matricula = $1 and senha = $2";
    $userId = $this->fetchPreparedQuery($sql, array($username, $password), true, 'first-field');

    if (! is_numeric($userId))
      $this->appendLoginMsg("Usu�rio ou senha incorreta.", "error");

    return $userId;
  }


  public function canStartLoginSession($userId) {

    if (! $this->hasLoginMsgWithType("error")) {
      if ($this->fetchPreparedQuery("SELECT ativo FROM portal.funcionario WHERE ref_cod_pessoa_fj = $1",
                                    $userId, true, 'first-field') != '1') {
        $this->appendLoginMsg("Aparentemente sua conta de usu�rio esta inativa (expirada), por favor, " .
                              "entre em contato com o administrador do sistema.", "error");
      }

      elseif ($this->fetchPreparedQuery("SELECT proibido FROM portal.funcionario WHERE ref_cod_pessoa_fj = $1",
                                    $userId, true, 'first-field') != '0') {
        $this->appendLoginMsg("Aparentemente sua conta n�o pode acessar o sistema, " .
                              "por favor, entre em contato com o administrador do sistema.", "error");
      }
    }

    #TODO verificar se conta expirou (se sim, inativar conta)
    #TODO verificar se senha expirou
    #TODO verifica se usuario acessou de outro ip em memos de 10 minutos (eliminar esta verifica��o ?), se bloquear;
    #TODO verificar se conta nunca usada (exibir mensagem ?)

    return ! $this->hasLoginMsgWithType("error");
  }


  public function startLoginSession($userId, $redirectTo = '') {
    $sql = "SELECT ref_cod_pessoa_fj, opcao_menu, ref_cod_setor_new, tipo_menu, email, status_token FROM funcionario WHERE ref_cod_pessoa_fj = $1";
    $record = $this->fetchPreparedQuery($sql, $userId, true, 'first-line');

    @session_start();
    $_SESSION = array();
    $_SESSION['itj_controle'] = 'logado';
    $_SESSION['id_pessoa']    = $record['ref_cod_pessoa_fj'];
    $_SESSION['pessoa_setor'] = $record['ref_cod_setor_new'];
    $_SESSION['menu_opt']     = unserialize($record['opcao_menu']);
    $_SESSION['tipo_menu']    = $record['tipo_menu'];
    @session_write_close();

    $this->logado = true;
    $this->appendLoginMsg("Usu�rio logado com sucesso.", "success");

    $this->logAccess($userId);
    $this->destroyUserStatusToken($userId);

    //redireciona para usu�rio informar email, caso este seja inv�lido
    if (! filter_var($record['email'], FILTER_VALIDATE_EMAIL))
       header("Location: /module/Usuario/AlterarEmail");
    elseif(! empty($redirectTo))
       header("Location: $redirectTo");
  }


  protected function destroyLoginSession($addMsg = false) {
    @session_start();
    $_SESSION = array();
    @session_destroy();

    if ($addMsg)
      $this->appendLoginMsg("Usu�rio deslogado com sucesso.", "success");
  }


  /* Ao fazer login destroy solicita��es em aberto, como redefini��o de senha.
  */
  protected function destroyUserStatusToken($userId) {

    $statusTokensToDestoyOnLogin = array('redefinir_senha');

    $sql = "SELECT status_token FROM funcionario WHERE ref_cod_pessoa_fj = $1";
    $record = $this->fetchPreparedQuery($sql, $userId, true, 'first-line');

    $statusToken = explode('-', $record['status_token']);
    $statusToken = $statusToken[0];

    if(in_array($statusToken, $statusTokensToDestoyOnLogin)) {
      $sql = "UPDATE funcionario set status_token = '' WHERE ref_cod_pessoa_fj = $1";
      $record = $this->fetchPreparedQuery($sql, $userId, true);
    }    
  }


  protected function logAccess($userId) {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
      // pega o (ultimo) IP real caso o host esteja atr�s de um proxy
      $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      $ip = trim(array_pop($ip));
    }
    else
      $ip = $_SERVER['REMOTE_ADDR'];

    $sql = "UPDATE funcionario SET ip_logado = '{$ip}', data_login = NOW() WHERE ref_cod_pessoa_fj = $1";
    $this->fetchPreparedQuery($sql, $userId, true);
  }


  protected function validateHumanAccess() {
    /* #TODO se ocorreram mais de 5 tentativas erradas nos ultimos minutos,
             confirmar se usu�rio que esta acessando � humano, like http://www.google.com/recaptcha */
    return true;
  }


  protected function renderLoginPage() {
    $this->destroyLoginSession();

    $templateName = 'templates/nvp_htmlloginintranet.tpl';
    $templateFile  = fopen($templateName, "r");
    $templateText = fread($templateFile, filesize($templateName));
    $templateText = str_replace( "<!-- #&ERROLOGIN&# -->", $this->getLoginMsgs(), $templateText);

    fclose($templateFile);
    die($templateText);
  }


  protected function fetchPreparedQuery($sql, $params = array(), $hideExceptions = true, $returnOnly = '') {
    try{    
      $result = array();
      $db = new clsBanco();
      if ($db->execPreparedQuery($sql, $params) != false) {

        while ($db->ProximoRegistro())
          $result[] = $db->Tupla();

        if ($returnOnly == 'first-line' and isset($result[0]))
          $result = $result[0];
        elseif ($returnOnly == 'first-field' and isset($result[0]) and isset($result[0][0]))
          $result = $result[0][0];
      }
    }
    catch(Exception $e) 
    {
      if (! $hideExceptions)
        $this->appendLoginMsg($e->getMessage(), "error", true);
    }
    return $result;
  }


  protected function appendLoginMsg($msg, $type="error", $encodeToUtf8 = false){
    if ($encodeToUtf8)
      $msg = utf8_encode($msg);

    //error_log("$type msg: '$msg'");
    $this->_loginMsgs[] = array('msg' => $msg, 'type' => $type);
  }


  protected function hasLoginMsgWithType($type) {
    $hasMsg = false;

    foreach ($this->_loginMsgs as $m){
      if ($m['type'] == $type) {
        $hasMsg = true;
        break;
      }
    }

    return $hasMsg;
  }


  protected function getLoginMsgs() {
    $msgs = '';
    foreach($this->_loginMsgs as $m)
      $msgs .= "<p class='{$m['type']}'>{$m['msg']}</p>";
    return $msgs;
  }
}
