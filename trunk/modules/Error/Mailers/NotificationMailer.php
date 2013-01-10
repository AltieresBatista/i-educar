<?php

/**
 * i-Educar - Sistema de gestí£o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí­
 *           <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; voc� pode redistribuí-lo e/ou modific�-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licença, como (a seu critério)
 * qualquer vers�o posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas D'Avila <lucasdavila@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Mailer
 * @subpackage  Modules
 * @since     Arquivo disponí­vel desde a vers�o ?
 * @version   $Id$
 */

require_once 'lib/Portabilis/Mailer.php';
require_once 'lib/Portabilis/String/Utils.php';

class NotificationMailer extends Portabilis_Mailer
{
  static function unexpectedDataBaseError($appError, $pgError, $sql) {
    $lastError = error_get_last();

    if (self::canSendEmail()) {
      $to      = self::notificationEmail();
      $subject = "[Erro inesperado bd] i-Educar - " . self::host();
      $message = "Ol�!\n\n"                                                             .
                 "Ocorreu um erro inesperado no banco de dados, detalhes abaixo:\n\n"   .
                 "  ERRO APP: ' . $appError\n"                                          .
                 "  ERRO PHP: ' . {$lastError['message']}\n"                            .
                 "  ERRO POSTGRES: $pgError\n"                                          .
                 "  LINHA {$lastError['line']} em {$lastError['file']}\n"               .
                 "  SQL: $sql"                                                          .
                 "\n\n-\n\n"                                                            .
                 "Voc� recebeu este email pois seu email foi configurado para receber " .
                 "notifica��es de erros.";

      // only send email, if a notification email was set.
      return ($to ? self::mail($to, $subject, $message) : false);
    }
  }

  static function unexpectedError($appError) {
    $lastError = error_get_last();

    if (self::canSendEmail()) {
      $to      = self::notificationEmail();
      $subject = "[Erro inesperado] i-Educar - " . self::host();
      $message = "Ol�!\n\n"                                                             .
                 "Ocorreu um erro inesperado, detalhes abaixo:\n\n"   .
                 "  ERRO APP: ' . $appError\n"                                          .
                 "  ERRO PHP: ' . {$lastError['message']}\n"                            .
                 "  LINHA {$lastError['line']} em {$lastError['file']}\n"               .
                 "\n\n-\n\n"                                                            .
                 "Voc� recebeu este email pois seu email foi configurado para receber " .
                 "notifica��es de erros.";

      // only send email, if a notification email was set.
      return ($to ? self::mail($to, $subject, $message) : false);
    }
  }

  // common error mailer methods

  protected static function canSendEmail() {
    return $GLOBALS['coreExt']['Config']->modules->error->send_notification_email == true;
  }

  protected static function notificationEmail() {
  $email = $GLOBALS['coreExt']['Config']->modules->error->notification_email;

    if (! is_string($email)) {
      error_log("N�o foi definido um email para receber detalhes dos erros, por favor adicione a op��o " .
                "'modules.error_notification.email = email@dominio.com' ao arquivo ini de configura��o.");

      return false;
    }

    return $email;
  }
}