<?php
include $_SERVER['DOCUMENT_ROOT'].'/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'].'/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

$mode = $_POST['mode'] ?? '';

function json_exit($arr){
    echo json_encode($arr);
    exit;
}

function check_id_hangul($userId) {
    // 한글 불가처리. hangul 으로 리턴
    if(preg_match('/[ㄱ-ㅎㅏ-ㅣ가-힣]/u', $userId)){
        return ['result'=>'hangul','msg'=>'아이디에 한글은 사용할 수 없습니다.'];
    }
    return null;
}

// 폼 유효성 검사 함수
function validate_form($data) {
    $required = ['f_user_id', 'f_password', 'f_user_name', 'f_birth_date', 'f_mobile', 'f_email', 'f_gender', 'f_addr_zip'];
    foreach($required as $field){
        if(empty($data[$field])){
            return ['result'=>'error', 'msg'=>'필수 항목이 누락되었습니다.'];
        }
    }
    return null;
}

if($mode === 'check_id'){
    $userId = trim($_POST['f_user_id'] ?? '');
    if($userId === '') json_exit(['result'=>'error','msg'=>'아이디를 입력해주세요.']);

    $hangulCheck = check_id_hangul($userId);
    if($hangulCheck) {
        json_exit($hangulCheck);
    }
    
    $row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_user_id = :id", ['id'=>$userId]);
    json_exit(['result'=>$row['cnt'] ? 'exist' : 'ok']);
}

if($mode !== 'sign_up'){
    json_exit(['result'=>'error','msg'=>'잘못된 요청입니다.']);
}

// 폼 유효성 검사
$validationError = validate_form($_POST);
if($validationError) {
    json_exit($validationError);
}

// 중복 확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_user_id = :id", ['id'=>$_POST['f_user_id']]);
if($row['cnt']) json_exit(['result'=>'error','msg'=>'이미 사용중인 아이디입니다.']);

// 한글 불가처리
$hangulCheck = check_id_hangul($_POST['f_user_id']);
if($hangulCheck) {
    json_exit($hangulCheck);
}

// 비밀번호 유효성 검사. 4~12자 미만의 영문/숫자 조합만 가능
if(strlen($_POST['f_password']) < 8 || !preg_match('/[A-Za-z0-9]/', $_POST['f_password'])){
    json_exit(['result'=>'error','msg'=>'비밀번호는 8자 이상, 영문과 숫자를 조합하여 입력해주세요.']);
}

// f_password_chk, f_password 비교
if($_POST['f_password'] !== $_POST['f_password_chk']){
    json_exit(['result'=>'error','msg'=>'비밀번호가 일치하지 않습니다.']);
}

// 휴대폰 번호 중복확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_mobile = :mobile", ['mobile'=>$_POST['f_mobile']]);
if($row['cnt']) json_exit(['result'=>'error','msg'=>'이미 사용중인 휴대폰 번호입니다.']);

// 이메일 중복확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_email = :email", ['email'=>$_POST['f_email']]);
if($row['cnt']) json_exit(['result'=>'error','msg'=>'이미 사용중인 이메일입니다.']);

$tel    = $_POST['f_tel']    ?? '';
$data = [
    'f_user_id'     => $_POST['f_user_id'],
    'f_password'    => password_hash($_POST['f_password'], PASSWORD_DEFAULT),
    'f_user_name'   => $_POST['f_user_name'],
    'f_birth_date'  => $_POST['f_birth_date'],
    'f_tel'         => $tel,
    'f_mobile'      => $_POST['f_mobile'],
    'f_email'       => $_POST['f_email'],
    'f_gender'      => $_POST['f_gender'],
    'f_addr_zip'    => $_POST['f_addr_zip'],
    'f_addr_basic'  => $_POST['f_addr_basic'],
    'f_addr_detail' => $_POST['f_addr_detail'] ?? null,
    'wip'           => $_SERVER['REMOTE_ADDR'] ?? '',
];

$sql = "INSERT INTO df_site_member (f_user_id,f_password,f_user_name,f_birth_date,f_tel,f_mobile,f_email,f_gender,f_addr_zip,f_addr_basic,f_addr_detail,wip) VALUES (:f_user_id,:f_password,:f_user_name,:f_birth_date,:f_tel,:f_mobile,:f_email,:f_gender,:f_addr_zip,:f_addr_basic,:f_addr_detail,:wip)";

if(!$db->query($sql,$data)){
    json_exit(['result'=>'error','msg'=>'저장 중 오류가 발생했습니다.']);
}

json_exit(['result'=>'ok','redirect'=>'/member/join_step03.html', 'msg'=>'회원가입이 완료되었습니다.']);
