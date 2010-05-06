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
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'include/relatorio.inc.php';

/**
 * clsIndexBase class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Alunos em 5� Avalia��o');
    $this->processoAp = 807;
    $this->renderMenu = FALSE;
    $this->renderMenuSuspenso = FALSE;
  }
}

/**
 * indice class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsCadastro
{
  var $pessoa_logada;

  var $ref_cod_instituicao;
  var $ref_cod_escola;
  var $ref_cod_serie;
  var $ref_cod_turma;
  var $ref_cod_curso;

  var $ano;

  var $cursos = array();

  var $get_link;

  var $media;
  var $media_exame;

  function renderHTML()
  {
    if($_POST){
      foreach ($_POST as $key => $value) {
        $this->$key = $value;
      }
    }

    if ($this->ref_ref_cod_serie) {
      $this->ref_cod_serie = $this->ref_ref_cod_serie;
    }

    $fonte    = 'arial';
    $corTexto = '#000000';

    if (is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_curso) &&
        is_numeric($this->ref_cod_serie) && is_numeric($this->ref_cod_turma) &&
        is_numeric($this->ano)
    ) {
      $obj_ref_cod_curso = new clsPmieducarCurso($this->ref_cod_curso);
      $det_ref_cod_curso = $obj_ref_cod_curso->detalhe();

      $nm_curso           = $det_ref_cod_curso['nm_curso'];
      $padrao_ano_escolar = $det_ref_cod_curso['padrao_ano_escolar'];

      if ($padrao_ano_escolar) {
        $obj_ano_letivo = new clsPmieducarEscolaAnoLetivo();
        $lst_ano_letivo = $obj_ano_letivo->lista( $this->ref_cod_escola,$this->ano,null,null,null,null,null,null,null,1 );

        if (is_array($lst_ano_letivo)) {
          $det_ano_letivo = array_shift($lst_ano_letivo);
          $ano_letivo     = $det_ano_letivo['ano'];

          $obj_ano_letivo_modulo = new clsPmieducarAnoLetivoModulo();
          $lst_ano_letivo_modulo = $obj_ano_letivo_modulo->lista($ano_letivo, $this->ref_cod_escola);

          if (is_array($lst_ano_letivo_modulo)) {
            $qtd_modulos = count($lst_ano_letivo_modulo);
          }
        }
        else {
          echo '
            <script>
              alert("Escola n�o possui calend�rio definido para este ano");
              window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));
            </script>';

          return TRUE;
        }
      }
      else {
        $obj_turma_modulo = new clsPmieducarTurmaModulo();
        $lst_turma_modulo = $obj_turma_modulo->lista($registro['ref_cod_turma']);

        if (is_array($lst_turma_modulo)) {
          $qtd_modulos = count($lst_turma_modulo);
        }
      }

      if ($this->ano == date('Y')) {
        $sql = "
          SELECT
            m.cod_matricula,
            (
            SELECT
              nome
            FROM
              pmieducar.aluno al,
              cadastro.pessoa
            WHERE
              al.cod_aluno = m.ref_cod_aluno
              AND al.ref_idpes = pessoa.idpes
            ) AS nome
          FROM
            pmieducar.matricula m,
            pmieducar.matricula_turma mt
          WHERE
            mt.ref_cod_turma = {$this->ref_cod_turma}
            AND mt.ref_cod_matricula = m.cod_matricula AND m.aprovado = 3
            AND mt.ativo = 1 AND m.ativo = 1
            AND m.modulo > {$qtd_modulos}
            AND m.ano = {$this->ano}
          ORDER BY
            nome";
      }
      else {
        $sql = "
          SELECT
            m.cod_matricula,
            (SELECT
               nome
             FROM
               pmieducar.aluno al,
               cadastro.pessoa
             WHERE
              al.cod_aluno = m.ref_cod_aluno
              AND al.ref_idpes = pessoa.idpes
            ) AS nome
          FROM
            pmieducar.matricula m,
            pmieducar.matricula_turma mt
          WHERE
            mt.ref_cod_turma = {$this->ref_cod_turma}
            AND mt.ref_cod_matricula = m.cod_matricula
            AND m.aprovado IN (1, 2, 3)
            AND mt.ativo = 1 AND m.ativo = 1
            AND m.modulo > {$qtd_modulos}
            AND m.ano = {$this->ano}
          ORDER BY
            nome";
      }

      $db = new clsBanco();
      $db->Consulta($sql);

      if ($db->Num_Linhas()) {
        $alunos = array();

        // Disciplinas da escola-s�rie
        $obj_disciplinas = new clsPmieducarEscolaSerieDisciplina();
        $obj_disciplinas->setOrderby('nm_disciplina');
        $obj_disciplinas->setCamposLista('cod_disciplina, nm_disciplina');
        $lst_disciplinas = $obj_disciplinas->lista($this->ref_cod_serie,
          $this->ref_cod_escola, NULL, 1, TRUE);

        // Curso
        $obj_curso = new clsPmieducarCurso($this->ref_cod_curso);
        $obj_curso->setCamposLista('media, media_exame, nm_curso');
        $det_curso = $obj_curso->detalhe();

        $this->media       = $det_curso['media'];
        $this->media_exame = $det_curso['media_exame'];

        // Instancia objeto de relat�rio padr�o
        $relatorio = new relatorios('Rela��o de alunos em 5� avalia��o', 210,
          FALSE, 'Rela��o de alunos em 5� avalia��o', 'A4',
          "{$this->nm_instituicao}\n{$this->nm_escola}\n{$this->nm_curso}\n{$this->nm_serie} -  Turma: $this->nm_turma         " . date("d/m/Y"));

        $relatorio->setMargem(20, 20, 20, 20);

        // Escola
        $obj_escola = new clsPmieducarEscola($this->ref_cod_escola);
        $nm_escola  = $obj_escola->detalhe();
        $nm_escola  = $nm_escola['nome'];
        $nm_curso   = $det_curso['nm_curso'];

        // S�rie
        $obj_serie = new clsPmieducarSerie($this->ref_cod_serie);
        $obj_serie->setCamposLista('nm_serie');
        $det_serie = $obj_serie->detalhe();
        $nm_serie  = $det_serie['nm_serie'];

        // Turma
        $obj_turma = new clsPmieducarTurma($this->ref_cod_turma);
        $obj_turma->setCamposLista('nm_turma');
        $det_turma = $obj_turma->detalhe();
        $nm_turma  = $det_turma['nm_turma'];

        $relatorio->novalinha(array(sprintf('Nome Escola: %s    Ano: %d', $nm_escola, $this->ano)),
          0, 12, TRUE, 'arial', FALSE, '#000000', '#d3d3d3', '#FFFFFF', FALSE, TRUE);

        $relatorio->novalinha(array(sprintf('Curso: %s    Ano/S�rie: %s    Turma: %s    Date: %s', $nm_curso, $nm_serie, $nm_turma, date('d/m/Y'))),
          0, 12, TRUE, 'arial', FALSE, '#000000', '#d3d3d3', '#FFFFFF', FALSE, TRUE);

        $relatorio->novalinha(array('Matr�cula', 'Nome Aluno', 'Disciplinas', 'Pontos', 'Nota 5� Av. Passar'),
          0, 12, TRUE, 'arial', array(50, 200, 150, 50), '#515151', '#d3d3d3', '#FFFFFF', FALSE, TRUE);

        while ($db->ProximoRegistro()) {
          list($cod_matricula, $nome_aluno) = $db->Tupla();

          foreach ($lst_disciplinas as $disciplina) {
            $obj_nota_aluno = new clsPmieducarNotaAluno();
            $obj_nota_aluno->setOrderby('modulo ASC');
            $lst_nota_aluno = $obj_nota_aluno->lista(NULL, NULL, NULL,
              $this->ref_cod_serie, $this->ref_cod_escola, $disciplina['cod_disciplina'],
              $cod_matricula, NULL, NULL, NULL, NULL, NULL, NULL, 1);

            $aluno_notas = array();
            $aluno_notas_normal = array();

            if (is_array($lst_nota_aluno)) {
              $aluno_notas[$disciplina['cod_disciplina']] = 0;

              foreach ($lst_nota_aluno as $nota_aluno) {
                $obj_avaliacao_valores = new clsPmieducarTipoAvaliacaoValores(
                  $nota_aluno['ref_ref_cod_tipo_avaliacao'], $nota_aluno['ref_sequencial']
                );

                $det_avaliacao_valores = $obj_avaliacao_valores->detalhe();

                $aluno_notas[$disciplina['cod_disciplina']] += $det_avaliacao_valores['valor'];
              }

              $aluno_notas_normal[$disciplina['cod_disciplina']] = $aluno_notas[$disciplina['cod_disciplina']];

              $aluno_notas[$disciplina['cod_disciplina']] /= count($lst_nota_aluno);

              $aluno_notas[$disciplina['cod_disciplina']] = sprintf('%01.1f', $aluno_notas[$disciplina['cod_disciplina']]);
            }

            if (is_array($aluno_notas)) {
              foreach ($aluno_notas as $cod_disciplina => $media) {
                if ($media < $this->media && $this->media_exame) {
                  // @todo WTF!??? Que diabos de nota fixa � essa?
                  // F�RMULA: 30 - (SOMA DE PONTOS DOS 4 BIMESTRES) / 2.
                  // Ex: 30 - 23 / 2 = 3,5
                  $nota_necessaria_passar = (30 - $aluno_notas_normal[$cod_disciplina]) / 2;

                  $data = array(
                    $cod_matricula,
                    $nome_aluno,
                    $disciplina['nm_disciplina'],
                    $aluno_notas_normal[$cod_disciplina],
                    $nota_necessaria_passar
                  );

                  $relatorio->novalinha($data, 0, 12, FALSE, 'arial',
                    array(50, 200, 150, 50), '#515151', '#d3d3d3', '#FFFFFF', FALSE, TRUE);
                }
              }
            }
          }
        }

        $this->get_link = $relatorio->fechaPdf();

        echo sprintf('
          <script>
            window.onload=function()
            {
              parent.EscondeDiv("LoadImprimir");
              window.location="download.php?filename=%s"
            }
          </script>', $this->get_link);

        echo sprintf('
          <html>
            <center>
              Se o download n�o iniciar automaticamente <br>
              <a target="blank" href="%s" style="font-size: 16px; color: #000000; text-decoration: underline;">clique aqui!</a><br><br>
              <span style="font-size: 10px;">
                Para visualizar os arquivos PDF, � necess�rio instalar o Adobe Acrobat Reader.<br>
                Clique na Imagem para Baixar o instalador<br><br>
                <a href="http://www.adobe.com.br/products/acrobat/readstep2.html" target="new"><br><img src="imagens/acrobat.gif" width="88" height="31" border="0"></a>
              </span>
            </center>
          </html>', $this->get_link);
      }
      else {
        echo '<script>window.onload=function(){parent.EscondeDiv("LoadImprimir");}</script>';
        echo 'Nenhum aluno est� em exame';
      }
    }
  }

  function Editar()
  {
    return FALSE;
  }

  function Excluir()
  {
    return FALSE;
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