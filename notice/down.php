<?
$fg = $_GET[fg];

if($fg == "1"){
	$file = "../img/notice/notice_sub04_list_icon01.svg"; 
	$filename = "CI/BI 가이드라인.svg";
	$filename = iconv("UTF-8","EUC-KR",$filename);
}

if($fg == "2"){
	$file = "../img/notice/notice_sub04_list_icon02.svg"; 
	$filename = "로고 파일.svg";
	$filename = iconv("UTF-8","EUC-KR",$filename);
}

if($fg == "3"){
	$file = "../img/notice/notice_sub04_list_icon03.svg"; 
	$filename = "주요 활동 고화질 사진.svg";
	$filename = iconv("UTF-8","EUC-KR",$filename);
}

if($fg == "4"){
	$file = "../img/notice/notice_sub04_list_icon04.svg"; 
	$filename = "홍보 영상.svg";
	$filename = iconv("UTF-8","EUC-KR",$filename);
}

if(file_exists($file)) {
   
   if( strstr($HTTP_USER_AGENT,"MSIE 5.5")){ 
       header("Content-Type: doesn/matter"); 
       header("Content-Disposition: filename=$filename"); 
       header("Content-Transfer-Encoding: binary"); 
       header("Pragma: no-cache"); 
       header("Expires: 0"); 
   }else{ 
       Header("Content-type: file/unknown"); 
       Header("Content-Disposition: attachment; filename=$filename"); 
       Header("Content-Description: PHP3 Generated Data"); 
       header("Pragma: no-cache"); 
       header("Expires: 0"); 
   }
   
   if(is_file("$file")){ 
       $fp = fopen("$file","r"); 
       if(!fpassthru($fp)) {
           fclose($fp);
       }
   }
 
}else{
   echo "<script>alert('첨부파일이 존재하지 않습니다.$file');history.go(-1);</script>";
}
?>