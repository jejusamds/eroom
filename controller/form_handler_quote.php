<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// 필수 값 검증
if (
    !isset(
    $_POST['f_subject'],
    $_POST['f_name_company'],
    $_POST['f_phone'],
    $_POST['f_email'],
    $_POST['f_region'],
    $_POST['f_usage'],
    $_POST['f_message']
)
) {
    echo json_encode(['result' => 'error', 'msg' => '필수 항목이 누락되었습니다.']);
    exit;
}

$subject = addslashes(trim($_POST['f_subject']));
$nameCompany = addslashes(trim($_POST['f_name_company']));
$phone = addslashes(trim($_POST['f_phone']));
$email = addslashes(trim($_POST['f_email']));
$region = addslashes(trim($_POST['f_region']));
$usage = addslashes(trim($_POST['f_usage']));
$message = addslashes(trim($_POST['f_message']));

// 첨부파일 처리 (파일명만 저장)
$attachmentName = '';
if (isset($_FILES['f_attachment']) && $_FILES['f_attachment']['error'] === UPLOAD_ERR_OK) {
    $origName = $_FILES['f_attachment']['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    // 필요하다면 확장자 체크 가능 (예: jpg, png, pdf 등)
    $uniqName = 'quote_' . time() . '.' . $ext;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/userfiles/form_attachments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0707, true);
    }
    $fullPath = $uploadDir . $uniqName;
    if (move_uploaded_file($_FILES['f_attachment']['tmp_name'], $fullPath)) {
        $attachmentName = $uniqName;
    } else {
        // 업로드 실패 시 무시하거나, 필요하면 응답을 에러로 보냄
        $attachmentName = '';
    }
}

include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

$sql = "
    INSERT INTO df_site_form_quote
    (f_subject, f_name_company, f_phone, f_email, f_attachment, f_region, f_usage, f_message, wdate)
    VALUES
    ('{$subject}', '{$nameCompany}', '{$phone}', '{$email}', '{$attachmentName}', '{$region}', '{$usage}', '{$message}', NOW())
";
$db->query($sql);

echo json_encode(['result' => 'ok', 'msg' => '견적문의가 정상 접수되었습니다.', 'redirect' => '/']);
flush();

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

$phpCli = '/usr/bin/php'; 
$worker = $_SERVER['DOCUMENT_ROOT'] . '/controller/mail_process.php';

$sql = "select g_manager_email from df_site_siteinfo";
$g_info = $db->row($sql);

// 메일 수신자, 제목, 본문 구성
$toEmail = $g_info['g_manager_email'];
$toName = '담당자';
$mailSub = "[견적문의] " . $subject;
$mailBody = "
    <h2>견적문의 접수</h2>
    <p><strong>제목:</strong> " . htmlspecialchars($subject, ENT_QUOTES) . "</p>
    <p><strong>이름/회사명:</strong> " . htmlspecialchars($nameCompany, ENT_QUOTES) . "</p>
    <p><strong>연락처:</strong> " . htmlspecialchars($phone, ENT_QUOTES) . "</p>
    <p><strong>이메일:</strong> " . htmlspecialchars($email, ENT_QUOTES) . "</p>
    <p><strong>지역:</strong> " . htmlspecialchars($region, ENT_QUOTES) . "</p>
    <p><strong>용도:</strong> " . htmlspecialchars($usage, ENT_QUOTES) . "</p>
    <p><strong>문의사항:</strong><br>" . nl2br(htmlspecialchars($_POST['f_message'], ENT_QUOTES)) . "</p>
";

// 첨부파일 절대 경로 (없으면 빈 문자열)
$attachmentPathFull = '';
if ($attachmentName !== '') {
    $attachmentPathFull = $_SERVER['DOCUMENT_ROOT'] . '/userfiles/form_attachments/' . $attachmentName;
}

// CLI 인수로 넘길 때는 escapeshellarg 처리
$cmdParts = [
    escapeshellcmd($phpCli),
    escapeshellarg($worker),
    escapeshellarg($toEmail),
    escapeshellarg($toName),
    escapeshellarg($mailSub),
    escapeshellarg($mailBody),
    escapeshellarg($email),
];
if ($attachmentPathFull !== '') {
    $cmdParts[] = escapeshellarg($attachmentPathFull);
}
$cmd = implode(' ', $cmdParts) . ' > /dev/null 2>&1 &';

// 백그라운드 실행
exec($cmd);
exit;
