<?php
header('Content-Type: application/json; charset=utf-8');

if (
    !isset(
    $_POST['f_name_first'],
    $_POST['f_name_last'],
    $_POST['f_email'],
    $_POST['f_company_name'],
    $_POST['f_country'],
    $_POST['f_contact_number'],
    $_POST['f_message']
)
) {
    echo json_encode(['result' => 'error', 'msg' => '필수 항목이 누락되었습니다.']);
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

$lang_type = addslashes(trim($_POST['lang_type'] ?? 'kr'));
$first = addslashes(trim($_POST['f_name_first']));
$last = addslashes(trim($_POST['f_name_last']));
$email = addslashes(trim($_POST['f_email']));
$company = addslashes(trim($_POST['f_company_name']));
$country = addslashes(trim($_POST['f_country']));
$contact = addslashes(trim($_POST['f_contact_number']));
$message = addslashes(trim($_POST['f_message']));
$wip = $_SERVER['REMOTE_ADDR'];

$sql = "INSERT INTO df_site_contact_us
        (lang_type, f_name_first, f_name_last, f_email, f_company_name, f_country, f_contact_number, f_message, wip, wdate)
        VALUES
        ('{$lang_type}', '{$first}', '{$last}', '{$email}', '{$company}', '{$country}', '{$contact}', '{$message}', '{$wip}', NOW())";
$db->query($sql);

$sql = "select g_manager_email from df_site_siteinfo";
$g_info = $db->row($sql);

echo json_encode(['result' => 'ok', 'msg' => '문의가 정상 접수되었습니다.', 'redirect' => '']);
flush();
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

$phpCli = '/usr/bin/php';
$worker = $_SERVER['DOCUMENT_ROOT'] . '/controller/mail_process.php';

$toEmail = $g_info['g_manager_email'];
$toName = '담당자';
$mailSub = $lang_type === 'kr' ? '[문의하기]' : '[Contact Us]';
$mailBody = "<h2>Contact Us Inquiry</h2>" .
    "<p><strong>이름:</strong> " . htmlspecialchars($first . ' ' . $last, ENT_QUOTES) . "</p>" .
    "<p><strong>이메일:</strong> " . htmlspecialchars($email, ENT_QUOTES) . "</p>" .
    "<p><strong>회사명:</strong> " . htmlspecialchars($company, ENT_QUOTES) . "</p>" .
    "<p><strong>국가:</strong> " . htmlspecialchars($country, ENT_QUOTES) . "</p>" .
    "<p><strong>연락처:</strong> " . htmlspecialchars($contact, ENT_QUOTES) . "</p>" .
    "<p><strong>문의내용:</strong><br>" . nl2br(htmlspecialchars($_POST['f_message'], ENT_QUOTES)) . "</p>";

$query = http_build_query([
    'toEmail'       => $toEmail,
    'toName'        => $toName,
    'mailSub'       => $mailSub,
    'mailBody'      => $mailBody,
    'attachment'    => $attachmentName
]);

// 프로토콜과 호스트 정보
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$url = "{$protocol}://" . $_SERVER['HTTP_HOST'] . "/controller/mail_process.php?{$query}";

// cURL 초기화 및 옵션 설정
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 1,
    CURLOPT_CONNECTTIMEOUT => 1,
]);
curl_exec($ch);
curl_close($ch);

exit;

// $cmdParts = [
//     escapeshellcmd($phpCli),
//     escapeshellarg($worker),
//     escapeshellarg($toEmail),
//     escapeshellarg($toName),
//     escapeshellarg($mailSub),
//     escapeshellarg($mailBody),
//     escapeshellarg($email)
// ];
// $cmd = implode(' ', $cmdParts) . ' > /dev/null 2>&1 &';
// exec($cmd);
// exit;
?>