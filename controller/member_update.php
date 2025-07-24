<?php
include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

function digits_only($val)
{
    return preg_replace('/\D/', '', $val);
}

$post = array_map('trim', $_POST);
$post['f_tel'] = digits_only($post['f_tel'] ?? '');
$post['f_mobile'] = digits_only($post['f_mobile'] ?? '');

if (($post['mode'] ?? '') !== 'update') {
    echo json_encode(['result' => 'error', 'msg' => '잘못된 요청입니다.']);
    exit;
}

if (empty($_SESSION['eroom_sess'])) {
    echo json_encode(['result' => 'error', 'msg' => '로그인이 필요합니다.']);
    exit;
}

if (($post['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['result' => 'error', 'msg' => '잘못된 접근입니다 - csrf_token']);
    exit;
}

// 휴대폰 번호 중복 체크
$f_mobile = $post['f_mobile'] ?? '';
if ($f_mobile !== '') {
    $existing_member = $db->row("SELECT f_user_id FROM df_site_member WHERE f_mobile = :mobile AND f_user_id != :id", [
        'mobile' => $f_mobile,
        'id' => $_SESSION['eroom_sess']
    ]);
    if ($existing_member) {
        echo json_encode(['result' => 'error', 'msg' => '이미 등록된 휴대폰 번호입니다.']);
        exit;
    }
}

// 이메일 중복 체크
$f_email = $post['f_email'] ?? '';
if ($f_email !== '') {
    $existing_member = $db->row("SELECT f_user_id FROM df_site_member WHERE f_email = :email AND f_user_id != :id", [
        'email' => $f_email,
        'id' => $_SESSION['eroom_sess']
    ]);
    if ($existing_member) {
        echo json_encode(['result' => 'error', 'msg' => '이미 등록된 이메일 주소입니다.']);
        exit;
    }
}

$f_password_old = $post['f_password_old'] ?? '';
if ($f_password_old === '') {
    echo json_encode(['result' => 'error', 'msg' => '기존 비밀번호를 입력하세요.']);
    exit;
}

$member = $db->row("SELECT f_password FROM df_site_member WHERE f_user_id = :id", ['id' => $_SESSION['eroom_sess']]);
if (!$member || !password_verify($f_password_old, $member['f_password'])) {
    echo json_encode(['result' => 'error', 'msg' => '기존 비밀번호가 일치하지 않습니다.']);
    exit;
}

$new_password = "";
if (!empty($post['f_password'])) {
    // // 	4~12자 미만의 영문/숫자 조합만 가능
    if (
        strlen($post['f_password']) < 4 ||
        strlen($post['f_password']) > 12 ||
        !preg_match('/^[a-zA-Z0-9]+$/', $post['f_password']) || // 영문/숫자 외 불가
        !preg_match('/[a-zA-Z]/', $post['f_password']) || // 영문 반드시 1개 이상
        !preg_match('/[0-9]/', $post['f_password'])       // 숫자 반드시 1개 이상
    ) {
        echo json_encode(['result' => 'error', 'msg' => '비밀번호는 4~12자의 영문+숫자 조합만 가능합니다.']);
        exit;
    }

    if ($post['f_password'] !== ($post['f_password_chk'] ?? '')) {
        echo json_encode(['result' => 'error', 'msg' => '새 비밀번호가 일치하지 않습니다.']);
        exit;
    } else {
        $new_password = password_hash($post['f_password'], PASSWORD_DEFAULT);
    }
}

$update = [
    'f_tel' => $post['f_tel'] ?? '',
    'f_mobile' => $post['f_mobile'] ?? '',
    'f_email' => $post['f_email'] ?? '',
    'f_addr_zip' => $post['f_addr_zip'] ?? '',
    'f_addr_basic' => $post['f_addr_basic'] ?? '',
    'f_addr_detail' => $post['f_addr_detail'] ?? '',
    'f_tax_service' => $post['f_tax_service'] ?? 'N',
    'f_news_agree' => $post['f_news_agree'] ?? 'N',
];

// new_password 가 있으면 비밀번호 변경
if (!empty($new_password)) {
    $update['f_password'] = $new_password;
}


if (!empty($post['f_password'])) {
    $update['f_password'] = password_hash($post['f_password'], PASSWORD_DEFAULT);
}

$set = [];
foreach ($update as $k => $v) {
    $set[] = "$k = :$k";
}
$sql = "UPDATE df_site_member SET " . implode(',', $set) . " WHERE f_user_id = :id";
$update['id'] = $_SESSION['eroom_sess'];

if ($db->query($sql, $update) === false) {
    echo json_encode(['result' => 'error', 'msg' => '수정 중 오류가 발생했습니다.']);
    exit;
}

echo json_encode(['result' => 'ok', 'msg' => '수정되었습니다.', 'redirect' => '/mypage/mypage_sub01.html']);