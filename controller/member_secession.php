<?php
include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

header('Content-Type: application/json; charset=utf-8');

if (($_POST['mode'] ?? '') !== 'secession') {
    echo json_encode(['result' => 'error', 'msg' => '잘못된 요청입니다.']);
    exit;
}

if (empty($_SESSION['eroom_sess'])) {
    echo json_encode(['result' => 'error', 'msg' => '로그인이 필요합니다.']);
    exit;
}

if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['result' => 'error', 'msg' => '잘못된 접근입니다.']);
    exit;
}

$f_user_id = $_SESSION['eroom_sess'];

$db->query("UPDATE df_site_member SET is_out = 'Y' WHERE f_user_id = :id", ['id' => $f_user_id]);
$db->query("INSERT INTO df_site_member_out (f_user_id, reason) VALUES (:id, NULL)", ['id' => $f_user_id]);

unset($_SESSION['eroom_sess']);

echo json_encode(['result' => 'ok', 'msg' => '탈퇴 처리되었습니다.', 'redirect' => '/member/login.html']);
