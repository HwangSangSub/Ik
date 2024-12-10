#!/usr/bin/php -q
<?php
/*======================================================================================================================

* 프로그램			: 캐릭터 매칭 king
* 페이지 설명		: 캐릭터 매칭 king
* 파일명				: charOrder.php

========================================================================================================================*/
function get_time() { $t=explode(' ',microtime()); return (float)$t[0]+(float)$t[1]; }
$start = get_time();
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


function debug($var) {
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function getMatMemberInfo($seller, $price, $char_Code) {
    global $DB_con;
    if($char_Code == "king"){
        $type = "kCnt";
    }else if($char_Code == "man"){
        $type = "mCnt";
    }else if($char_Code == "kid"){
        $type = "dCnt";
    }else if($char_Code == "baby"){
        $type = "bCnt";
    }
    //echo $char_Code ;
    //echo $type;
    //exit;
    /*
     * 
     *oSeed desc, : 번호표 정렬방식
     * 
     * */
    // 
	$query = "select idx , lSlot , maxSlot, minSlot , dMoney  from MAT_MEMBERS where dMoney >= :price and idx <> :seller and minSlot IS NOT NULL and maxSlot IS NOT NULL order by totCnt ASC, ".$type." asc limit 1  ";
	$stmt= $DB_con->prepare($query);
	$stmt->bindparam(":seller",$seller);
	$stmt->bindparam(":price",$price);
	$stmt->execute();
	$row=$stmt->fetch(PDO::FETCH_ASSOC);
	//echo $query;
	//exit;

	if($row) {
		return $row;
	}else {
 		$row["idx"] = 0;
		return $row;
	}

}

function getLastSlotNumber($buyer, $lSlot , $minSlot, $maxSlot  ) {
	global $DB_con;
    //마지막슬롯까지 넣고 다시 처음부터 리셋
	if($lSlot == $maxSlot){
	    $lSlot = 0;
	}
	$query = "select slot_Idx from  TB_MATCHING_SLOT where slot_Idx > :lSlot  AND mem_Idx = :buyer order by slot_Idx asc limit 1  ";
	$stmt= $DB_con->prepare($query);

	$stmt->bindparam(":buyer",$buyer);
	$stmt->bindparam(":lSlot",$lSlot);
	$stmt->execute();
	$row=$stmt->fetch(PDO::FETCH_ASSOC);
   // debug($row);
	if($row) {
		return $row['slot_Idx'];
	}else {
		return "0";
	}

}

function updateMatMemberDMoneyAndSlot($memIdx, $price, $slot, $char_Code) {
	global $DB_con;
	if($char_Code == "king"){
	    $type = "kCnt";
	}else if($char_Code == "man"){
	    $type = "mCnt";
	}else if($char_Code == "kid"){
	    $type = "dCnt";
	}else if($char_Code == "baby"){
	    $type = "bCnt";
	}
	$query = "UPDATE MAT_MEMBERS SET dMoney = :price, lSlot = :slot, ".$type." = ".$type." + 1, totCnt = totCnt +1 where idx = :memIdx";

	$stmt= $DB_con->prepare($query);
	$stmt->bindparam(":memIdx",$memIdx);
	$stmt->bindparam(":price",$price);
	$stmt->bindparam(":slot",$slot);
	$stmt->execute();
}

	$DB_con = db1();

    $reg_Date = date('Y-m-d H:i:s', time());	 //등록일
	$now_Time = $reg_Date;				//현재시간
	
	$chkBit = "0";
	$cntQuery = "
			SELECT COUNT(ml.order_Idx) as cnt
			FROM TB_MATCHING_LIST ml
				LEFT OUTER JOIN TB_CHARORDER_LIST col ON ml.order_Idx = col.idx
				LEFT OUTER JOIN MAT_MEMBERS m ON col.mem_SIdx = m.idx
			WHERE ml.mat_Bit ='N'
                AND ml.del_Bit IS NULL
               
	";
	// AND col.mem_SIdx NOT IN (21,28,44,61,76,89,95,422,431,895,924,1487)
	$cntStmt = $DB_con->prepare($cntQuery);
	$cntStmt->execute();
	$cntrow=$cntStmt->fetch(PDO::FETCH_ASSOC);
	$num =  $cntrow['cnt'];												// 판매대기건수
	if($num < 1)  { //아닐경우
		$result = array("result" => "error", "Msg" => "판매대기 상품이 없습니다.", "time" => $reg_Date);
	} else {
		$cnt = 0;
		$Query = "   
		SELECT ml.order_Idx, col.slot_SIdx, col.char_OrdNo, col.char_Code, col.char_Price, col.mem_SIdx as mem_Idx
		FROM TB_MATCHING_LIST ml
		LEFT OUTER JOIN TB_CHARORDER_LIST col ON ml.order_Idx = col.idx
		LEFT OUTER JOIN MAT_MEMBERS m ON col.mem_SIdx = m.idx
		WHERE ml.mat_Bit ='N'
		    ORDER BY RAND() 
		";
		/*  
         SELECT DISTINCT(m.idx) as mem_Idx, ml.order_Idx, col.slot_SIdx, col.char_OrdNo, col.char_Code, col.char_Price
         FROM TB_MATCHING_LIST ml
            LEFT OUTER JOIN TB_CHARORDER_LIST col ON ml.order_Idx = col.idx
            LEFT OUTER JOIN MAT_MEMBERS m ON col.mem_SIdx = m.idx
         WHERE ml.mat_Bit ='N'
            ORDER BY RAND();     */
		//ORDER BY col.char_Price ASC
		$Stmt = $DB_con->prepare($Query);
		$Stmt->execute();
		while($row=$Stmt->fetch(PDO::FETCH_ASSOC)) {
		    //echo $num;

			$order_Idx =  $row['order_Idx'];									// 주문고유번호
			$mem_SIdx = $row['mem_Idx'];							// 판매자고유번호
			$slot_SIdx =  $row['slot_SIdx'];							// 판매자슬롯고유번호
			$char_OrdNo =  $row['char_OrdNo'];						// 주문번호
			$char_Price =  $row['char_Price'];						// 캐릭터판매가
			$char_Code =  $row['char_Code'];						// 캐릭터코드
			//echo "order_Idx".$order_Idx;
			//echo "mem_SIdx".$mem_SIdx;
			//echo "slot_SIdx".$slot_SIdx;   
		    // 구매자 정보 가져오기
			$buyerInfo  = getMatMemberInfo($mem_SIdx, (int)$char_Price, $char_Code);
		
		    //debug($buyerInfo);

		    if($buyerInfo["idx"] == 0) {
			    echo "매칭 불가 1:$char_OrdNo , $char_Price, ($mem_SIdx) <br />";
		    }else{
		        
    			// idx , lSlot , maxSlot, minSlot
		        $buy_mIdx = $buyerInfo["idx"];         // 구매자 회원 고유번호
    		    $lSlot = $buyerInfo["lSlot"];              // 매칭에 사용된 마지막 슬롯고유번호
    		    $minSlot = $buyerInfo["minSlot"];     // 구매자 슬롯 중 최소 고유번호
    		    $maxSlot = $buyerInfo["maxSlot"];    // 구매자 슬롯 중 최대 고유번호
    		    $dMoney = $buyerInfo["dMoney"];    // 현재 남은 매칭 가능 

                //구매자 슬롯 고유번호 가져오기 
    			$slot_BIdx = getLastSlotNumber($buy_mIdx, $lSlot , $minSlot, $maxSlot);
    
    			if($slot_BIdx == 0 ){
    				echo "매칭 불가 2:$char_OrdNo , $char_Price <br />";
    				continue;
			     }
                 // 매칭가능금액 줄이기
			     $dMoney = $dMoney - (int)$char_Price;
                 // 맴버 temp 파일 반영하기( cnt, 매칭가능금액 )
			     updateMatMemberDMoneyAndSlot((int)$buy_mIdx, (int)$dMoney, (int)$slot_BIdx, $char_Code);
			     
		         // 매칭등록
		         $ordMatQuery = "
					UPDATE TB_MATCHING_LIST
					SET slot_BIdx = :slot_BIdx,
						mem_BIdx = :buy_mIdx,
						mat_Date = NOW(),
						mat_Bit = 'Y'
					WHERE order_Idx = :order_Idx
					LIMIT 1
				";
		         $ordMatstmt = $DB_con->prepare($ordMatQuery);
		         $ordMatstmt->bindparam(":slot_BIdx",$slot_BIdx);
		         $ordMatstmt->bindparam(":buy_mIdx",$buy_mIdx);
		         $ordMatstmt->bindparam(":order_Idx",$order_Idx);
		         $ordMatstmt->execute();
		         
		         $chkBit = "1";
		         $cnt++;

			}//구매자 while 끝
			
			if($chkBit == "1"){
				continue;
			}else{
			}

		}//판매자 while 끝
		if($cnt == 0){
			$result = array("result" => "error", "errorMsg" => "구매조건에 성립하는 구매자가 없습니다.", "time" => $reg_Date);
		}else{
			$result = array("result" => "success", "cnt" => $cnt, "time" => $reg_Date); // 각 API 성공값 부분
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

$end = get_time();
$time = $end - $start;
//echo number_format($time,6) . " 초 걸림";
?>