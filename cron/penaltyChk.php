#!/usr/bin/php -q
<?php
date_default_timezone_set('Asia/Seoul');
$nTime = date('H', time());	 //현재시간
$oTime = array("21");
if(in_array($nTime, $oTime)){
}else{
	//exit;
}
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
	
	$query = "SELECT idx FROM TB_MEMBERS WHERE b_Disply = 'N' AND bonsa_Bit = 'N' AND test_Bit = 'N' AND mem_Lv = '1' LIMIT 10";
	$stmt = $DB_con->prepare($query);
	$stmt->execute();
	$num = $stmt->rowCount();
	if($num < 1)  { //아닐경우
		$result = array("result" => "error", "erroMsg" => "회원이 없습니다.");
	} else {
	    $cnt = 0;
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	        $mem_Idx = $row['idx'];
	        
	        // 캐릭터 보유 수
	        $charQuery = "SELECT COUNT(idx) as charCnt FROM TB_CHARACTER_LIST WHERE slot_Idx IN (SELECT idx FROM TB_MEMBERS_SLOT WHERE mem_Idx = :mem_Idx) AND del_Bit ='N'";
	        $charStmt = $DB_con->prepare($charQuery);
	        $charStmt->bindparam(":mem_Idx",$mem_Idx);
	        $charStmt->execute();
	        $charRow = $charStmt->fetch(PDO::FETCH_ASSOC);
	        $charCnt = $charRow['charCnt'];
	        
	        // 자동예약 중 슬롯 보유 수
	        $slotQuery = "SELECT COUNT(idx) as slotCnt FROM TB_MEMBERS_SLOT WHERE mem_Idx = :mem_Idx AND rea_Auto = 'Y' ";
	        $slotStmt = $DB_con->prepare($slotQuery);
	        $slotStmt->bindparam(":mem_Idx",$mem_Idx);
	        $slotStmt->execute();
	        $slotRow = $slotStmt->fetch(PDO::FETCH_ASSOC);
	        $slotCnt = $slotRow['slotCnt'];
	        
	        // 비율 = 슬롯수 / 캐릭터수
	        if($charCnt == "0"){
	            $penaltySlotSelQuery = "SELECT idx FROM TB_SLOT_PENALTY WHERE mem_Idx = :mem_Idx AND status = 'Y' ";
	            $psslStmt = $DB_con->prepare($penaltySlotSelQuery);
	            $psslStmt->bindparam(":mem_Idx",$mem_Idx);
	            $psslStmt->execute();
	            $psslNum = $psslStmt->rowCount();
	            if($psslNum < 1)  {
	            }else{
	                $psslRow = $psslStmt->fetch(PDO::FETCH_ASSOC);
	                $pssl_Idx = $psslRow['idx'];
	                $psuQuery = "UPDATE TB_SLOT_PENALTY SET status = 'N' WHERE idx = :pssl_Idx LIMIT 1";
	                $psuStmt = $DB_con->prepare($psuQuery);
	                $psuStmt->bindparam(":pssl_Idx",$pssl_Idx);
	                $psuStmt->execute();
	            }
	        }else{
	            if($slotCnt == "50"){
	                $penaltySlotSelQuery = "SELECT idx FROM TB_SLOT_PENALTY WHERE mem_Idx = :mem_Idx AND status = 'Y' ";
	                $psslStmt = $DB_con->prepare($penaltySlotSelQuery);
	                $psslStmt->bindparam(":mem_Idx",$mem_Idx);
	                $psslStmt->execute();
	                $psslNum = $psslStmt->rowCount();
	                if($psslNum < 1)  {
	                }else{
	                    $psslRow = $psslStmt->fetch(PDO::FETCH_ASSOC);
	                    $pssl_Idx = $psslRow['idx'];
	                    $psuQuery = "UPDATE TB_SLOT_PENALTY SET status = 'N' WHERE idx = :pssl_Idx LIMIT 1";
	                    $psuStmt = $DB_con->prepare($psuQuery);
	                    $psuStmt->bindparam(":pssl_Idx",$pssl_Idx);
	                    $psuStmt->execute();
	                }
	            }else{
	                $ratio = $slotCnt / $charCnt;
	                if($ratio < 1){
	                    $penaltySlotSelQuery = "SELECT idx, sDate, tDate FROM TB_SLOT_PENALTY WHERE mem_Idx = :mem_Idx AND status = 'Y' ";
	                    $psslStmt = $DB_con->prepare($penaltySlotSelQuery);
	                    $psslStmt->bindparam(":mem_Idx",$mem_Idx);
	                    $psslStmt->execute();
	                    $psslNum = $psslStmt->rowCount();
	                    if($psslNum < 1)  {  //없을경우 인설트
	                        $psiQuery = "INSERT INTO TB_SLOT_PENALTY (mem_Idx, fDate) VALUES(:mem_Idx, NOW())";
	                        $psiStmt = $DB_con->prepare($psiQuery);
	                        $psiStmt->bindparam(":mem_Idx",$mem_Idx);
	                        $psiStmt->execute();
	                    }else{
	                        $psslRow = $psslStmt->fetch(PDO::FETCH_ASSOC);
	                        $pssl_Idx = $psslRow['idx'];
	                        $pssl_sDate = $psslRow['sDate'];
	                        $pssl_tDate = $psslRow['tDate'];
	                        if($pssl_sDate == ""){
	                            $psiQuery = "UPDATE TB_SLOT_PENALTY SET sDate = NOW() WHERE idx = :pssl_Idx LIMIT 1";
	                            $psiStmt = $DB_con->prepare($psiQuery);
	                            $psiStmt->bindparam(":pssl_Idx",$pssl_Idx);
	                            $psiStmt->execute();
	                        }else if($pssl_tDate == ""){
	                            $psuQuery = "UPDATE TB_SLOT_PENALTY SET tDate = NOW() WHERE idx = :pssl_Idx LIMIT 1";
	                            $psuStmt = $DB_con->prepare($psuQuery);
	                            $psuStmt->bindparam(":pssl_Idx",$pssl_Idx);
	                            $psuStmt->execute();
	                            
	                            $memUpQuery = "UPDATE TB_MEMBERS SET penalty_Bit = 'Y', penalty_Date = NOW() WHERE idx = :mem_Idx LIMIT 1";
	                            $memUpStmt = $DB_con->prepare($memUpQuery);
	                            $memUpStmt->bindparam(":mem_Idx",$mem_Idx);
	                            $memUpStmt->execute();
	                            $cnt++;
	                        }
	                    }
	                }else{
	                    $penaltySlotSelQuery = "SELECT idx FROM TB_SLOT_PENALTY WHERE mem_Idx = :mem_Idx AND status = 'Y' ";
	                    $psslStmt = $DB_con->prepare($penaltySlotSelQuery);
	                    $psslStmt->bindparam(":mem_Idx",$mem_Idx);
	                    $psslStmt->execute();
	                    $psslNum = $psslStmt->rowCount();
	                    if($psslNum < 1)  { 
	                    }else{
	                        $psslRow = $psslStmt->fetch(PDO::FETCH_ASSOC);
	                        $pssl_Idx = $psslRow['idx'];
	                        $psuQuery = "UPDATE TB_SLOT_PENALTY SET status = 'N' WHERE idx = :pssl_Idx LIMIT 1";
	                        $psuStmt = $DB_con->prepare($psuQuery);
                            $psuStmt->bindparam(":pssl_Idx",$pssl_Idx);
                            $psuStmt->execute();
	                    }
	                }
	            }
	        }
	    }
	    $result = array("result" => "success", "cnt" => $cnt, "procDate" => $now_Time);
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
