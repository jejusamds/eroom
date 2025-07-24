<?php
include $_SERVER['DOCUMENT_ROOT'].'/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'].'/inc/util_lib.inc';


if (isset($_GET['mode']) && $_GET['mode'] === 'logout') {
    unset($_SESSION['eroom_sess']);
    echo "<script>window.location.href = '/member/login.html';</script>";
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$user_id = trim($_POST['f_user_id'] ?? '');
$password = trim($_POST['f_password'] ?? '');

if ($user_id === '') {
    echo json_encode(['result'=>'blank','field'=>'f_user_id','msg'=>'아이디를 입력해주세요.']);
    exit;
}
if ($password === '') {
    echo json_encode(['result'=>'blank','field'=>'f_password','msg'=>'비밀번호를 입력해주세요.']);
    exit;
}

$sql = "SELECT f_password FROM df_site_member WHERE f_user_id = :id AND is_del = 'N' AND is_out = 'N'";
$row = $db->row($sql, ['id'=>$user_id]);
if (!$row || !password_verify($password, $row['f_password'])) {
    echo json_encode(['result'=>'error','msg'=>'아이디 또는 비밀번호가 일치하지 않습니다.']);
    exit;
}

$db->query("UPDATE df_site_member SET last_login = NOW() WHERE f_user_id = :id", ['id'=>$user_id]);

$_SESSION['eroom_sess'] = $user_id;

echo json_encode(['result'=>'ok','msg'=>'','redirect'=>'/mypage/']);