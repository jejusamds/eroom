<?php
include $_SERVER['DOCUMENT_ROOT'].'/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'].'/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

$mode = $_POST['mode'] ?? '';

function json_exit($arr){
    echo json_encode($arr);
    exit;
}

if($mode === 'check_id'){
    $userId = trim($_POST['f_user_id'] ?? '');
    if($userId === '') json_exit(['result'=>'error','msg'=>'아이디를 입력해주세요.']);
    $row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_user_id = :id", ['id'=>$userId]);
    json_exit(['result'=>$row['cnt'] ? 'exist' : 'ok']);
}

if($mode !== 'sign_up'){
    json_exit(['result'=>'error','msg'=>'잘못된 요청입니다.']);
}

$required = ['f_user_id','f_password','f_user_name','f_birth_date','f_mobile','f_email','f_gender','f_addr_zip','f_addr_basic'];
foreach($required as $field){
    if(empty($_POST[$field])){
        json_exit(['result'=>'error','msg'=>'필수 항목이 누락되었습니다.']);
    }
}

// 중복 확인
$row = $db->row("SELECT COUNT(*) AS cnt FROM df_site_member WHERE f_user_id = :id", ['id'=>$_POST['f_user_id']]);
if($row['cnt']) json_exit(['result'=>'error','msg'=>'이미 사용중인 아이디입니다.']);

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
