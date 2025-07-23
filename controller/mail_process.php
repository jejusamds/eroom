<?php
// controller/mail_process.php
// HTTP GET 호출 방식으로 메일 전송 워커 (replyTo 제거)

error_reporting(E_ALL);
ini_set('display_errors', 1);

$debugLog = __DIR__ . '/debug_exec.log';
$mailLog  = __DIR__ . '/mail_worker.log';

function writeDebug($msg) {
    global $debugLog;
    file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

function writeMailLog($msg) {
    global $mailLog;
    file_put_contents($mailLog, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

writeDebug('HTTP mail_process start');

// 1) GET 파라미터 수신 (replyTo 제거됨)
$toEmail      = isset($_GET['toEmail'])    ? trim($_GET['toEmail'])    : '';
$toName       = isset($_GET['toName'])     ? trim($_GET['toName'])     : '';
$mailSub      = isset($_GET['mailSub'])    ? trim($_GET['mailSub'])    : '';
$mailBody     = isset($_GET['mailBody'])   ? $_GET['mailBody']         : '';
$attachmentId = isset($_GET['attachment']) ? trim($_GET['attachment']) : '';

writeDebug("Params: toEmail={$toEmail}, toName={$toName}, attachment={$attachmentId}");

// 2) 첨부 파일 경로 설정
$attachmentPath = '';
if ($attachmentId !== '') {
    $path = $_SERVER['DOCUMENT_ROOT'] . '/userfiles/form_attachments/' . basename($attachmentId);
    if (file_exists($path)) {
        $attachmentPath = $path;
        writeDebug("Attachment found: {$path}");
    } else {
        writeDebug("Attachment missing at: {$path}");
    }
}

// 3) PHPMailer 로드
require_once __DIR__ . '/../phpmailer/Exception.php';
require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * SMTP 메일 전송 (Gmail 예시)
 */
function sendMail($toEmail, $toName, $subject, $htmlBody, $attachment = '') {
    writeDebug('sendMail start');
    // 받는 사람 이메일 유효성 검사
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        writeMailLog("Invalid toEmail: {$toEmail}");
        return false;
    }
    // DB에서 SMTP 계정 정보 조회
    $pdo = new PDO(
        'mysql:host=db.kctrack1.gabia.io;dbname=dbkctrack1;charset=utf8mb4',
        'kctrack1', 'rhksfleogod04!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $row = $pdo->query("SELECT g_user, g_app_password FROM df_site_siteinfo LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        writeMailLog('SMTP credentials not found');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $row['g_user'];
        $mail->Password   = $row['g_app_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($row['g_user'], '웹사이트 문의');
        $mail->addAddress($toEmail, $toName);

        if ($attachment && file_exists($attachment)) {
            $mail->addAttachment($attachment);
            writeMailLog("Attachment added: {$attachment}");
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        writeMailLog("Mail sent to {$toEmail}");
        return true;
    } catch (Exception $e) {
        writeMailLog("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// 4) 메일 전송 실행
$result = sendMail($toEmail, $toName, $mailSub, $mailBody, $attachmentPath);
writeMailLog('sendMail result: ' . ($result ? 'success' : 'fail'));

exit;
