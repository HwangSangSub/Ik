#!/usr/bin/php -q
<?php
date_default_timezone_set('Asia/Seoul');
/*======================================================================================================================

* 프로그램			: 탈퇴회원정리
* 페이지 설명		: 탈퇴회원정리
* 파일명				: withdrawalProc.php

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
		SELECT idx
		FROM TB_MEMBERS
		WHERE b_Disply = 'Y'
			back_Bit = 'N';
	";
	$Stmt = $DB_con->prepare($Query);
	$Stmt->execute();
	$num = $Stmt->rowCount();
	//echo $num."<BR>";
	

	if($num < 1)  { //아닐경우
		$result = array("result" => "error", "Msg" => "거래불가 캐릭터가 없습니다.");
	} else {
		$cnt = 0;
		while($row=$Stmt->fetch(PDO::FETCH_ASSOC)) {
			$mIdx =  $row['idx'];										// 인형고유번호

			$memUpQuery = "
				UPDATE TB_MEMBERS
				SET back_Bit = 'Y'
				WHERE idx = :mIdx
				LIMIT 1;
			";
			$memUpStmt = $DB_con->prepare($memUpQuery);
			$memUpStmt->bindParam(":mIdx", $mIdx);
			$memUpStmt->execute();

			//그외 데이터 삭제.
			//TB_MEMBERS_ETC 삭제
			$etcDelQuery = "
				DELEFT FROM TB_MEMBERS_ETC
				WHERE mem_Idx = :mIdx
				LIMIT 1;
			";
			$etcDelStmt = $DB_con->prepare($etcDelQuery);
			$etcDelStmt->bindParam(":mIdx", $mIdx);
			$etcDelStmt->execute();

			//TB_MEMBERS_SLOT 삭제
			$slotSelQuery = "
				SELECT idx
				FROM TB_MEMBERS_SLOT
				WHERE mem_Idx = :mIdx
				;
			";
			$slotSelStmt = $DB_con->prepare($slotSelQuery);
			$slotSelStmt->bindParam(":mIdx", $mIdx);
			$slotSelStmt->execute();
			while($slotSelRow = $slotSelStmt->fetch(PDO::FETCH_ASSOC)){
				$slotIdx = $slotSelRow['idx'];		// 슬롯 고유번호
				$slotDelQuery = "
					DELEFT FROM TB_MEMBERS_SLOT
					WHERE idx = :slotIdx
					LIMIT 1;
				";
				$slotDelStmt = $DB_con->prepare($slotDelQuery);
				$slotDelStmt->bindParam(":slotIdx", $slotIdx);
				$slotDelStmt->execute();
			}

			//TB_PUSH_HISTORY 삭제
			$pushSelQuery = "
				SELECT idx
				FROM TB_PUSH_HISTORY
				WHERE mem_Idx = :mIdx
				;
			";
			$pushSelStmt = $DB_con->prepare($pushSelQuery);
			$pushSelStmt->bindParam(":mIdx", $mIdx);
			$pushSelStmt->execute();
			while($pushSelRow = $pushSelStmt->fetch(PDO::FETCH_ASSOC)){
				$pushIdx = $pushSelRow['idx'];		// 슬롯 고유번호
				$pushDelQuery = "
					DELEFT FROM TB_PUSH_HISTORY
					WHERE idx = :pushtIdx
					LIMIT 1;
				";
				$pushDelStmt = $DB_con->prepare($pushDelQuery);
				$pushDelStmt->bindParam(":pushtIdx", $pushIdx);
				$pushDelStmt->execute();
			}

			//TB_USEPORINT_LIST 삭제
			$pointSelQuery = "
				SELECT idx
				FROM TB_PUSH_HISTORY
				WHERE mem_Idx = :mIdx
				;
			";
			$pointSelStmt = $DB_con->prepare($pointSelQuery);
			$pointSelStmt->bindParam(":mIdx", $mIdx);
			$pointSelStmt->execute();
			while($pointSelRow = $pointSelStmt->fetch(PDO::FETCH_ASSOC)){
				$pointIdx = $pointSelRow['idx'];		// 슬롯 고유번호
				$pointDelQuery = "
					DELEFT FROM TB_USEPOINT_LIST
					WHERE idx = :pointIdx
					LIMIT 1;
				";
				$pointDelStmt = $DB_con->prepare($pointDelQuery);
				$pointDelStmt->bindParam(":pointIdx", $pointIdx);
				$pointDelStmt->execute();
			}

			//TB_HISTORY 삭제
			$historySelQuery = "
				SELECT idx
				FROM TB_HISTORY
				WHERE mem_Idx = :mIdx
				;
			";
			$historySelStmt = $DB_con->prepare($historySelQuery);
			$historySelStmt->bindParam(":mIdx", $mIdx);
			$historySelStmt->execute();
			while($historySelRow = $historySelStmt->fetch(PDO::FETCH_ASSOC)){
				$historyIdx = $historySelRow['idx'];		// 슬롯 고유번호
				$historyDelQuery = "
					DELEFT FROM TB_HISTORY
					WHERE idx = :historyIdx
					LIMIT 1;
				";
				$historyDelStmt = $DB_con->prepare($historyDelQuery);
				$historyDelStmt->bindParam(":historyIdx", $historyIdx);
				$historyDelStmt->execute();
			}

			//TB_DIATRADE_LIST 삭제
			$diatradeSelQuery = "
				SELECT idx
				FROM TB_DIATRADE_LIST
				WHERE (mem_Idx = :mem_Idx OR mem_SIdx = :mem_SIdx)
				;
			";
			$diatradeSelStmt = $DB_con->prepare($diatradeSelQuery);
			$diatradeSelStmt->bindParam(":mem_Idx", $mIdx);
			$diatradeSelStmt->bindParam(":mem_SIdx", $mIdx);
			$diatradeSelStmt->execute();
			while($diatradeSelRow = $diatradeSelStmt->fetch(PDO::FETCH_ASSOC)){
				$diatradeIdx = $diatradeSelRow['idx'];		// 슬롯 고유번호
				$diatradeDelQuery = "
					DELEFT FROM TB_DIATRADE_LIST
					WHERE idx = :diatradeIdx
					LIMIT 1;
				";
				$diatradeDelStmt = $DB_con->prepare($diatradeDelQuery);
				$diatradeDelStmt->bindParam(":diatradeIdx", $diatradeIdx);
				$diatradeDelStmt->execute();
			}
			//TB_DIAORDER_LIST 삭제
			$diaorderSelQuery = "
				SELECT idx
				FROM TB_DIAORDER_LIST
				WHERE mem_Idx = :mIdx
				;
			";
			$diaorderSelStmt = $DB_con->prepare($diaorderSelQuery);
			$diaorderSelStmt->bindParam(":mIdx", $mIdx);
			$diaorderSelStmt->execute();
			while($diaorderSelRow = $diaorderSelStmt->fetch(PDO::FETCH_ASSOC)){
				$diaorderIdx = $diaorderSelRow['idx'];		// 슬롯 고유번호
				$diaorderDelQuery = "
					DELEFT FROM TB_DIAORDER_LIST
					WHERE idx = :diaorderIdx
					LIMIT 1;
				";
				$diaorderDelStmt = $DB_con->prepare($diaorderDelQuery);
				$diaorderDelStmt->bindParam(":diaorderIdx", $diaorderIdx);
				$diaorderDelStmt->execute();
			}

			//TB_CHARORDER_LIST 삭제
			$charorderSelQuery = "
				SELECT idx
				FROM TB_CHARORDER_LIST
				WHERE (mem_SIdx = :mem_SIdx OR mem_BIdx = :mem_BIdx)
				;
			";
			$charorderSelStmt = $DB_con->prepare($charorderSelQuery);
			$charorderSelStmt->bindParam(":mem_SIdx", $mIdx);
			$charorderSelStmt->bindParam(":mem_BIdx", $mIdx);
			$charorderSelStmt->execute();
			while($charorderSelRow = $charorderSelStmt->fetch(PDO::FETCH_ASSOC)){
				$charorderIdx = $charorderSelRow['idx'];		// 슬롯 고유번호
				$charorderDelQuery = "
					DELEFT FROM TB_CHARORDER_LIST
					WHERE idx = :charorderIdx
					LIMIT 1;
				";
				$charorderDelStmt = $DB_con->prepare($charorderIdx);
				$charorderDelStmt->bindParam(":charorderIdx", $charorderIdx);
				$charorderDelStmt->execute();
			}

			//TB_WAITING_LIST 삭제
			$waitingSelQuery = "
				SELECT idx
				FROM TB_WAITING_LIST
				WHERE mem_Idx = :mIdx
				;
			";
			$waitingSelStmt = $DB_con->prepare($waitingSelQuery);
			$waitingSelStmt->bindParam(":mIdx", $mIdx);
			$waitingSelStmt->execute();
			while($waitingSelRow = $waitingSelStmt->fetch(PDO::FETCH_ASSOC)){
				$waitingIdx = $waitingSelRow['idx'];		// 슬롯 고유번호
				$waitingDelQuery = "
					DELEFT FROM TB_WAITING_LIST
					WHERE idx = :waitingIdx
					LIMIT 1;
				";
				$waitingDelStmt = $DB_con->prepare($waitingIdx);
				$waitingDelStmt->bindParam(":waitingIdx", $waitingIdx);
				$waitingDelStmt->execute();
			}
			$cnt++;
		}//while 끝
		if($cnt == 0){
			$result = array("result" => "error", "errorMsg" => "조건에 만족하는 탈퇴회원이 없습니다.");
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
