#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 푸시보내기
* 페이지 설명		: 푸시보내기
* 파일명				: richPush.php

========================================================================================================================*/

// register_globals off 처리
@extract($_GET);
@extract($_POST);
@extract($_SERVER);
@extract($_ENV);
@extract($_SESSION);
@extract($_COOKIE);
@extract($_REQUEST);
@extract($_FILES);

ob_start();

header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

//구글 fcm키
define("GOOGLE_API_KEY", "AAAABqpoJMc:APA91bG7AfoVWTt12jUMQUH39kn89rn9GwOselIfhWB8zQZjMfsVsbKalIPWf86-gfaju6Itvdoqr8GMLqXf6AX7KHRVM3Enm9rf3ZECO9YZDWi4hdFSP5HFPM9wuWbGbI6PocTUApz2");
	include 'inc/dbcon.php';


	$DB_con = db1();

    $reg_Date = date('Y-m-d H:i:s', time());	 //등록일
	$now_Time = $reg_Date;				//현재시간

	$Query = "
		SELECT mem_Idx, SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) as mCnt, SUM(CASE WHEN type = 2 THEN 1 ELSE 0 END) as pCnt, SUM(CASE WHEN type = 4 THEN 1 ELSE 0 END) as ppCnt
		FROM TB_PUSH_HISTORY
		WHERE push_Bit = 'N'
			AND type IN ('1', '2', '4')
		GROUP BY mem_Idx;
	";
	//echo $Query1."<BR>";
	//exit;
	$Stmt = $DB_con->prepare($Query);
	$Stmt->execute();
	$num = $Stmt->rowCount();
	//echo $num."<BR>";
	

	if($num < 1)  { //아닐경우
		$result = array("result" => "error", "Msg" => "발송할 푸시가 없습니다.");
	} else {
		$cnt = 0;
		while($row=$Stmt->fetch(PDO::FETCH_ASSOC)) {
			$mem_Idx =  $row['mem_Idx'];					// 회원고유번호
			$mCnt =  $row['mCnt'];								// 매칭성공푸시수
			$pCnt =  $row['pCnt'];								// 페널티부과푸시수
			$ppCnt =  $row['ppCnt'];								// 페널티부과푸시수
		if($cnt == 0){
			$result = array("result" => "error", "errorMsg" => "발송할 푸시가 없습니다.");
		}else{
			$result = array("result" => "success", "cnt" => $cnt ); // 각 API 성공값 부분
		}
	}

	dbClose($DB_con);
	$chkStmt = null;
	$Stmt = null;
	$Stmt1 = null;
	$insStmt = null;
	$memTokStmt = null;
	$memRTokStmt = null;
	$nSidStmt = null;
	$rSidStmt = null;
	$upPStmt = null;
	$upMStmt = null;
	$upMStmt2 = null;
	echo "
".str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
?>
