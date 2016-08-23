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
 * Meus dados.
 *
 * @author   Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @license  http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package  Core
 * @since    Arquivo dispon�vel desde a vers�o 1.0.0
 * @version  $Id$
 */

$desvio_diretorio = '';
require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/RDStationAPI.class.php';
require_once 'lib/Portabilis/String/Utils.php';

class clsIndex extends clsBase
{
  public function Formular() {
    $this->SetTitulo($this->_instituicao . 'Usu&aacute;rios');
    $this->processoAp = '0';
  }
}

class indice extends clsCadastro
{

  var $pessoa_logada;

  var $nome;
  var $ddd_telefone;
  var $telefone;
  var $ddd_celular;
  var $celular;
  var $email;
  var $senha;
  var $senha_confirma;
  var $sexo;
  var $senha_old;
  var $matricula_old;

  var $receber_novidades;

  public function Inicializar() {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    $retorno = "Novo";

    $pessoaFisica = new clsPessoaFisica($this->pessoa_logada);
    $pessoaFisica = $pessoaFisica->detalhe();

    if ($pessoaFisica) {
      $this->nome = $pessoaFisica['nome'];

      if ($pessoaFisica) {

        $this->ddd_telefone = $pessoaFisica['ddd_1'];
        $this->telefone = $pessoaFisica['fone_1'];
        $this->ddd_celular = $pessoaFisica['ddd_mov'];
        $this->celular = $pessoaFisica['fone_mov'];
        $this->sexo = $pessoaFisica['sexo'];
      }

      $this->email = $pessoaFisica['email'];

      $funcionario = new clsPortalFuncionario($this->pessoa_logada);
      $funcionario = $funcionario->detalhe();

      if ($funcionario) {
        $this->senha = $funcionario["senha"];
        $this->senha_confirma = $funcionario["senha"];
        $this->matricula = $funcionario["matricula"];

        $this->senha_old = $funcionario["senha"];
        $this->matricula_old = $funcionario["matricula"];
        $this->receber_novidades = $funcionario["receber_novidades"];
      }
    }

    $this->url_cancelar      = 'index.php';
    $this->nome_url_cancelar = 'Cancelar';

    return $retorno;
  }

  public function Gerar() {
    $this->campoOculto('senha_old', $this->senha_old);
    $this->campoOculto('matricula_old', $this->matricula_old);

    $this->campoTexto("nome", "Nome", $this->nome, 50, 150, true);
    $this->campoTexto("matricula", "Matr�cula", $this->matricula, 25, 12, true);

    $options = array(
      'required'    => false,
      'label'       => "(ddd) / Telefone",
      'placeholder' => 'ddd',
      'value'       => $this->ddd_telefone,
      'max_length'  => 3,
      'size'        => 3,
      'inline'      => true
    );

    $this->inputsHelper()->integer("ddd_telefone", $options);

    $options = array(
      'required'    => false,
      'label'       => '',
      'placeholder' => 'Telefone',
      'value'       => $this->telefone,
      'max_length'  => 11
    );

    $this->inputsHelper()->integer("telefone", $options);

    $options = array(
      'required'    => false,
      'label'       => "(ddd) / Celular",
      'placeholder' => 'ddd',
      'value'       => $this->ddd_celular,
      'max_length'  => 3,
      'size'        => 3,
      'inline'      => true
    );

    $this->inputsHelper()->integer("ddd_celular", $options);

    $options = array(
      'required'    => false,
      'label'       => '',
      'placeholder' => 'Celular',
      'value'       => $this->celular,
      'max_length'  => 11
    );

    $this->inputsHelper()->integer("celular", $options);

    $this->campoTexto("email", "E-mail", $this->email, 50, 100, true);

    $this->campoSenha('senha', "Senha", $this->senha, TRUE);
    $this->campoSenha('senha_confirma', "Confirma��o de senha", $this->senha_confirma, TRUE);

    $lista_sexos = array('' => 'Selecione',
                        'M' => 'Masculino',
                        'F' => 'Feminino');
    $this->campoLista("sexo", "Sexo", $lista_sexos, $this->sexo);

    $this->campoQuebra();

    if (is_null($this->receber_novidades)) $this->receber_novidades = 1;

    $options = array('label' => 'Desejo receber novidades do produto por e-mail', 'value' => $this->receber_novidades);
    $this->inputsHelper()->checkbox('receber_novidades', $options);

  }

  public function Novo() {
    $this->Editar();
  }

  public function Editar() {
    @session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    @session_write_close();

    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      $this->mensagem = "Formato do e-mail inv�lido.";
      return false;
    }

    // Valida��o de senha
    if ($this->senha != $this->senha_confirma) {
      $this->mensagem = "As senhas que voc� digitou n�o conferem.";
      return false;
    } elseif (strlen($this->senha) < 8) {
      $this->mensagem = "Por favor informe uma senha mais segura, com pelo menos 8 caracteres.";
      return false;
    } elseif (strrpos($this->senha, $this->matricula)) {
      $this->mensagem = "A senha informada &eacute; similar a sua matricula, informe outra senha.";
      return false;
    }

    $telefone = new clsPessoaTelefone($this->pessoa_logada, 1, str_replace("-", "", $this->telefone), $this->ddd_telefone);
    $telefone->cadastra();

    $celular = new clsPessoaTelefone($this->pessoa_logada, 3, str_replace("-", "", $this->celular), $this->ddd_celular);
    $celular->cadastra();

    $pessoa = new clsPessoa_($this->pessoa_logada);
    $pessoa->nome = $this->nome;
    $pessoa->email = $this->email;
    $pessoa->edita();

    $pessoaFisica = new clsFisica($this->pessoa_logada, FALSE, $this->sexo);
    $pessoaFisica->edita();

    $funcionario = new clsPortalFuncionario();

    if ($this->matricula != $this->matricula_old) {
      $existeMatricula = $funcionario->lista($this->matricula);
      if ($existeMatricula) {
        $this->mensagem = "A matr�cula informada j� perdence a outro usu�rio.";
        return false;
      }
      $funcionario->matricula = $this->matricula;
    }
    $funcionario->ref_cod_pessoa_fj = $this->pessoa_logada;
    $funcionario->receber_novidades = ($this->receber_novidades ? 1 : 0);
    $funcionario->atualizou_cadastro = 1;

    if ($this->senha_old != $this->senha) {
      $funcionario->senha = md5($this->senha);
    }

    $funcionario->edita();

    $usuario = new clsPmieducarUsuario($this->pessoa_logada);
    $usuario = $usuario->detalhe();

    if ($usuario) {
      $instituicao = new clsPmieducarInstituicao($usuario['ref_cod_instituicao']);
      $instituicao = $instituicao->detalhe();

      $instituicao = $instituicao['nm_instituicao'];

      $escola = new clsPmieducarEscola($usuario['ref_cod_escola']);
      $escola = $escola->detalhe();

      $escola = $escola['nome'];
    }

    $configuracoes = new clsPmieducarConfiguracoesGerais();
    $configuracoes = $configuracoes->detalhe();

    $permiteRelacionamentoPosvendas = ($configuracoes['permite_relacionamento_posvendas'] ? "Sim" : "Nao");

    $dados = array(
      "nome" => Portabilis_String_Utils::toUtf8($this->nome),
      "empresa" => Portabilis_String_Utils::toUtf8($instituicao),
      "cargo" => Portabilis_String_Utils::toUtf8($escola),
      "telefone" => ($this->telefone ? "$this->ddd_telefone $this->telefone" : null),
      "celular" => ($this->celular ? "$this->ddd_celular $this->celular" : null),
      "Assuntos de interesse" => ($this->receber_novidades ? "Todos os assuntos relacionados ao i-Educar" : "Nenhum"),
      "Permite relacionamento direto no pos-venda?" => $permiteRelacionamentoPosvendas
    );

    // echo "<pre>";print_r($dados);die;

    $rdAPI = new RDStationAPI("***REMOVED***","***REMOVED***");

    $rdAPI->sendNewLead($this->email, $dados);
    $rdAPI->updateLeadStage($this->email, 2);

    $this->mensagem .= "Edi��oo efetuada com sucesso.<br>";
    header( "Location: index.php" );
    die();
  }

}

// Instancia objeto de p�gina
$pagina = new clsIndex();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do � p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();
