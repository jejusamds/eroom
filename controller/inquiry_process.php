<?php
include $_SERVER['DOCUMENT_ROOT'] . "/inc/global.inc";
include $_SERVER['DOCUMENT_ROOT'] . "/inc/util_lib.inc";

function auto_filter_input($data) {
    return SQL_Injection(RemoveXSS($data));
}

$filtered_post = array_map('auto_filter_input', $_POST);

$aproved_mode = [
    'submit',
    'reset_csrf_token'
];

if (!isset($filtered_post['mode']) || !in_array($filtered_post['mode'], $aproved_mode)) {
    // return_json(['result' => 'error', 'msg' => '잘못된 요청입니다.']);
    // alert 로 처리
    echo "<script>alert('잘못된 요청입니다.');history.back();</script>";
    exit;
}

function upload_file($files){
    $upfile = $files['name'];
    $upfile_tmp = $files['tmp_name'];
    $upfile_size = $files['size'];
    $upfile_error = $files['error'];

    $upfile_ext = explode('.', $upfile);
    $upfile_ext = strtolower(end($upfile_ext));

    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'hwp', 'txt', 'zip', 'alz', 'rar', '7z'];

    if (in_array($upfile_ext, $allowed)) {
        if ($upfile_error === 0) {
            if ($upfile_size <= 1024 * 1024 * 20) {
                $upfile_new = uniqid('', true) . '.' . $upfile_ext;
                $upfile_destination = $_SERVER['DOCUMENT_ROOT'] . '/userfiles/inquiry/' . $upfile_new;
                move_uploaded_file($upfile_tmp, $upfile_destination);
                return $upfile_new;
            } else {
                return_json(['result' => 'error', 'msg' => '파일이 너무 큽니다.']);
            }
        } else {
            return_json(['result' => 'error', 'msg' => '파일 업로드 중 오류가 발생하였습니다.']);
        }
    } else {
        return_json(['result' => 'error', 'msg' => '업로드 할 수 없는 파일 형식입니다.']);
    }
}

if ($filtered_post['mode'] === 'submit') {

    $ret = array();
    $ret['post'] = $filtered_post;
    $this_table = "df_site_inquiry_" . $filtered_post['db'];

    if ($f_name == 'devtest') {
        return_json(['result' => 'test', 'msg' => 'test', 'post' => $filtered_post, 'files' => $_FILES]);
    }

    // file 이 있는경우 upload
    $upfile = '';
    if (!empty($_FILES['upfile']['name'])) {
        $upfile = upload_file($_FILES['upfile']);
    }
    
    if (!empty($_POST['f_honey'])) {
        return_json(['result' => 'f_honey', 'msg' => '잘못된 접근입니다.']);
    }
    
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token'] || empty($_POST['csrf_token'])) {
        return_json(['result' => 'csrf_token', 'msg' => '잘못된 접근입니다 - csrf_token']);
    }

    // show
    if ($filtered_post['db'] == 'show') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                'f_meeting_place' => '미팅장소는 필수 항목입니다.',
                'f_hope_visit_date' => '방문희망일은 필수 항목입니다.',
                'f_hope_visit_time' => '방문희망시간은 필수 항목입니다.',
                'f_inquiry_contents' => '신청이유는 필수 항목입니다.',
                //'privacy' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            // $f_email = $filtered_post['f_email'] . '@' .$filtered_post['f_email_2'];
            // if (filter_var($f_email, FILTER_VALIDATE_EMAIL)) {} 
            // else {
            //     $ret['result'] = 'wrong';
            //     $ret['field'] = '이메일';
            //     $ret['msg'] = '이메일 형식을 확인해 주세요.';
            //     $ret['email'] = $f_email;
            //     return_json($ret);
            // }
            
            $sql = "INSERT INTO $this_table SET ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "f_meeting_place = :f_meeting_place, ";
            $sql .= "f_hope_visit_date = :f_hope_visit_date, ";
            $sql .= "f_hope_visit_time = :f_hope_visit_time, ";
            $sql .= "f_build_period = :f_build_period, ";
            $sql .= "f_expected_size = :f_expected_size, ";
            $sql .= "f_prefer_way = :f_prefer_way, ";
            $sql .= "f_build_place = :f_build_place, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip";
            
            $params = [
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'f_meeting_place' => $filtered_post['f_meeting_place'],
                'f_hope_visit_date' => $filtered_post['f_hope_visit_date'],
                'f_hope_visit_time' => $filtered_post['f_hope_visit_time'],
                'f_build_period' => $filtered_post['f_build_period'],
                'f_expected_size' => $filtered_post['f_expected_size'],
                'f_prefer_way' => $filtered_post['f_prefer_way'],
                'f_build_place' => $filtered_post['f_build_place'],
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }
        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }

    // buy
    if ($filtered_post['db'] == 'buy') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                //'f_product' => '상품은 필수 항목입니다.',
                //'privacy' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            $sql = "INSERT INTO $this_table SET ";
            $sql .= "f_product = :f_product, ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "f_build_place = :f_build_place, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip ";

            $params = [
                'f_product' => $filtered_post['f_product'],
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'f_build_place' => $filtered_post['f_build_place'],
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }
        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }

    // dev
    if ($filtered_post['db'] == 'dev') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                //'privacy' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            // f_category 가 개발이면 f_dev_product 는 f_dev_product_dev, 견적이면 f_dev_product_estimate
            if ($filtered_post['f_category'] == '개발') {
                $filtered_post['f_dev_product'] = $filtered_post['f_dev_product_dev'];
            } else {
                $filtered_post['f_dev_product'] = $filtered_post['f_dev_product_estimate'];
            }

            $sql = "INSERT INTO $this_table SET ";
            $sql .= "f_category = :f_category, ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "f_dev_product = :f_dev_product, ";
            $sql .= "f_build_place = :f_build_place, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "upfile = :upfile, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip ";

            $params = [
                'f_category' => $filtered_post['f_category'],
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'f_dev_product' => $filtered_post['f_dev_product'],
                'f_build_place' => $filtered_post['f_build_place'],
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'upfile' => $upfile,
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }
        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }

    // coalition
    if ($filtered_post['db'] == 'coalition') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                //'privacy' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            $sql = "INSERT INTO $this_table SET ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "f_coalition_contents = :f_coalition_contents, ";
            $sql .= "upfile = :upfile, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip ";

            $params = [
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'f_coalition_contents' => $filtered_post['f_coalition_contents'],
                'upfile' => $upfile,
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }
        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }

    // as
    if ($filtered_post['db'] == 'as') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                // 'f_address_postcode' => '우편번호는 필수 항목입니다.',
                // 'f_address_basic' => '기본주소는 필수 항목입니다.',
                // 'f_address_back' => '상세주소는 필수 항목입니다.',
                //'privacy' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            $sql = "INSERT INTO $this_table SET ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "f_address_postcode = :f_address_postcode, ";
            $sql .= "f_address_basic = :f_address_basic, ";
            $sql .= "f_address_back = :f_address_back, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "upfile = :upfile, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip ";

            $params = [
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'f_address_postcode' => $filtered_post['f_address_postcode'],
                'f_address_basic' => $filtered_post['f_address_basic'],
                'f_address_back' => $filtered_post['f_address_back'],
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'upfile' => $upfile,
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }
        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }

    // contact_us
    if ($filtered_post['db'] == 'contact_us') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                'apply_type' => '문의유형은 필수 항목입니다.',
                'f_inquiry_contents' => '문의내용은 필수 항목입니다.',
                'agree' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            $sql = "INSERT INTO $this_table SET ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "apply_type = :apply_type, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "upfile = :upfile, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip ";

            $params = [
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'apply_type' => $filtered_post['apply_type'],
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'upfile' => $upfile,
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }
        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }

    /*
    CREATE TABLE `df_site_inquiry_quick` (
    `idx` INT(11) NOT NULL AUTO_INCREMENT,
    `apply_type` VARCHAR(50) COMMENT '상담선택(필수)',
    `f_name` VARCHAR(64) DEFAULT NULL COMMENT '이름(필수)',
    `f_tel` VARCHAR(32) DEFAULT NULL COMMENT '전화번호(필수)', 
    `f_inquiry_contents` MEDIUMTEXT COMMENT '상담내용(필수)',  
    `f_agree` ENUM('Y','N') DEFAULT 'Y',
    `is_del` ENUM('N','Y') DEFAULT 'N',
    `wip` VARCHAR(32) DEFAULT NULL COMMENT '아이피',
    `wdate` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    PRIMARY KEY (`idx`)
    );
    */

    // quick
    if ($filtered_post['db'] == 'quick') {

        try {
            $f_ref_site = SQL_Injection(RemoveXSS($_SESSION['ref_site'] ?? ''));
            $ret['f_ref_site'] = $f_ref_site;
            $f_wip = $_SERVER['REMOTE_ADDR'];
            $pure_tel = preg_replace('/\D/', '', $filtered_post['f_tel'] ?? '');

            // 필수 항목
            $required_fields = [
                'apply_type' => '상담선택은 필수 항목입니다.',
                'f_name' => '이름은 필수 항목입니다.',
                'f_tel' => '연락처는 필수 항목입니다.',
                'f_inquiry_contents' => '상담내용은 필수 항목입니다.',
                'f_agree' => '개인정보 수집 및 이용에 동의해 주세요.',
            ];

            foreach ($required_fields as $field => $message) {
                if (empty($filtered_post[$field])) {
                    return_json(['result' => 'blank', 'field' => $field, 'msg' => $message]);
                }
            }

            // 연락처 형식 검사
            if (!preg_match('/^[\d-]+$/', $filtered_post['f_tel'])) {
                return_json(['result' => 'wrong', 'field' => '연락처', 'msg' => '연락처에는 숫자만 입력 가능합니다.']);
            }
            
            if (strlen($filtered_post['f_tel']) > 18) {
                return_json(['result' => 'too_long', 'field' => '연락처', 'msg' => '연락처의 길이를 확인해 주세요.']);
            }

            $sql = "INSERT INTO $this_table SET ";
            $sql .= "apply_type = :apply_type, ";
            $sql .= "f_name = :f_name, ";
            $sql .= "f_tel = :f_tel, ";
            $sql .= "f_inquiry_contents = :f_inquiry_contents, ";
            $sql .= "f_agree = :f_agree, ";
            $sql .= "wdate = NOW(), ";
            $sql .= "wip = :f_wip ";

            $params = [
                'apply_type' => $filtered_post['apply_type'],
                'f_name' => $filtered_post['f_name'],
                'f_tel' => $filtered_post['f_tel'],
                'f_inquiry_contents' => $filtered_post['f_inquiry_contents'],
                'f_agree' => $filtered_post['f_agree'],
                'f_wip' => $f_wip,
            ];

            if ($db->query($sql, $params)) {
                return_json(['result' => 'ok', 'msg' => '문의하기가 등록되었습니다.']);
            } else {
                return_json(['result' => 'error', 'msg' => '일시적인 오류로 신청에 실패하였습니다.']);
            }

        } catch (Exception $e) {
            return_json(['result' => 'error', 'msg' => '일시적인 장애로 신청에 실패하였습니다.']);
        }
    }
}

function return_json($ret) {
    header('Content-Type: application/json');
    echo json_encode($ret);
    exit;
}
