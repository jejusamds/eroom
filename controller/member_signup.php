<?php
include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

function digits_only(string $val): string
{
    return preg_replace('/\D/', '', $val);
}

function sanitize_signup_input(array $data): array
{
    $data = array_map('trim', $data);
    if (isset($data['f_tel'])) {
        $data['f_tel'] = digits_only($data['f_tel']);
    }
    if (isset($data['f_mobile'])) {
        $data['f_mobile'] = digits_only($data['f_mobile']);
    }
    if (isset($data['f_birth_date'])) {
        $data['f_birth_date'] = digits_only($data['f_birth_date']);
        if ($data['f_birth_date'] !== '' && strlen($data['f_birth_date']) !== 8) {
            json_exit(['result' => 'error', 'msg' => '생년월일은 숫자 8자리로 입력해주세요.']);
        }
    }
    return $data;
}

$mode = $_POST['mode'] ?? '';
$post = sanitize_signup_input($_POST);

function json_exit($arr)
{
    echo json_encode($arr);
    exit;
}

function check_id_hangul($userId)
{
    // 한글 불가처리. hangul 으로 리턴
    if (preg_match('/[ㄱ-ㅎㅏ-ㅣ가-힣]/u', $userId)) {
        return ['result' => 'hangul', 'msg' => '아이디에 한글은 사용할 수 없습니다.'];
    }
    return null;
}

// 폼 유효성 검사 함수
function validate_form($data)
{
    $required = ['f_user_id', 'f_password', 'f_user_name', 'f_birth_date', 'f_mobile', 'f_email', 'f_gender', 'f_addr_zip'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['result' => 'error', 'msg' => '필수 항목이 누락되었습니다.'];
        }
    }
    return null;
}

if ($mode === 'check_id') {
    $userId = trim($post['f_user_id'] ?? '');
    if ($userId === '')
        json_exit(['result' => 'error', 'msg' => '아이디를 입력해주세요.']);

    $hangulCheck = check_id_hangul($userId);
    if ($hangulCheck) {
        json_exit($hangulCheck);
    }

    $row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_user_id = :id", ['id' => $userId]);
    json_exit(['result' => $row['cnt'] ? 'exist' : 'ok']);
}

if ($mode !== 'sign_up') {
    json_exit(['result' => 'error', 'msg' => '잘못된 요청입니다.']);
}

// 폼 유효성 검사
$validationError = validate_form($post);
if ($validationError) {
    json_exit($validationError);
}

// 중복 확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_user_id = :id", ['id' => $post['f_user_id']]);
if ($row['cnt'])
    json_exit(['result' => 'error', 'msg' => '이미 사용중인 아이디입니다.']);

// 한글 불가처리
$hangulCheck = check_id_hangul($post['f_user_id']);
if ($hangulCheck) {
    json_exit($hangulCheck);
}

// 비밀번호 유효성 검사. 4~12자 미만의 영문/숫자 조합만 가능
if (strlen($post['f_password']) < 8 || !preg_match('/[A-Za-z0-9]/', $post['f_password'])) {
    json_exit(['result' => 'error', 'msg' => '비밀번호는 8자 이상, 영문과 숫자를 조합하여 입력해주세요.']);
}

// f_password_chk, f_password 비교
if ($post['f_password'] !== $post['f_password_chk']) {
    json_exit(['result' => 'error', 'msg' => '비밀번호가 일치하지 않습니다.']);
}

// 휴대폰 번호 중복확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE REPLACE(f_mobile, '-', '') = :mobile", ['mobile' => $post['f_mobile']]);
if ($row['cnt'])
    json_exit(['result' => 'error', 'msg' => '이미 사용중인 휴대폰 번호입니다.']);

// 이메일 중복확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_email = :email", ['email' => $post['f_email']]);
if ($row['cnt'])
    json_exit(['result' => 'error', 'msg' => '이미 사용중인 이메일입니다.']);

$tel = $post['f_tel'] ?? '';
$data = [
    'f_user_id' => $post['f_user_id'],
    'f_password' => password_hash($post['f_password'], PASSWORD_DEFAULT),
    'f_user_name' => $post['f_user_name'],
    'f_birth_date' => $post['f_birth_date'],
    'f_tel' => $tel,
    'f_mobile' => $post['f_mobile'],
    'f_email' => $post['f_email'],
    'f_gender' => $post['f_gender'],
    'f_addr_zip' => $post['f_addr_zip'],
    'f_addr_basic' => $post['f_addr_basic'],
    'f_addr_detail' => $post['f_addr_detail'] ?? null,
    'wip' => $_SERVER['REMOTE_ADDR'] ?? '',
];

//$sql = "INSERT INTO df_site_member (f_user_id,f_password,f_user_name,f_birth_date,f_tel,f_mobile,f_email,f_gender,f_addr_zip,f_addr_basic,f_addr_detail,wip) VALUES (:f_user_id,:f_password,:f_user_name,:f_birth_date,:f_tel,:f_mobile,:f_email,:f_gender,:f_addr_zip,:f_addr_basic,:f_addr_detail,:wip)";
$sql = "";
$sql .= " INSERT INTO df_site_member ( ";
$sql .= " f_user_id ";
$sql .= " ,f_password ";
$sql .= " ,f_user_name ";
$sql .= " ,f_birth_date ";
$sql .= " ,f_tel ";
$sql .= " ,f_mobile ";
$sql .= " ,f_email ";
$sql .= " ,f_gender ";
$sql .= " ,f_addr_zip ";
$sql .= " ,f_addr_basic ";
$sql .= " ,f_addr_detail ";
$sql .= " ,wip ";
$sql .= " ) VALUES ( ";
$sql .= " :f_user_id ";
$sql .= " ,:f_password ";
$sql .= " ,:f_user_name ";
$sql .= " ,:f_birth_date ";
$sql .= " ,:f_tel ";
$sql .= " ,:f_mobile ";
$sql .= " ,:f_email ";
$sql .= " ,:f_gender ";
$sql .= " ,:f_addr_zip ";
$sql .= " ,:f_addr_basic ";
$sql .= " ,:f_addr_detail ";
$sql .= " ,:wip ";
$sql .= " ) ";

if (!$db->query($sql, $data)) {
    json_exit(['result' => 'error', 'msg' => '저장 중 오류가 발생했습니다.']);
}

json_exit(['result' => 'ok', 'redirect' => '/member/join_step03.html', 'msg' => '회원가입이 완료되었습니다.']);