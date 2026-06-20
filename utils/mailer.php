<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private PHPMailer $mail;
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['GMAIL_USER'];
        $this->mail->Password   = $_ENV['GMAIL_APP_PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'],
            $_ENV['MAIL_FROM_NAME']
        );
    }

    public function sendWelcome(string $toEmail, string $toName, string $uuid): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Activation de compte';
            $this->mail->Body    = $this->welcomeTemplate($toName, $uuid);
            $this->mail->AltBody = "";
            return $this->mail->send();
        } catch (Exception $e) {
            error_log('Erreur email bienvenue : ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    public function sendNewsletter(
        array  $recipients,
        string $monsterCode,
        string $monsterImageUrl,
        int    $batchSize = 80
    ): int {
        $chunks = array_chunk($recipients, $batchSize);
        $totalSent = 0;
        $monsterTitle = strtoupper(str_replace('_', ' ', $monsterCode));

        foreach ($chunks as $batch) {
            try {
                $this->mail->clearAddresses();
                $this->mail->clearBCCs();
                $this->mail->addAddress($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);

                foreach ($batch as $recipient) {
                    $this->mail->addBCC($recipient['mail'], $recipient['pseudo'] ?? '');
                }

                $this->mail->isHTML(true);
                $this->mail->Subject = "Nouvelle monster - {$monsterTitle}";
                $this->mail->Body    = $this->newsletterTemplate($monsterTitle, $monsterCode, $monsterImageUrl);

                $this->mail->send();
                $totalSent += count($batch);

                sleep(2);

            } catch (Exception $e) {
                error_log('Erreur newsletter (batch) : ' . $this->mail->ErrorInfo);
            }
        }

        return $totalSent;
    }

    private function welcomeTemplate(string $name, string $uuid): string {
      return "
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset='UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <meta name='supported-color-schemes' content='dark'>
        </head>
        <body style='margin:0;padding:0;background:transparent;font-family:Arial,sans-serif;' bgcolor='transparent'>
          <table width='100%' cellpadding='0' cellspacing='0' style='background:transparent;padding:40px 0;' bgcolor='transparent'>
            <tr>
              <td align='center'>
                <table width='600' height='700' cellpadding='0' cellspacing='0' bgcolor='#111111'
                  style='background:#111111;border:2px solid #ffffff;border-radius:20px;
                  box-shadow:0 0 10px #ffffff,0 0 25px rgba(255,255,255,.6);'>
                  <tr>
                    <td align='center' style='padding:40px 30px 20px;'>
                      <h1 style='
                        color:#ffffff;
                        margin:0;
                        font-size:42px;
                        letter-spacing:3px;
                        text-shadow:
                          0 0 5px #ffffff,
                          0 0 10px #ffffff,
                          0 0 20px #ffffff;'>
                        MONSTERS
                      </h1>
                      <div style='
                        width:120px;
                        height:3px;
                        background:#ffffff;
                        margin:20px auto;
                        box-shadow:0 0 10px #ffffff;'>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td style='padding:0 40px 20px;color:#ffffff;'>
                      <h2 style='
                        font-size:28px;
                        margin-bottom:20px;
                        text-shadow:0 0 10px rgba(255,255,255,.8);'>
                        Bienvenue, $name
                      </h2>
                      <p style='
                        color:#d1d5db;
                        font-size:16px;
                        line-height:1.8;'>
                        Ton compte vient d'être créé avec succès.
                        Rejoins la communauté et découvre toutes les saveurs,
                        collections et classements Monster.
                      </p>
                    </td>
                  </tr>
                  <tr>
                    <td align='center' style='padding:20px 40px 40px;'>
                      <a href='https://monsters.addrien.fr/login?verify=$uuid'
                        style='
                          display:inline-block;
                          padding:16px 36px;
                          color:#000000;
                          background:#ffffff;
                          font-weight:bold;
                          font-size:16px;
                          text-decoration:none;
                          border-radius:999px;
                          box-shadow:
                            0 0 10px #ffffff,
                            0 0 25px rgba(255,255,255,.8);'>
                        ACTIVER MON COMPTE
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td align='center'
                      style='padding:25px;color:#9ca3af;font-size:12px;border-top:1px solid #333;'>
                      Monsters Energy Collection<br>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </body>
      </html>";
    }

    private function newsletterTemplate(string $title, string $code, string $imageUrl): string {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeCode  = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $safeImage = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');

        return "
        <!DOCTYPE html>
        <html>
          <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <meta name='supported-color-schemes' content='dark'>
          </head>
          <body style='margin:0;padding:0;background:transparent;font-family:Arial,sans-serif;' bgcolor='transparent'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background:transparent;padding:40px 0;' bgcolor='transparent'>
              <tr>
                <td align='center'>
                  <table width='600' cellpadding='0' cellspacing='0' bgcolor='#111111'
                    style='background:#111111;border:2px solid #ffffff;border-radius:20px;
                    box-shadow:0 0 10px #ffffff,0 0 25px rgba(255,255,255,.6);overflow:hidden;'>
                    <tr>
                      <td align='center' style='padding:40px 30px 20px;'>
                        <h1 style='
                          color:#ffffff;
                          margin:0;
                          font-size:42px;
                          letter-spacing:3px;
                          text-shadow:
                            0 0 5px #ffffff,
                            0 0 10px #ffffff,
                            0 0 20px #ffffff;'>
                          MONSTERS
                        </h1>
                        <div style='
                          width:120px;
                          height:3px;
                          background:#ffffff;
                          margin:20px auto;
                          box-shadow:0 0 10px #ffffff;'>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td style='padding:0 40px 10px;color:#ffffff;'>
                        <p style='
                          color:#9ca3af;
                          font-size:12px;
                          text-transform:uppercase;
                          letter-spacing:2px;
                          margin:0 0 8px;
                          text-align:center;'>
                          Nouvelle monster disponible
                        </p>
                      </td>
                    </tr>
                    <tr>
                      <td style='padding:0 40px;'>
                        <img src='{$safeImage}' alt='{$safeTitle}'
                          style='
                            height:100%;
                            max-height:280px;
                            min-width:100%;
                            object-fit:contain;
                            display:block;
                            border-radius:12px;' />
                      </td>
                    </tr>
                    <tr>
                      <td align='center' style='padding:25px 40px 10px;color:#ffffff;'>
                        <h2 style='
                          font-size:28px;
                          margin:0;
                          text-shadow:0 0 10px rgba(255,255,255,.8);'>
                          {$safeTitle}
                        </h2>
                      </td>
                    </tr>
                    <tr>
                      <td align='center' style='padding:20px 40px 40px;'>
                        <a href='https://monsters.addrien.fr/monster?name={$safeCode}'
                          style='
                            display:inline-block;
                            padding:16px 36px;
                            color:#000000;
                            background:#ffffff;
                            font-weight:bold;
                            font-size:16px;
                            text-decoration:none;
                            border-radius:999px;
                            box-shadow:
                              0 0 10px #ffffff,
                              0 0 25px rgba(255,255,255,.8);'>
                          DÉCOUVRIR
                        </a>
                      </td>
                    </tr>
                    <tr>
                      <td align='center'
                        style='padding:25px;color:#9ca3af;font-size:12px;border-top:1px solid #333;'>
                        Monsters Energy Collection<br>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </body>
        </html>";
    }
}