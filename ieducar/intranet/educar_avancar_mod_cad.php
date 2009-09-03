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
 * @author      Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @license     http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package     Core
 * @subpackage  pmieducar
 * @subpackage  Matricula
 * @subpackage  Rematricula
 * @since       Arquivo dispon�vel desde a vers�o 1.0.0
 * @version     $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';

class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar');
    $this->processoAp = '561';
  }
}

class indice extends clsCadastro
{
  var $pessoa_logada;

  function Inicializar()
  {
    $retorno = 'Novo';
    session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    return $retorno;
  }

  function Gerar()
  {
    $instituicao_obrigatorio        = true;
    $escola_obrigatorio             = true;
    $curso_obrigatorio              = true;
    $escola_curso_serie_obrigatorio = true;
    $turma_obrigatorio              = true;
    $get_escola                     = true;
    $get_curso                      = true;
    $get_escola_curso_serie         = true;
    $get_turma                      = true;
    $get_cursos_nao_padrao          = true;

    include 'include/pmieducar/educar_campo_lista.php';
  }

  function Novo()
  {
    session_start();
    $this->pessoa_logada = $_SESSION['id_pessoa'];
    session_write_close();

    $db  = new clsBanco();
    $db2 = new clsBanco();

    $ano = $db2->CampoUnico("SELECT MAX(ano) FROM pmieducar.escola_ano_letivo
      WHERE ref_cod_escola = {$this->ref_cod_escola} AND andamento = 1");

    if (! is_numeric($ano)) {
      $ano = date("Y");
    }

    // Aprovados
    $db->Consulta("SELECT cod_matricula, ref_cod_aluno
      FROM pmieducar.matricula m, pmieducar.matricula_turma
      WHERE aprovado = '1' AND m.ativo = '1' AND ref_ref_cod_escola = '{$this->ref_cod_escola}' AND ref_ref_cod_serie='{$this->ref_ref_cod_serie}' AND ref_cod_curso = '$this->ref_cod_curso' AND cod_matricula = ref_cod_matricula AND ref_cod_turma = '$this->ref_cod_turma' ");

    while ($db->ProximoRegistro()) {
      list($cod_matricula, $ref_cod_aluno) = $db->Tupla();
      $prox_mod = $db2->campoUnico("SELECT ref_serie_destino FROM pmieducar.sequencia_serie WHERE ref_serie_origem = '{$this->ref_ref_cod_serie}' AND ativo = '1' ");

      if (is_numeric($prox_mod)) {
        // Aqui localizar o pr�ximo curso
        $ref_cod_curso = $db2->CampoUnico("SELECT ref_cod_curso FROM pmieducar.serie WHERE cod_serie = {$prox_mod}");
        $db2->Consulta("UPDATE pmieducar.matricula SET ultima_matricula = '0' WHERE cod_matricula = '$cod_matricula'");

        $db2->Consulta("
          INSERT INTO pmieducar.matricula
            (ref_ref_cod_escola, ref_ref_cod_serie, ref_usuario_cad, ref_cod_aluno, aprovado, data_cadastro, ano, ref_cod_curso, ultima_matricula)
          VALUES
            ('{$this->ref_cod_escola}', '$prox_mod', '{$this->pessoa_logada}', '$ref_cod_aluno', '3', 'NOW()', '$ano', '{$ref_cod_curso}', '1')
        ");
      }
    }

    // Reprovados
    $db->Consulta("SELECT cod_matricula, ref_cod_aluno, ref_ref_cod_serie FROM pmieducar.matricula, pmieducar.matricula_turma WHERE aprovado = '2' AND ref_ref_cod_escola = '{$this->ref_cod_escola}' AND ref_ref_cod_serie='{$this->ref_ref_cod_serie}' AND cod_matricula = ref_cod_matricula AND ref_cod_turma = '$this->ref_cod_turma'");

    while ($db->ProximoRegistro()) {
      list($cod_matricula, $ref_cod_aluno, $ref_cod_serie) = $db->Tupla();

      $db2->Consulta("UPDATE pmieducar.matricula SET ultima_matricula = '0'
        WHERE cod_matricula = '$cod_matricula'");

      $db2->Consulta("
        INSERT INTO pmieducar.matricula
          (ref_ref_cod_escola, ref_ref_cod_serie, ref_usuario_cad, ref_cod_aluno, aprovado, data_cadastro, ano, ref_cod_curso, ultima_matricula)
        VALUES
          ('{$this->ref_cod_escola}', '$ref_cod_serie', '{$this->pessoa_logada}', '$ref_cod_aluno', '3', 'NOW()', '$ano', '{$this->ref_cod_curso}', '1')
      ");
    }

    $this->mensagem = "Rematr�cula efetuada com sucesso!";
    return TRUE;
  }

  function Editar() {
    return TRUE;
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do �  p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();
?>
<script type="text/javascript">
document.getElementById('ref_cod_escola').onchange = function() {
  getEscolaCurso();
}

document.getElementById('ref_cod_curso').onchange = function() {
  getEscolaCursoSerie();
}

document.getElementById('ref_ref_cod_serie').onchange = function() {
  getTurma();
}
</script>