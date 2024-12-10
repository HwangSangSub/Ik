#!/usr/bin/php -q
<?php
date_default_timezone_set('Asia/Seoul');
/*======================================================================================================================

* 프로그램			: 신규회원 첫 매칭 이후 패널티가 없을 경우 설정금액의 10%의 다이아를 추천인에게 지급.
* 페이지 설명		: 신규회원 첫 매칭 이후 패널티가 없을 경우 설정금액의 10%의 다이아를 추천인에게 지급.
* 파일명				: memFmat.php

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
//하루전 매칭일을 조회해서 지급하기.
$memQuery = "SELECT idx, mem_Code, matF_Date FROM TB_MEMBERS WHERE mem_Code IS NOT NULL AND DATE_FORMAT(matF_Date, '%Y-%m-%d') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') AND matF_Bit = 'Y' AND reward_Bit = 'N'";
$memStmt = $DB_con->prepare($memQuery);
$memStmt->execute();
$memNum = $memStmt->rowCount();
if($memNum < 1){
    $result = array("result" => "error", "errorMsg" => "첫매칭이후 리워드 지급대기상태인 회원이 없습니다.");
}else{
    // echo "페널티대상 : ".$memNum;
    while($memRow = $memStmt->fetch(PDO::FETCH_ASSOC)) {
        $mem_Idx = $memRow['idx'];				        // 회원고유번호
        $matF_Date = $memRow['matF_Date'];		    // 첫매칭일
        $mem_Code = $memRow['mem_Code'];		// 추천인코드
        $matFDate = substr($matF_Date,0,10);          // 판매일(시간제거)
        
        $selCharQuery = "SELECT idx FROM TB_CHARORDER_LIST WHERE mem_BIdx = :mem_BIdx AND DATE_FORMAT(mat_Date, '%Y-%m-%d') = :mat_Date AND char_OrdState = '6'";
        $selCharStmt = $DB_con->prepare($selCharQuery);
        $selCharStmt->bindparam(":mem_BIdx",$mem_Idx);
        $selCharStmt->bindparam(":mat_Date",$matFDate);
        $selCharStmt->execute();
        $selCharNum = $selCharStmt->rowCount();
        // 미입금이 없다면 리워드 지급 없으면 리워드 지급 안함 기회없어짐.
        if($selCharNum < 1){
            // 설정금액 계산하기 
            $memEtcChkQuery = "SELECT mem_Money FROM TB_MEMBERS_ETC WHERE mem_Idx = :mem_Idx";
            $memEtcChkStmt = $DB_con->prepare($memEtcChkQuery);
            $memEtcChkStmt->bindparam(":mem_Idx",$mem_Idx);
            $memEtcChkStmt->execute();
            $memEtcChkRow = $memEtcChkStmt->fetch(PDO::FETCH_ASSOC);
            $mem_Money = $memEtcChkRow['mem_Money'];		    // 설정금액
            $memAddDia =  ROUND(($mem_Money * 0.1) / 100);  //지급받을 다이아
            
            //추천인 정보 확인.
            $memCodeQuery = "SELECT idx FROM TB_MEMBERS WHERE mem_NickNm = :mem_Code";
            $memCodeStmt = $DB_con->prepare($memCodeQuery);
            $memCodeStmt->bindparam(":mem_Code",$mem_Code);
            $memCodeStmt->execute();
            $memCodeRow = $memCodeStmt->fetch(PDO::FETCH_ASSOC);
            $memCIdx = $memCodeRow['idx'];		    // 추천인고유번호
            
            $memEtcUpQuery = "UPDATE TB_MEMBERS_ETC SET mem_Dia = mem_Dia + ".$memAddDia." WHERE mem _Idx = :mem_Idx";
            $memEtcUpStmt = $DB_con->prepare($memEtcUpQuery);
            $memEtcUpStmt->bindparam(":mem_Idx",$memCIdx);
            $memEtcUpStmt->execute();
            
            // 다이아내역등록
            $diaTquery = "
				INSERT INTO TB_DIATRADE_LIST (mem_Idx, add_Dia, reg_Date)
				VALUES (:mem_Idx, :add_Dia, NOW());
			";
            $diaTstmt = $DB_con->prepare($diaTquery);
            $diaTstmt->bindparam(":mem_Idx",$memCIdx);
            $diaTstmt->bindparam(":add_Dia",$memAddDia);
            $diaTstmt->execute();
            $dtIdx = $DB_con->lastInsertId();  //저장된 idx 값
            
            // 히스토리등록
            $history = "리워드지급(첫매칭)";
            $hisQuery = "
				INSERT INTO TB_HISTORY (mem_Idx, dia_Idx, history, reg_Date)
				VALUES (:mem_Idx, :dia_Idx, :history, NOW());
			";
            $hisstmt = $DB_con->prepare($hisQuery);
            $hisstmt->bindparam(":mem_Idx",$memCIdx);
            $hisstmt->bindparam(":dia_Idx",$dtIdx);
            $hisstmt->bindparam(":history",$history);
            $hisstmt->execute();
            
            //보상받은 비트값 추가하기
            $memUpQuery = "UPDATE TB_MEMBERS SET reward_Bit = 'Y' WHERE mem_Idx = :mem_Idx LIMIT 1";
            $memUpStmt = $DB_con->prepare($memUpQuery);
            $memUpStmt->bindparam(":mem_Idx",$mem_Idx);
            $memUpStmt->execute();
            
        }else{
            //패널티 받은 회원은 리워드 보상 받지 못함.
            $memUpQuery = "UPDATE TB_MEMBERS SET reward_Bit = 'Y' WHERE mem_Idx = :mem_Idx LIMIT 1";
            $memUpStmt = $DB_con->prepare($memUpQuery);
            $memUpStmt->bindparam(":mem_Idx",$mem_Idx);
            $memUpStmt->execute();
        }
    }
    $result = array("result" => "success");
}

dbClose($DB_con);
$Stmt = null;
$memDiaUpStmt = null;
echo "
".str_replace('\\/', '/', json_encode($result, JSON_UNESCAPED_UNICODE));
?>
