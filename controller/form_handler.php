<?php
include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

// 1) 필수 값 검증
if (!isset(
    $_POST['f_subject'],
    $_POST['f_name_company'],
    $_POST['f_phone'],
    $_POST['f_email'],
    $_POST['f_message']
)) {
    echo json_encode([
        'result' => 'error',
        'msg'    => '필수 항목이 누락되었습니다.',
        'post'   => $_POST
    ]);
    exit;
}

// 2) DB 저장을 위한 변수 준비
$subject         = addslashes(trim($_POST['f_subject']));
$nameCompany     = addslashes(trim($_POST['f_name_company']));
$phone           = addslashes(trim($_POST['f_phone']));
$email           = addslashes(trim($_POST['f_email']));
$contractProject = addslashes(trim($_POST['f_contract_project']));
$message         = addslashes(trim($_POST['f_message']));

// 3) 첨부 파일 처리
$attachmentName = '';
if (!empty($_FILES['f_attachment']) && $_FILES['f_attachment']['error'] === UPLOAD_ERR_OK) {
    $ext       = strtolower(pathinfo($_FILES['f_attachment']['name'], PATHINFO_EXTENSION));
    $uniqName  = 'as_' . time() . '.' . $ext;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/userfiles/form_attachments/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0707, true);
    move_uploaded_file($_FILES['f_attachment']['tmp_name'], $uploadDir . $uniqName);
    $attachmentName = $uniqName;
}

// 4) DB에 문의 저장
$sql = "INSERT INTO df_site_form_as
    (f_subject, f_name_company, f_phone, f_email, f_contract_project, f_attachment, f_message)
 VALUES
    ('{$subject}','{$nameCompany}','{$phone}','{$email}','{$contractProject}','{$attachmentName}','{$message}')";
$db->query($sql);

// 5) 관리자 메일 조회
$g_info = $db->row("SELECT g_manager_email FROM df_site_siteinfo LIMIT 1");
$toEmail = $g_info['g_manager_email'];
$toName  = '담당자';
$mailSub = "[AS 문의] {$subject}";
$mailBody =
    "<h2>AS 문의 접수</h2>" .
    "<p><strong>제목:</strong> " . htmlspecialchars($subject, ENT_QUOTES) . "</p>" .
    "<p><strong>이름/회사명:</strong> " . htmlspecialchars($nameCompany, ENT_QUOTES) . "</p>" .
    "<p><strong>연락처:</strong> " . htmlspecialchars($phone, ENT_QUOTES) . "</p>" .
    "<p><strong>이메일:</strong> " . htmlspecialchars($email, ENT_QUOTES) . "</p>" .
    "<p><strong>계약공사명:</strong> " . htmlspecialchars($contractProject, ENT_QUOTES) . "</p>" .
    "<p><strong>문의사항:</strong><br>" . nl2br(htmlspecialchars($_POST['f_message'], ENT_QUOTES)) . "</p>";

// 6) JSON 응답 후 즉시 종료 (비동기 호출 위해)
echo json_encode(['result' => 'ok', 'msg' => '접수가 완료되었습니다.']);
flush();

// 7) 비동기 HTTP 요청으로 메일 프로세스 호출
$query = http_build_query([
    'toEmail'       => $toEmail,
    'toName'        => $toName,
    'mailSub'       => $mailSub,
    'mailBody'      => $mailBody,
    'replyTo'       => $email,
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
