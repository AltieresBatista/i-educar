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

require_once 'clsConfigItajai.inc.php';
require_once 'include/clsCronometro.inc.php';
require_once 'include/clsEmail.inc.php';


/**
 * clsBancoSQL_ class.
 *
 * @author   Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @license  http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package  Core
 * @since    Classe dispon�vel desde a vers�o 1.0.0
 * @version  $Id$
 */
class clsBancoSQL_ {

  protected $strHost       = NULL;     // Nome ou endere�o IP do servidor do banco de dados
  protected $strBanco      = NULL;     // Nome do banco de dados
  protected $strUsuario    = NULL;     // Usu�rio devidamente autorizado a acessar o banco
  protected $strSenha      = NULL;     // Senha do usu�rio do banco
  protected $strPort       = NULL;     // Porta do servidor de banco de dados

  public $bLink_ID         = 0;        // Identificador da conex�o
  public $bConsulta_ID     = 0;        // Identificador do resultado da consulta
  public $arrayStrRegistro = array();  // Tupla resultante de uma consulta
  public $iLinha           = 0;        // Ponteiro interno para a tupla atual da consulta

  public $bErro_no         = 0;        // Se ocorreu erro na consulta, retorna FALSE
  public $strErro          = '';       // Frase de descri��o do erro retornado
  public $bDepurar         = FALSE;    // Ativa ou desativa fun��es de depura��o

  public $bAuto_Limpa      = FALSE;    // '1' para limpar o resultado assim que chegar ao �ltimo registro

  public $strStringSQL     = '';

  /*protected*/var $transactionBlock = FALSE;
  /*protected*/var $savePoints       = array();
  /*protected*/var $showReportarErro = TRUE;

  /*public*/   var $executandoEcho   = FALSE;


  /*
   * Setters
   */
  public function setHost($v) {
    $this->strHost = (string) $v;
  }

  public function setDbname($v) {
    $this->strBanco = (string) $v;
  }

  public function setUser($v) {
    $this->strUsuario = (string) $v;
  }

  public function setPassword($v) {
    $this->strSenha = (string) $v;
  }

  public function setPort($v) {
    $this->strPort = (string) $v;
  }


  /*
   * Getters
   */
  public function getHost() {
    return $this->strHost;
  }

  public function getDbname() {
    return $this->strBanco;
  }

  public function getUser() {
    return $this->strUsuario;
  }

  public function getPassword() {
    return $this->strSenha;
  }

  public function getPort() {
    return $this->strPort;
  }

  public function getFraseConexao() {
    return $this->strFraseConexao;
  }



  /**
   * Constr�i a string de conex�o de banco de dados
   */
  public function FraseConexao() {
    $this->strFraseConexao = "";
    if (!empty($this->strHost)) {
      $this->strFraseConexao .= "host={$this->strHost}";
    }
    if (!empty($this->strBanco)) {
      $this->strFraseConexao .= " dbname={$this->strBanco}";
    }
    if (!empty($this->strUsuario)) {
      $this->strFraseConexao .= " user={$this->strUsuario}";
    }
    if (!empty($this->strSenha)) {
      $this->strFraseConexao .= " password={$this->strSenha}";
    }
    if (!is_null($this->strPort)) {
      $this->strFraseConexao .= " port={$this->strPort}";
    }
  }



  /**
   * Conecta com o banco de dados
   *
   * Verifica se o link est� inativo e conecta. Se a conex�o n�o obtiver
   * sucesso, interrompe o script
   */
  public function Conecta() {
    // Verifica se o link de conex�o est� inativo e conecta
    if (0 == $this->bLink_ID) {
      $this->FraseConexao();

      if ($this->bDepurar) {
        printf("<br>Depurar: Frase de Conex&atilde;o : %s<br>\n", $this->strFraseConexao);
      }

      $this->bLink_ID = pg_connect($this->strFraseConexao);

      if (!$this->bLink_ID) {
        $this->Interrompe("Link inv&aacute;lido, conex&atilde;o falhou!");
      }
    }
  }



	/*
	Executa uma instru&ccedil;&atilde;o SQL e retorna um identificador para o resultado
	Se a frase SQL for inv&aacute;lida ele interrompe a execu&ccedil;&atilde;o do script.
	*/

	/*private*/ function Consulta( $consulta )
	{
		$cronometro = new clsCronometro();
		$cronometro->marca( "inicio" );
		/* Testa se a consulta � vazia */
		if (empty($consulta))
		{
			return false;
		}
		else
		{
			$this->strStringSQL = $consulta;
		}

		$this->strStringSQLOriginal = $this->strStringSQL;

		$this->Conecta();

		/* Funcao para depuracao */
		if ($this->bDepurar)
		{
			printf("<br>Depurar: Frase de Consulta = %s<br>\n", $this->strStringSQL);
		}

		/*
			Alteracoes de padrao MySQL para PostgreSQL
		*/

		// Altera o Limit
		$this->strStringSQL = eregi_replace( "LIMIT[ ]{0,3}([0-9]+)[ ]{0,3},[ ]{0,3}([0-9]+)", "LIMIT \\2 OFFSET \\1", $this->strStringSQL );
		// Altera selects com YEAR( campo ) ou MONTH ou DAY
		$this->strStringSQL = eregi_replace( "(YEAR|MONTH|DAY)[(][ ]{0,3}(([a-z]|_|[0-9])+)[ ]{0,3}[)]", "EXTRACT( \\1 FROM \\2 )", $this->strStringSQL );
		// Remove os ORDER BY das querys COUNT()
		//$this->strStringSQL = eregi_replace( "(SELECT.*COUNT[(][0|\*][)].*FROM.*)(ORDER BY.*)", "\\1", $this->strStringSQL );
		// Altera os LIKE para ILIKE (ignore case)
		$this->strStringSQL = eregi_replace( " LIKE ", " ILIKE ", $this->strStringSQL );

		$this->strStringSQL = eregi_replace( "([a-z_0-9.]+) +ILIKE +'([^']+)'", "to_ascii(\\1) ILIKE to_ascii('\\2')", $this->strStringSQL );
		$this->strStringSQL = eregi_replace( "fcn_upper_nrm", "to_ascii", $this->strStringSQL );

		$temp = explode( "'", $this->strStringSQL );
		for ( $i = 0; $i < count( $temp ); $i++ )
		{
			// ignora o que esta entre aspas
			if( ! ( $i % 2 ) )
			{
				// fora das aspas, verifica se h� algo errado no SQL
				if( eregi( "(--|#|/\*)", $temp[$i] ) )
				{
					$erroMsg = "Protecao contra injection: " . date( "Y-m-d H:i:s" );
					echo "<!-- {$this->strStringSQL} -->";
					$this->Interrompe( $erroMsg );
				}
			}
		}

		/* Executa a Consulta */
		if ($this->executandoEcho)
		{
			echo $this->strStringSQL."\n<br>";
			//return true;
		}

		$this->bConsulta_ID = pg_query($this->bLink_ID, $this->strStringSQL);// or $this->Interrompe( "Ocorreu um erro ao consultar o banco de dados.", true );
		$this->strErro = pg_result_error($this->bConsulta_ID);
		$this->bErro_no = ($this->strErro == "") ? false : true;


		$this->iLinha   = 0;

		/* Testa se a consulta foi v&aacute;lida */
		if ( !$this->bConsulta_ID )
		{
			$erroMsg = "SQL invalido: {$this->strStringSQL}<br>\n";
			die( $erroMsg );
			if( $this->transactionBlock )
			{
				/*
				if( count( $this->savePoints ) )
				{
					$this->rollBack();
					$erroMsg .= "<!-- retornando ao ultimo SavePoint -->\n";
				}
				*/
			}
			$this->Interrompe( $erroMsg );
			return false;
			//$this->Interrompe("SQL invalido: ".$this->strStringSQL);
		}

		$cronometro->marca( "fim" );
		$tempoTotal = $cronometro->getTempoTotal();

		$objConfig = new clsConfig();
		if( $tempoTotal > $objConfig->arrayConfig["intSegundosQuerySQL"] )
		{
			$conteudo = "<table border=\"1\" width=\"100%\">";
			$conteudo .= "<tr><td><b>Data</b>:</td><td>" . date( "d/m/Y H:i:s", time() ) . "</td></tr>";
			$conteudo .= "<tr><td><b>Script</b>:</td><td>{$_SERVER["PHP_SELF"]}</td></tr>";
			$conteudo .= "<tr><td><b>Tempo da query</b>:</td><td>{$tempoTotal} segundos</td></tr>";
			$conteudo .= "<tr><td><b>Tempo max permitido</b>:</td><td>{$objConfig->arrayConfig["intSegundosQuerySQL"]} segundos</td></tr>";
			$conteudo .= "<tr><td><b>SQL Query Original</b>:</td><td>{$this->strStringSQLOriginal}</td></tr>";
			$conteudo .= "<tr><td><b>SQL Query Executado</b>:</td><td>{$this->strStringSQL}</td></tr>";
			$conteudo .= "<tr><td><b>URL get</b>:</td><td>{$_SERVER['QUERY_STRING']}</td></tr>";
			$conteudo .= "<tr><td><b>Metodo</b>:</td><td>{$_SERVER["REQUEST_METHOD"]}</td></tr>";
			if ( $_SERVER["REQUEST_METHOD"] == "POST" )
			{
				$conteudo .= "<tr><td><b>POST vars</b>:</td><td>";
				foreach ( $_POST AS $var => $val )
				{
					$conteudo .= "{$var} => {$val}<br>";
				}
				$conteudo .= "</td></tr>";
			} else if ( $_SERVER["REQUEST_METHOD"] == "GET" )
			{
				$conteudo .= "<tr><td><b>GET vars</b>:</td><td>";
				foreach ( $_GET AS $var => $val )
				{
					$conteudo .= "{$var} => {$val}<br>";
				}
				$conteudo .= "</td></tr>";
			}
			if( $_SERVER["HTTP_REFERER"] )
			{
				$conteudo .= "<tr><td><b>Referrer</b>:</td><td>{$_SERVER["HTTP_REFERER"]}</td></tr>";
			}
			$conteudo .= "</table>";

			$objEmail = new clsEmail( $objConfig->arrayConfig['ArrStrEmailsAdministradores'], "[INTRANET - PMI] Desempenho de query", $conteudo );
			$objEmail->envia();
		}

		return $this->bConsulta_ID;
	}

	/**
	 * Inicia umbloco de transacao (transaction block)
	 *
	 * @return bool
	 */
	function begin()
	{
		if( ! $this->transactionBlock )
		{
			$this->Consulta( "BEGIN" );
			$this->transactionBlock = true;
			// reseta os savePoints
			$this->savePoints = array();
			return true;
		}
		// tratamento de erro informando que ja esta dentro de um transaction block
		return false;
	}

	/**
	 * Processa umbloco de transacao (transaction block)
	 *
	  * @return bool
	 */
	function commit()
	{
		if( $this->transactionBlock )
		{
			$this->Consulta( "COMMIT" );
			$this->transactionBlock = false;
			// reseta os savePoints
			$this->savePoints = array();
			return true;
		}
		// tratamento de erro informando que nao esta dentro de um transaction block
		return false;
	}

	/**
	 * Cria um novo savePoint
	 *
	  * @param string  $strSavePointName [Opcional] nome do savePoint a ser criado
	 *  @return bool
	 */
	function savePoint( $strSavePointName=false )
	{
		if( $this->transactionBlock )
		{
			if( $strSavePointName )
			{
				foreach ( $this->savePoints AS $key => $nome )
				{
					// nao podemos ter dois savepoints com o mesmo nome
					if( $nome == $strSavePointName )
					{
						return false;
					}
				}
				$this->savePoints[] = $strSavePointName;
				$this->Consulta( "SAVEPOINT $strSavePointName" );
				return true;
			}
			else
			{
				$nome = "save_" . count( $this->savePoints );
				$this->savePoints[] = $nome;
				$this->Consulta( "SAVEPOINT $nome" );
				return true;
			}
		}
		return false;
	}

	/**
	 * Cria um novo savePoint
	 *
	  * @param string  $strSavePointName nome do savePoint onde se deseja voltar, se nao for definido volta ao ultimo savepoint criado
	 *  @return bool
	 */
	function rollBack( $strSavePointName=false )
	{
		if( $this->transactionBlock )
		{
			if( count( $this->savePoints ) )
			{
				if( $strSavePointName )
				{
					foreach ( $this->savePoints AS $key => $nome )
					{
						// se achar eh porque tem o savePoint
						if( $nome == $strSavePointName )
						{
							$this->savePoints = array_slice( $this->savePoints, 0, $key );
							$this->Consulta( "ROLLBACK TO {$strSavePointName}" );
							return true;
						}
					}
				}
				else
				{
					// se nao tem um nome definido ele volta ao ultimo savePoint
					$lastPos = count( $this->savePoints ) - 1;
					$strSavePointName = $this->savePoints[$lastPos];
					$this->savePoints = array_slice( $this->savePoints, 0, ( $lastPos - 1 ) );
					$this->Consulta( "ROLLBACK TO {$strSavePointName}" );
				}
			}
		}
		return false;
	}

	/*
	* metodo que retorna o valor do ultimo ID inserido em uma query
	*/
	/*private*/ function InsertId( $str_sequencia = false )
	{
		// return pg_last_oid($this->bConsulta_ID);
		if( $str_sequencia )
		{
			$this->Consulta( "SELECT currval('{$str_sequencia}'::text)" );
			$this->ProximoRegistro();
			list( $valor ) = $this->Tupla();
			return $valor;
		}
		return false;
	}

	function UltimoID( $str_sequencia = false )
	{
		return $this->InsertId( $str_sequencia );
	}

	/*
	Avan&ccedil;a um resgistro no resultado da consulta corrente,
	retorna Falso se chegar ao fim do resultado.
	� necess&aacute;rio se fazer uma chamada a essa fun&ccedil;&atilde;o
	antes de se acessar o primeiro registro do resultado,
	se o resultado for vazio, ele reotrna Falso,
	sen&atilde;o posiciona para leitura o primeiro registro.
	*/
	/*public*/ function ProximoRegistro()
	{
		/*
		Carrega Registro com o resultado da Tupla indexada por Linha
		e incrementa Linha
		*/
		$this->arrayStrRegistro = @pg_fetch_array($this->bConsulta_ID);

		/* Testa por erros */
		$this->strErro = pg_result_error($this->bConsulta_ID);
		$this->bErro_no = ($this->strErro == "")? false : true ;

		/*
		Testa se Registro est&aacute; vazio,
		sinal que se chegou ao fim da Consulta.
		Verifica ent&atilde;o de Auto_Limpa � Verdade,
		se for, limpa o resultado da consulta.
		*/
		$stat = is_array($this->arrayStrRegistro);
		if ($this->bDepurar && $stat)
			printf("<br>Depurar: Registro : %s <br>\n", implode($this->arrayStrRegistro));
		if (!$stat && $this->bAuto_Limpa)
		{
			$this->Libera();
		}
		return $stat;
	}

	/*public*/ function Procura($Pos)
	{
		$this->iLinha = $Pos;
	}

	/*
	Retorna as linhas afetadas em Consultas com instru&ccedil;&otilde;es
	INSERT, UPDATE e DELETE
	*/
	/*public*/ function Linhas_Afetadas()
	{
		return @pg_affected_rows($this->bConsulta_ID);
	}

	/*
	Retorna as linhas afetadas em Consultas com instru&ccedil;&otilde;es
	INSERT, UPDATE e DELETE
	*/
	/*public*/ function Libera()
	{
		pg_free_result($this->bConsulta_ID);
		$this->bConsulta_ID = 0;
		$this->strStringSQL = "";
	}

	/**
	 * Alias para numLinhas mantido para compatibilidade com vers�es anteriores
	 */
	/*public*/ function Num_Linhas()
	{
		return $this->numLinhas();
	}

	/**
	 * Retorna o n�mero de linhas do resultado de um Consulta
	 */
	/*public*/ function numLinhas()
	{
		return pg_num_rows($this->bConsulta_ID);
	}

	/**
	 * Alias para numCampos mantido para compatibilidade com vers�es anteriores
	 */
	/*public*/ function Num_Campos()
	{
		return $this->numCampos();
	}

	/**
	 * Retorna o n�mero de campo do resultado de um Consulta
	 */
	/*public*/ function numCampos()
	{
		return pg_num_fields($this->bConsulta_ID);
	}

	/*
	Retorna o valor do campo Nome de Consulta	com instru&ccedil;&atilde;o SELECT
	*/
	/*public*/ function Campo($Nome)
	{
		return $this->arrayStrRegistro[$Nome];
	}

	/*
	Retorna a tupla atual da Consulta atual
	*/
	/*public*/ function Tupla()
	{
		return $this->arrayStrRegistro;
	}

	/*
	Retorna um �nico campo de uma pesquisa de uma tupla
	*/
	/*public*/ function UnicoCampo($consulta)
	{
		$this->Consulta($consulta);
		if( $this->ProximoRegistro() )
		{
			list ($campo) = $this->Tupla();
			$this->Libera();
			return $campo;
		}
		return false;
	}
	/*
	Retorna um �nico campo de uma pesquisa de uma tupla
	*/
	/*public*/ function CampoUnico($consulta)
	{
		return $this->UnicoCampo( $consulta );
	}

	/*
	Retorna uma �nica tupla de uma pesquisa
	*/
	/*public*/ function UnicaTupla($consulta)
	{
		$this->Consulta($consulta);
		$this->ProximoRegistro();
		$tupla = $this->Tupla();
		$this->Libera();
		return $tupla;
	}

	/*
	Retorna uma �nica tupla de uma pesquisa
	*/
	/*public*/ function TuplaUnica($consulta)
	{
		return $this->UnicaTupla;
	}

	/*
	Interrompe a execu&ccedil;&atilde;o do Programa e Imprime mensagens de Erro
	*/
	/*private*/ function Interrompe($msg, $getError = false )
	{
		if( $getError )
		{
			$this->strErro = pg_result_error($this->bConsulta_ID);
			$this->bErro_no = ($this->strErro == "") ? false : true;
		}
		/*
		printf("</td></tr></table><br><b>arquivo {$_SERVER['SCRIPT_FILENAME']}<br><br>Erro de Banco de Dados:</b> %s<br>\n", $msg);
		printf("<b>Erro do PgSQL </b>: %s (%s)<br>\n", $this->bErro_no, $this->strErro);
		die("Sess&atilde;o Interrompida.");
		*/
		$erro1 = substr(md5('1erro'), 0, 10);
		$erro2 = substr(md5('2erro'), 0, 10);

		function show($data, $func = "var_dump")
		{
			ob_start();
			$func( $data );
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
		@session_start();
		$_SESSION['vars_session'] = show( $_SESSION );
		$_SESSION['vars_post'] = show( $_POST );
		$_SESSION['vars_get'] = show( $_GET );
		$_SESSION['vars_cookie'] = show( $_COOKIE );
		$_SESSION['vars_erro1'] = $msg;
		$_SESSION['vars_erro2'] = $this->strErro;
		$_SESSION['vars_server'] = show( $_SERVER );
		$id_pessoa = $_SESSION['id_pessoa'];
		session_write_close();
		if( $this->showReportarErro )
		{
			$array_idpes_erro_total = array();
			$array_idpes_erro_total[4910] = true;
			$array_idpes_erro_total[2151] = true;
			$array_idpes_erro_total[8194] = true;
			$array_idpes_erro_total[7470] = true;
			$array_idpes_erro_total[4637] = true;
			$array_idpes_erro_total[4702] = true;
			$array_idpes_erro_total[1801] = true;

			if( ! $array_idpes_erro_total[$id_pessoa] )
			{
				die( "<script>document.location.href = 'erro_banco.php';</script>" );
			}
			else
			{
				printf("</td></tr></table><b>Erro de Banco de Dados:</b> %s<br><br>\n", $msg);
				printf("<b>SQL:</b> %s<br><br>\n", $this->strStringSQL );
				printf("<b>Erro do PgSQL </b>: %s (%s)<br><br>\n", $this->bErro_no, $this->strErro);
				die("Sess&atilde;o Interrompida.");
			}
		}
		else
		{
			//echo $msg . "\n";
			die( $this->strErro . "\n" );
		}
	}
}
?>
