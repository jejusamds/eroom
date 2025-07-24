<?php

include $_SERVER['DOCUMENT_ROOT'] . '/inc/global.inc';
include $_SERVER['DOCUMENT_ROOT'] . '/inc/util_lib.inc';

$is_login = false;
$login_user_info = null;
if (!empty($_SESSION['eroom_sess'])) {
    $sql = "SELECT * FROM df_site_member WHERE f_user_id = :f_user_id AND is_del = 'N' AND is_out = 'N'";
    $db->bind("f_user_id", $_SESSION['eroom_sess']);
    $login_user_info = $db->row($sql);
    if ($login_user_info) {
        $is_login = true;
    } else {
        unset($_SESSION['eroom_sess']);
    }
}

// 로그인이 필요한 목록
$login_required_pages = [
    '/mypage/',
    '/mypage/index.html',
    '/mypage/mypage_sub01.html',
    '/mypage/mypage_sub02.html',
    '/mypage/mypage_sub03.html',
    '/mypage/mypage_sub03_view.html'
];

// 로그인 상태에서 접근안되는 목록
$not_login_required_pages = [
    '/member/login.html'
];

/**
 * 로그인 페이지로 리다이렉트
 */
function require_login(){
    global $is_login, $login_required_pages, $not_login_required_pages, $db;
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (!$is_login) {
        if (in_array($uri, $login_required_pages, true)) {
            echo "<script>alert('로그인이 필요합니다.');window.location.href = '/member/login.html';</script>";
            exit;
        }
    } else {
        if (in_array($uri, $not_login_required_pages, true)) {
            echo "<script>window.location.href = '/mypage/';</script>";
            exit;
        }
    }
}
require_login();

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/Mobile_Detect.php';
$detect = new Mobile_Detect;

include $_SERVER['DOCUMENT_ROOT'] . "/inc/_df_counter.php";

function generate_csrf_token()
{
    return bin2hex(random_bytes(32));
}
$csrf_token = generate_csrf_token();
$_SESSION['csrf_token'] = $csrf_token;

