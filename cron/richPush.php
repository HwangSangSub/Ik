#!/usr/bin/php -q
<?php
date_default_timezone_set('Asia/Seoul');
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
            AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
		GROUP BY mem_Idx;
	";
	//echo $Query1."<BR>";
	//exit;
	$Stmt = $DB_con->prepare($Query);
	$Stmt->execute();
	$num = $Stmt->rowCount();
	//echo $num."<BR>";
	

	if($num < 1)  { //아닐경우
		$result = array("result" => "error", "Msg" => "발송할 푸시가 없습니다.", "time" => $reg_Date);
	} else {
		$cnt = 0;
		while($row=$Stmt->fetch(PDO::FETCH_ASSOC)) {
			$mem_Idx =  $row['mem_Idx'];					// 회원고유번호
			$mCnt =  $row['mCnt'];								// 매칭성공푸시수
			$pCnt =  $row['pCnt'];								// 페널티부과푸시수
			$ppCnt =  $row['ppCnt'];								// 페널티부과푸시수
			if((int)$mCnt > 0){
				$orderPquery = "
					SELECT idx
					FROM TB_ORDER_PROC 
					WHERE DATE_FORMAT(proc_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
						AND use_Bit = 'Y'
					LIMIT 1;
				";
				$orderPStmt = $DB_con->prepare($orderPquery);
				$orderPStmt->execute();
				$orderPnum = $orderPStmt->rowCount();
				if($orderPnum > 0){
					$msgCquery = "
						SELECT message
						FROM TB_PUSH_HISTORY 
						WHERE mem_Idx = :mem_Idx
                            AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
							AND push_Bit = 'N'
							AND type = '1'
						LIMIT 1;
					";
					$msgCStmt = $DB_con->prepare($msgCquery);
					$msgCStmt->bindparam(":mem_Idx",$mem_Idx);
					$msgCStmt->execute();
					$msgCrow=$msgCStmt->fetch(PDO::FETCH_ASSOC);
					$mmessage =  $msgCrow['message'];					// 회원고유번호
					if($mCnt <2){
						$nmsg = $mmessage;
					}else{
						$nmsg = $mmessage."(외 ".((int)$mCnt - 1)."건)";
					}

					$msgUquery = "
						UPDATE TB_PUSH_HISTORY 
						SET push_Bit = 'Y',
                            see_Bit = 'Y',
							reg_Date = NOW(),
                            send_Date = NOW()
						WHERE mem_Idx = :mem_Idx
                            AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
							AND push_Bit = 'N'
							AND type = '1'
						LIMIT ".$mCnt.";
					";
					$msgUStmt = $DB_con->prepare($msgUquery);
					$msgUStmt->bindparam(":mem_Idx",$mem_Idx);
					$msgUStmt->execute();

					$memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mIdx AND b_Disply IN ('N', 'P')" ;
					$memTokStmt = $DB_con->prepare($memTokQuery);
					$memTokStmt->bindparam(":mIdx",$mem_Idx);
					$memTokStmt->execute();
					$memTokNum = $memTokStmt->rowCount();
					if($memTokNum < 1)  { //주 ID가 없을 경우 회원가입 시작
					} else {  //등록된 회원이 있을 경우
						while($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
							$mem_NToken = $memTokRow["mem_Token"];//토큰값
						}
					}

					
					//회원 고유 아이디
					$nSidQuery = "SELECT mem_Os, mem_MPush, mem_Token from TB_MEMBERS WHERE idx = :mIdx AND b_Disply IN ('N', 'P') " ;
					$nSidStmt = $DB_con->prepare($nSidQuery);
					$nSidStmt->bindparam(":mIdx",$mem_Idx);
					$nSidStmt->execute();
					$nSidNum = $nSidStmt->rowCount();
					
					if($nSidNum < 1)  { //아닐경우
					} else {
						
						while($nSidRow=$nSidStmt->fetch(PDO::FETCH_ASSOC)) {
							
							$nmemOs = $nSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
							$nmemMPush = $nSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)
							
							$chkState = "5";  //매칭성공시 구매자에게 입금요청
							

							if ($nmemOs != "" ) { //os가 있을 경우
								if ($nmemMPush == "0") { //푸시 수신 가능
									$ntitle = "리치";
									$nmsg = $nmsg;
								} else {
									$ntitle = "";
									$nmsg = "";
								}
												
								$ntokens = $mem_NToken;
								
								//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
								$ninputData = array("title" => $ntitle, "body" => $nmsg, "state" => $chkState);
								
								//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
								if ($nmemOs == "0") { //안드로이드
									/*if ($nmemMPush == "0") {
										$nresult = send_AnPush($ntokens, $ninputData);
									}*/
									$pushUrl = "https://fcm.googleapis.com/fcm/send";
									$headers = [];
									$headers[] = 'Content-Type: application/json';
									$headers[] = 'Authorization:key=' . GOOGLE_API_KEY;
									
									$data = array(
										"data" => array(
											'title'	=> $ninputData["title"],
											'body' 	=> $ninputData["body"],
											'chkState'  => $ninputData["state"], //상태 리턴
										),
										"to"  => $ntokens,//token get on my ipad with the getToken method of cordova plugin,
										
									);
									
									//$json_data = json_encode($data);
									$json_data =  json_encode($data, JSON_UNESCAPED_UNICODE);
									//print_r($json_data);
									
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $pushUrl);
									curl_setopt($ch, CURLOPT_POST, true );
									curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
									curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
									
									$result = curl_exec($ch);
									
									if ($result === FALSE) {
										die('Curl failed: ' . curl_error($ch));
									}
									curl_close($ch);
									
									sleep(1);
								} else {
									//$nresult = send_IosPush($ntokens, $ninputData);
													
									$url = "https://fcm.googleapis.com/fcm/send";
									$registrationIds = array($ntokens);
									$serverKey = GOOGLE_API_KEY;
									
									$title = $ninputData["title"];
									$body = $ninputData["body"];
									$taxiState = $ninputData["state"]; //상태 리턴
									
									if ($title == "" && $body == "") { //제목, 메시지가 없을 때 없앰.
										$notification = array('content_available' => 'true', 'title' => '', 'body' => '', 'icon' => 'fcm_push_icon', 'sound' => '');
									} else {
										$notification = array('title' => $title , 'body' => $body, 'icon' => 'fcm_push_icon', 'sound' => 'default');
									}
									
									$arrayToSend = array(
										'registration_ids' => $registrationIds,
										'notification'=> $notification,
										"data" => array(
											'chkState'  => $taxiState, //상태 리턴
										),
										'priority'=>'high'
									);
									
									//$json = json_encode($arrayToSend);
									$json =  json_encode($arrayToSend, JSON_UNESCAPED_UNICODE);
									//print_r($json)."<BR>";
									
									$headers = array();
									$headers[] = 'Content-Type: application/json';
									$headers[] = 'Authorization: key='. $serverKey;
									
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_URL, $url);
									curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
									curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
									curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									
									//Send the request
									$result = curl_exec($ch);
									if ($result === FALSE)
									{
										die('FCM Send Error: ' . curl_error($ch));
									}
									
									curl_close( $ch );
									
									sleep(1);
									
									//return $result;
									
									
								}
								
							}
							
						}
					}
				}//승인한 이후 푸시 발송
			}//매칭성공푸시 종료

			if((int)$pCnt > 0){
				$pmsgCquery = "
					SELECT message
					FROM TB_PUSH_HISTORY 
					WHERE mem_Idx = :mem_Idx
                        AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
						AND push_Bit = 'N'
						AND type = '2'
					LIMIT 1;
				";
				$pmsgCStmt = $DB_con->prepare($pmsgCquery);
				$pmsgCStmt->bindparam(":mem_Idx",$mem_Idx);
				$pmsgCStmt->execute();
				$pmsgCrow=$pmsgCStmt->fetch(PDO::FETCH_ASSOC);
				$pmessage =  $pmsgCrow['message'];					// 회원고유번호
				if($pCnt <2){
					$pnmsg = $pmessage;
				}else{
					$pnmsg = $pmessage."(외 ".((int)$pCnt - 1)."건)";
				}

				$pmsgUquery = "
					UPDATE TB_PUSH_HISTORY 
					SET push_Bit = 'Y',
                            send_Date = NOW()
					WHERE mem_Idx = :mem_Idx
                        AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
						AND push_Bit = 'N'
						AND type = '2'
					LIMIT ".$pCnt.";
				";
				$pmsgUStmt = $DB_con->prepare($pmsgUquery);
				$pmsgUStmt->bindparam(":mem_Idx",$mem_Idx);
				$pmsgUStmt->execute();

				$pmemTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mIdx AND b_Disply IN ('N', 'P')" ;
				$pmemTokStmt = $DB_con->prepare($pmemTokQuery);
				$pmemTokStmt->bindparam(":mIdx",$mem_Idx);
				$pmemTokStmt->execute();
				$pmemTokNum = $pmemTokStmt->rowCount();
				if($pmemTokNum < 1)  { //주 ID가 없을 경우 회원가입 시작
				} else {  //등록된 회원이 있을 경우
					while($pmemTokRow = $pmemTokStmt->fetch(PDO::FETCH_ASSOC)) {
						$pmem_NToken = $pmemTokRow["mem_Token"];//토큰값
					}
				}

				
				//회원 고유 아이디
				$pnSidQuery = "SELECT mem_Os, mem_MPush, mem_Token from TB_MEMBERS WHERE idx = :mIdx AND b_Disply IN ('N', 'P') " ;
				$pnSidStmt = $DB_con->prepare($pnSidQuery);
				$pnSidStmt->bindparam(":mIdx",$mem_Idx);
				$pnSidStmt->execute();
				$pnSidNum = $pnSidStmt->rowCount();
				
				if($pnSidNum < 1)  { //아닐경우
				} else {
					
					while($pnSidRow=$pnSidStmt->fetch(PDO::FETCH_ASSOC)) {
						
						$pnmemOs = $pnSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
						$pnmemMPush = $pnSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)
						
						$pchkState = "16";  //페널티부과시 구매자에게 입금요청
						

						if ($pnmemOs != "" ) { //os가 있을 경우
							if ($pnmemMPush == "0") { //푸시 수신 가능
								$pntitle = "리치";
								$pnmsg = $pnmsg;
							} else {
								$pntitle = "";
								$pnmsg = "";
							}

							$pntokens = $pmem_NToken;
							
							//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
							$pninputData = array("title" => $pntitle, "body" => $pnmsg, "state" => $pchkState);
							
							//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
							if ($pnmemOs == "0") { //안드로이드
								/*if ($nmemMPush == "0") {
									$nresult = send_AnPush($ntokens, $ninputData);
								}*/
								$ppushUrl = "https://fcm.googleapis.com/fcm/send";
								$pheaders = [];
								$pheaders[] = 'Content-Type: application/json';
								$pheaders[] = 'Authorization:key=' . GOOGLE_API_KEY;
								
								$pdata = array(
									"data" => array(
										'title'	=> $pninputData["title"],
										'body' 	=> $pninputData["body"],
										'chkState'  => $pninputData["state"], //상태 리턴
									),
									"to"  => $pntokens,//token get on my ipad with the getToken method of cordova plugin,
									
								);
								
								//$json_data = json_encode($data);
								$pjson_data =  json_encode($pdata, JSON_UNESCAPED_UNICODE);
								//print_r($json_data);
								
								$pch = curl_init();
								curl_setopt($pch, CURLOPT_URL, $ppushUrl);
								curl_setopt($pch, CURLOPT_POST, true );
								curl_setopt($pch, CURLOPT_HTTPHEADER, $pheaders);
								curl_setopt($pch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($pch, CURLOPT_SSL_VERIFYPEER, false );
								curl_setopt($pch, CURLOPT_POSTFIELDS, $pjson_data);
								
								$presult = curl_exec($pch);
								
								if ($presult === FALSE) {
									die('Curl failed: ' . curl_error($pch));
								}
								curl_close($pch);
								
								sleep(1);
							} else {
								//$nresult = send_IosPush($ntokens, $ninputData);
												
								$purl = "https://fcm.googleapis.com/fcm/send";
								$pregistrationIds = array($pntokens);
								$pserverKey = GOOGLE_API_KEY;
								
								$ptitle = $pninputData["title"];
								$pbody = $pninputData["body"];
								$ptaxiState = $pninputData["state"]; //상태 리턴
								
								if ($ptitle == "" && $pbody == "") { //제목, 메시지가 없을 때 없앰.
									$pnotification = array('content_available' => 'true', 'title' => '', 'body' => '', 'icon' => 'fcm_push_icon', 'sound' => '');
								} else {
									$pnotification = array('title' => $ptitle , 'body' => $pbody, 'icon' => 'fcm_push_icon', 'sound' => 'default');
								}
								
								$parrayToSend = array(
									'registration_ids' => $pregistrationIds,
									'notification'=> $pnotification,
									"data" => array(
										'chkState'  => $ptaxiState, //상태 리턴
									),
									'priority'=>'high'
								);
								
								//$json = json_encode($arrayToSend);
								$pjson =  json_encode($parrayToSend, JSON_UNESCAPED_UNICODE);
								//print_r($json)."<BR>";
								
								$pheaders = array();
								$pheaders[] = 'Content-Type: application/json';
								$pheaders[] = 'Authorization: key='. $pserverKey;
								
								$pch = curl_init();
								curl_setopt($pch, CURLOPT_URL, $purl);
								curl_setopt($pch, CURLOPT_CUSTOMREQUEST,"POST");
								curl_setopt($pch, CURLOPT_POSTFIELDS, $pjson);
								curl_setopt($pch, CURLOPT_HTTPHEADER,$pheaders);
								curl_setopt($pch, CURLOPT_RETURNTRANSFER, true);
								
								//Send the request
								$presult = curl_exec($pch);
								if ($presult === FALSE)
								{
									die('FCM Send Error: ' . curl_error($pch));
								}
								
								curl_close( $pch );
								
								sleep(1);
								
								//return $result;
								
								
							}
							
						}
						
					}
				}
			}//페널티부과푸시 종료
			if((int)$ppCnt > 0){
				$ppmsgCquery = "
					SELECT message
					FROM TB_PUSH_HISTORY 
					WHERE mem_Idx = :mem_Idx
                        AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
						AND push_Bit = 'N'
						AND type = '4'
					LIMIT 1;
				";
				$ppmsgCStmt = $DB_con->prepare($ppmsgCquery);
				$ppmsgCStmt->bindparam(":mem_Idx",$mem_Idx);
				$ppmsgCStmt->execute();
				$ppmsgCrow=$ppmsgCStmt->fetch(PDO::FETCH_ASSOC);
				$ppmessage =  $ppmsgCrow['message'];					// 회원고유번호
				if($ppCnt <2){
					$ppnmsg = $ppmessage;
				}else{
					$ppnmsg = $ppmessage."(외 ".((int)$ppCnt - 1)."건)";
				}

				$ppmsgUquery = "
					UPDATE TB_PUSH_HISTORY 
					SET push_Bit = 'Y',
                            send_Date = NOW()
					WHERE mem_Idx = :mem_Idx
                        AND DATE_FORMAT(reg_Date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
						AND push_Bit = 'N'
						AND type = '4'
					LIMIT ".$ppCnt.";
				";
				$ppmsgUStmt = $DB_con->prepare($ppmsgUquery);
				$ppmsgUStmt->bindparam(":mem_Idx",$mem_Idx);
				$ppmsgUStmt->execute();

				$ppmemTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE idx = :mIdx AND b_Disply IN ('N', 'P')" ;
				$ppmemTokStmt = $DB_con->prepare($ppmemTokQuery);
				$ppmemTokStmt->bindparam(":mIdx",$mem_Idx);
				$ppmemTokStmt->execute();
				$ppmemTokNum = $ppmemTokStmt->rowCount();
				if($ppmemTokNum < 1)  { //주 ID가 없을 경우 회원가입 시작
				} else {  //등록된 회원이 있을 경우
					while($ppmemTokRow = $ppmemTokStmt->fetch(PDO::FETCH_ASSOC)) {
						$ppmem_NToken = $ppmemTokRow["mem_Token"];//토큰값
					}
				}

				
				//회원 고유 아이디
				$ppnSidQuery = "SELECT mem_Os, mem_MPush, mem_Token from TB_MEMBERS WHERE idx = :mIdx AND b_Disply IN ('N', 'P') " ;
				$ppnSidStmt = $DB_con->prepare($ppnSidQuery);
				$ppnSidStmt->bindparam(":mIdx",$mem_Idx);
				$ppnSidStmt->execute();
				$ppnSidNum = $ppnSidStmt->rowCount();
				
				if($ppnSidNum < 1)  { //아닐경우
				} else {
					
					while($ppnSidRow=$ppnSidStmt->fetch(PDO::FETCH_ASSOC)) {
						
						$ppnmemOs = $ppnSidRow['mem_Os'];         //os구분  (0 : 안드로이드, 1: 아이폰)
						$ppnmemMPush = $ppnSidRow['mem_MPush'];     //푸시발송여부  (0 : 발송, 1: 발송불가)
						
						$ppchkState = "8";  //페널티부과시 구매자에게 입금요청
						

						if ($ppnmemOs != "" ) { //os가 있을 경우
							if ($ppnmemMPush == "0") { //푸시 수신 가능
								$ppntitle = "리치";
								$ppnmsg = $ppnmsg;
							} else {
								$ppntitle = "";
								$ppnmsg = "";
							}

							$ppntokens = $ppmem_NToken;
							
							//알림할 내용들을 취합해서 $data에 모두 담는다. 프로젝트 의도에 따라 다른게 더 있을 수 있다.
							$ppninputData = array("title" => $ppntitle, "body" => $ppnmsg, "state" => $ppchkState);
							
							//마지막에 알림을 보내는 함수를 실행하고 그 결과를 화면에 출력해 준다.
							if ($ppnmemOs == "0") { //안드로이드
								/*if ($nmemMPush == "0") {
									$nresult = send_AnPush($ntokens, $ninputData);
								}*/
								$pppushUrl = "https://fcm.googleapis.com/fcm/send";
								$ppheaders = [];
								$ppheaders[] = 'Content-Type: application/json';
								$ppheaders[] = 'Authorization:key=' . GOOGLE_API_KEY;
								
								$ppdata = array(
									"data" => array(
										'title'	=> $ppninputData["title"],
										'body' 	=> $ppninputData["body"],
										'chkState'  => $ppninputData["state"], //상태 리턴
									),
									"to"  => $ppntokens,//token get on my ipad with the getToken method of cordova plugin,
									
								);
								
								//$json_data = json_encode($data);
								$ppjson_data =  json_encode($ppdata, JSON_UNESCAPED_UNICODE);
								//print_r($json_data);
								
								$ppch = curl_init();
								curl_setopt($ppch, CURLOPT_URL, $pppushUrl);
								curl_setopt($ppch, CURLOPT_POST, true );
								curl_setopt($ppch, CURLOPT_HTTPHEADER, $ppheaders);
								curl_setopt($ppch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ppch, CURLOPT_SSL_VERIFYPEER, false );
								curl_setopt($ppch, CURLOPT_POSTFIELDS, $ppjson_data);
								
								$ppresult = curl_exec($ppch);
								
								if ($ppresult === FALSE) {
									die('Curl failed: ' . curl_error($ppch));
								}
								curl_close($ppch);
								
								sleep(1);
							} else {
								//$nresult = send_IosPush($ntokens, $ninputData);
												
								$ppurl = "https://fcm.googleapis.com/fcm/send";
								$ppregistrationIds = array($ppntokens);
								$ppserverKey = GOOGLE_API_KEY;
								
								$pptitle = $ppninputData["title"];
								$ppbody = $ppninputData["body"];
								$pptaxiState = $ppninputData["state"]; //상태 리턴
								
								if ($pptitle == "" && $ppbody == "") { //제목, 메시지가 없을 때 없앰.
									$ppnotification = array('content_available' => 'true', 'title' => '', 'body' => '', 'icon' => 'fcm_push_icon', 'sound' => '');
								} else {
									$ppnotification = array('title' => $pptitle , 'body' => $ppbody, 'icon' => 'fcm_push_icon', 'sound' => 'default');
								}
								
								$pparrayToSend = array(
									'registration_ids' => $ppregistrationIds,
									'notification'=> $ppnotification,
									"data" => array(
										'chkState'  => $pptaxiState, //상태 리턴
									),
									'priority'=>'high'
								);
								
								//$json = json_encode($arrayToSend);
								$ppjson =  json_encode($pparrayToSend, JSON_UNESCAPED_UNICODE);
								//print_r($json)."<BR>";
								
								$ppheaders = array();
								$ppheaders[] = 'Content-Type: application/json';
								$ppheaders[] = 'Authorization: key='. $ppserverKey;
								
								$ppch = curl_init();
								curl_setopt($ppch, CURLOPT_URL, $ppurl);
								curl_setopt($ppch, CURLOPT_CUSTOMREQUEST,"POST");
								curl_setopt($ppch, CURLOPT_POSTFIELDS, $ppjson);
								curl_setopt($ppch, CURLOPT_HTTPHEADER,$ppheaders);
								curl_setopt($ppch, CURLOPT_RETURNTRANSFER, true);
								
								//Send the request
								$ppresult = curl_exec($ppch);
								if ($ppresult === FALSE)
								{
									die('FCM Send Error: ' . curl_error($ppch));
								}
								
								curl_close( $ppch );
								
								sleep(1);
								
								//return $result;
								
								
							}
							
						}
						
					}
				}
			}//페널티부과푸시 종료
			$cnt++;
		}
		if($cnt == 0){
			$result = array("result" => "error", "errorMsg" => "발송할 푸시가 없습니다.", "time" => $reg_Date);
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
?>
