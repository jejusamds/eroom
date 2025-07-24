<?php
include $_SERVER['DOCUMENT_ROOT'].'/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'].'/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

function digits_only($val){
    return preg_replace('/\D/', '', $val);
}

$post = array_map('trim', $_POST);
$post['f_tel'] = digits_only($post['f_tel'] ?? '');
$post['f_mobile'] = digits_only($post['f_mobile'] ?? '');

if(($post['mode'] ?? '') !== 'update'){
    echo json_encode(['result'=>'error','msg'=>'잘못된 요청입니다.']);
    exit;
}

if(empty($_SESSION['eroom_sess'])){
    echo json_encode(['result'=>'error','msg'=>'로그인이 필요합니다.']);
    exit;
}

if(($post['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')){
    echo json_encode(['result'=>'error','msg'=>'잘못된 접근입니다 - csrf_token']);
    exit;
}

$f_password_old = $post['f_password_old'] ?? '';
if($f_password_old === ''){
    echo json_encode(['result'=>'error','msg'=>'기존 비밀번호를 입력하세요.']);
    exit;
}

$member = $db->row("SELECT f_password FROM df_site_member WHERE f_user_id = :id", ['id'=>$_SESSION['eroom_sess']]);
if(!$member || !password_verify($f_password_old, $member['f_password'])){
    echo json_encode(['result'=>'error','msg'=>'기존 비밀번호가 일치하지 않습니다.']);
    exit;
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

if(!empty($post['f_password'])){
    $update['f_password'] = password_hash($post['f_password'], PASSWORD_DEFAULT);
}

$set = [];
foreach($update as $k=>$v){
    $set[] = "$k = :$k";
}
$sql = "UPDATE df_site_member SET ".implode(',', $set)." WHERE f_user_id = :id";
$update['id'] = $_SESSION['eroom_sess'];

if(!$db->query($sql,$update)){
    echo json_encode(['result'=>'error','msg'=>'수정 중 오류가 발생했습니다.']);
    exit;
}

echo json_encode(['result'=>'ok','msg'=>'수정되었습니다.','redirect'=>'/mypage/mypage_sub01.html']);

