<?
/*======================================================================================================================

* 프로그램			: DB 내용 불러올 함수
* 페이지 설명		: DB 내용 불러올 함수

========================================================================================================================*/


/*회원 등급 값 가져오기 */
function memLvInfo($chkNum) {
    
    $fDB_con = db1();
    
    //회원 등급 기준 조회
    $mpQuery = "";
    $mpQuery = "SELECT memLv, memMatCnt FROM TB_MEMBER_LEVEL WHERE memLv <> '1' ORDER BY memLv ASC ";
    //$mpQuery = "SELECT memLv, memMatCnt FROM TB_MEMBER_LEVEL WHERE memMatCnt >= :memMatCnt  LIMIT 1 ";
    $mpStmt = $fDB_con->prepare($mpQuery);
    $mpStmt->bindparam(":memMatCnt",$totMemNum);
    $mpStmt->execute();
    $mpNum = $mpStmt->rowCount();
    
    if($mpNum < 1)  { //아닐경우
    } else {
        while($mpRow=$mpStmt->fetch(PDO::FETCH_ASSOC)) {
            $memLv = trim($mpRow['memLv']);	         // 포인트
            $memMatCnt = trim($mpRow['memMatCnt']);	         // 포인트
            
            if ($chkNum >= "500") {
                $memLv = "7";
            } else if ($chkNum >= "400") {
                $memLv = "8";
            } else if ($chkNum >= "300") {
                $memLv = "9";
            } else if ($chkNum >= "200") {
                $memLv = "10";
            } else if ($chkNum >= "100") {
                $memLv = "11";
            } else if ($chkNum >= "50") {
                $memLv = "12";
            } else if ($chkNum >= "10") {
                $memLv = "13";
            } else {
                $memLv = "14";
            }
        }
        
        return $memLv;
    }
    
    dbClose($fDB_con);
    $mpStmt = null;
}  



/*회원 주 아이디 가져오기 */
function memSIdInfo($mem_Id) {
    
    $fDB_con = db1();
    
    $memTQuery = "SELECT mem_SId FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1" ;
    $memTStmt = $fDB_con->prepare($memTQuery);
    $memTStmt->bindparam(":mem_Id",$mem_Id);
    $memTStmt->execute();
    $memTNum = $memTStmt->rowCount();
    
    if($memTNum < 1)  { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while($memTRow = $memTStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_SId = $memTRow['mem_SId'];	       //체크 랜덤아이디
        }
        return $mem_SId;
    }
    
    dbClose($fDB_con);
    $memTStmt = null;
}


/*회원 닉네임 가져오기 */
function memNickInfo($mem_Id) {
    
    $fDB_con = db1();
    
    $memNmQuery = "SELECT mem_NickNm FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1" ;
    $memNmStmt = $fDB_con->prepare($memNmQuery);
    $memNmStmt->bindparam(":mem_Id",$mem_Id);
    $memNmStmt->execute();
    $memNmNum = $memNmStmt->rowCount();
    
    if($memNmNum < 1)  { 
    } else {  //등록된 회원이 있을 경우
        while($memNmRow = $memNmStmt->fetch(PDO::FETCH_ASSOC)) {
            $mem_NickNm = $memNmRow['mem_NickNm'];	       //체크 랜덤아이디
        }
        return $mem_NickNm;
    }
    
    dbClose($fDB_con);
    $memNmStmt = null;
}




/*회원 디바이스 아이디 가져오기 */
function memDeviceIdInfo($mem_Id) {
    
    $fDB_con = db1();
    
    $memDeQuery = "SELECT mem_DeviceId FROM TB_MEMBERS WHERE mem_Id = :mem_Id AND b_Disply = 'N' LIMIT 1" ;
    $memDeStmt = $fDB_con->prepare($memDeQuery);
    $memDeStmt->bindparam(":mem_Id",$mem_Id);
    $memDeStmt->execute();
    $memDeNum = $memDeStmt->rowCount();
    
    if($memDeNum < 1)  { //없을 경우
    } else {  //등록된 회원이 있을 경우
        while($memDeRow = $memDeStmt->fetch(PDO::FETCH_ASSOC)) {
            $memDeviceId = $memDeRow['mem_DeviceId'];	       //체크 랜덤아이디
        }
        return $memDeviceId;
    }
    
    dbClose($fDB_con);
    $memDeStmt = null;
}




/* 매칭 회원 토큰 값 가져오기 */
function memMatchTokenInfo($mem_SId) {
    
    $fDB_con = db1();
    
    $memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE mem_SId = :mem_SId AND b_Disply = 'N'" ;
    $memTokStmt = $fDB_con->prepare($memTokQuery);
    $memTokStmt->bindparam(":mem_SId",$mem_SId);
    $memTokStmt->execute();
    $memTokNum = $memTokStmt->rowCount();
    
    $tokens = array();
    if($memTokNum < 1)  { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
            $tokens[] = $memTokRow["mem_Token"];//토큰값
        }
        return $tokens;
    }
    
    
    dbClose($fDB_con);
    $memTokStmt = null;
}



/* 이벤트 공지 회원 토큰 값 가져오기 */
function memNoticeTokenInfo($mem_SId) {
    
    $fDB_con = db1();
    
    $memTokQuery = "SELECT mem_Token FROM TB_MEMBERS WHERE mem_SId = :mem_SId AND mem_NPush = '0' AND b_Disply = 'N'" ;
    $memTokStmt = $fDB_con->prepare($memTokQuery);
    $memTokStmt->bindparam(":mem_SId",$mem_SId);
    $memTokStmt->execute();
    $memTokNum = $memTokStmt->rowCount();
    
    $tokens = array();
    if($memTokNum < 1)  { //주 ID가 없을 경우 회원가입 시작
    } else {  //등록된 회원이 있을 경우
        while($memTokRow = $memTokStmt->fetch(PDO::FETCH_ASSOC)) {
            $tokens[] = $memTokRow["mem_Token"];//토큰값
        }
        return $tokens;
    }
    
    
    dbClose($fDB_con);
    $memTokStmt = null;
}




//푸시 메시지 전송(안드로이드)
function send_notification ($tokens, $data) {
    
    $url = 'https://fcm.googleapis.com/fcm/send';
    //어떤 형태의 data/notification payload를 사용할것인지에 따라 폰에서 알림의 방식이 달라 질 수 있다.
    $msg = array(
        'title'	=> $data["title"],
        'msg' 	=> $data["msg"],
        'chkState' => $data["state"]
    );
    
    
    //data payload로 보내서 앱이 백그라운드이든 포그라운드이든 무조건 알림이 떠도록 하자.
    $fields = array(
        'registration_ids' => $tokens,
        'data'	=> $msg
    );
    
    //구글키는 config.php에 저장되어 있다.
    $headers = array(
        'Authorization:key =' . GOOGLE_API_KEY,
        'Content-Type: application/json'
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    $result = curl_exec($ch);
    
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    
    sleep(1);
    
    return $result;
}



//새로운 푸시 메시지 전송(최종 안드로이드)
function send_AnPush ($tokens, $data) {
    
    $pushUrl = "https://fcm.googleapis.com/fcm/send";
    $headers = [];
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;
    
    $data = array(
        "data" => array(
            'title'	=> $data["title"],
            'msg' 	=> $data["msg"],
            'chkState'  => $data["state"] //상태 리턴
        ),
        "to"  => $tokens,//token get on my ipad with the getToken method of cordova plugin,
        
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
    
    return $result;
    
}

//최종 아이폰 푸시
function send_IosPush($tokens, $data) {
    
    $url = "https://fcm.googleapis.com/fcm/send";
    $registrationIds = array($tokens);
    $serverKey = GOOGLE_API_KEY;
    
    $title = $data["title"];
    $msg = $data["msg"];
    $taxiState = $data["state"];
    $chat = $data["chat"];

    
    if ($title == "" && $msg == "") { //제목, 메시지가 없을 때 없앰.
        $notification = array('content_available' => 'true', 'title' => '', 'body' => '', 'icon' => 'fcm_push_icon', 'sound' => '');
    } else {
        $notification = array('title' => $title , 'body' => $msg, 'icon' => 'fcm_push_icon', 'sound' => 'default');
    }
    
    $arrayToSend = array(
        'registration_ids' => $registrationIds,
        'notification'=> $notification,
        "data" => ($chat != "1" ? array('chkState' => $taxiState) : array('chat'  => $chat)),
        'priority'=>'high'
    );
    
    //$json = json_encode($arrayToSend);
    $json =  json_encode($arrayToSend, JSON_UNESCAPED_UNICODE);
    
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
    
    return $result;
}



//새로운 푸시 메시지 전송(아이폰)==>잘안되는 거
function send_Push ($tokens, $data) {
    $pushUrl = "https://fcm.googleapis.com/fcm/send";
    $headers = [];
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization:key=' . GOOGLE_API_KEY;
    
    
    $title = $data["title"];
    $msg = $data["msg"];
    $taxiState = $data["state"];
    
    if ($title == "" && $msg == "") { //제목, 메시지가 없을 때 없앰.
        $data = array(
            "notification" => array(
                "content_available" => "true",
                "title"  => "",
                "body"   => "",
                "icon"   => "",
                "sound"  => "", 
            ),
            "data" => array(
                'chkState' => "" //상태 리턴
            ),
            "to"  => $tokens,//token get on my ipad with the getToken method of cordova plugin,
            "priority" => "high",
        );
    } else {
        $data = array(
            "notification" => array(
                "title"  => $title,
                "body"   => $msg,
                // "click_action" =>"FCM_PLUGIN_ACTIVITY", //기본 클릭했을 경우 돌아감
                "icon"   => "fcm_push_icon",
                "sound"  => "default", 
            ),
            "data" => array(
                'chkState'  => $taxiState //상태 리턴
            ),
            "to"  => $tokens,//token get on my ipad with the getToken method of cordova plugin,
            "priority" => "high",
        );
    }
    
    $json_data = json_encode($data);
    //print_R($json_data)."<BR>";
    
    $ch = curl_init();
    /*
    curl_setopt($ch, CURLOPT_URL, $pushUrl);
    curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    */
    
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
    
    return $result;
}



//취소 신청자 회원정보
function memMatCInfo($mem_SId, $mem_Id) {
    
    $fDB_con = db1();
    
    $mnSql = "  , ( SELECT mem_NickNm FROM TB_MEMBERS WHERE TB_MEMBERS.mem_SId = TB_MEMBERS_ETC.mem_SId AND TB_MEMBERS.mem_Id = TB_MEMBERS_ETC.mem_Id AND TB_MEMBERS.b_Disply = 'N' limit 1 ) AS memNickNm  ";
    $memQuery = "";
    $memQuery = "SELECT mem_ChNum, mem_McCnt {$mnSql} FROM TB_MEMBERS_ETC WHERE mem_SId = :mem_SId AND mem_Id = :mem_Id  LIMIT 1 ";
    //echo $memQuery."<BR>";
    //exit;
    $memStmt = $fDB_con->prepare($memQuery);
    $memStmt->bindparam(":mem_SId",$mem_SId);
    $memStmt->bindparam(":mem_Id",$mem_Id);
    $memStmt->execute();
    $memNum = $memStmt->rowCount();
    
    if($memNum < 1)  { //아닐경우
    } else {
        
        while($memRow=$memStmt->fetch(PDO::FETCH_ASSOC)) {
            $memNickNm = trim($memRow['memNickNm']);        // 취소신청자 닉네임
            $memChNum = trim($memRow['mem_ChNum']);		   // 회원 등급 관련 점수
            $$memMcCnt = trim($memRow['mem_McCnt']);		  // 회원 매칭 취소 횟수
            
            if ($memNickNm == "") {
                $memNickNm = "탈퇴회원";
            } else {
                $memNickNm = $memNickNm;
            }
            
            if ($memChNum == "") {
                $memChNum = "0";
            } else {
                $memChNum =  $memChNum ;
            }
            
            if ($memMcCnt == "") {
                $memMcCnt = "0";
            } else {
                $memMcCnt =  $memMcCnt ;
            }
            
            $dinfo[memNickNm] = $memNickNm;        // 취소신청자 닉네임
            $dinfo[memChNum] = $memChNum;		   // 회원 등급 관련 점수
            $dinfo[memMcCnt] = $memMcCnt;		  // 회원 매칭 취소 횟수
            
        }
        
        return $dinfo;
    }
    
    dbClose($fDB_con);
    $memStmt = null;
}


/* 패널티 타이틀 및 점수 가져오기 */
function penaltyIdInfo($idx) {
    
    $fDB_con = db1();
    
    $mPointQuery = "";
    $mPointQuery = "SELECT point_Title, point_Num FROM TB_CPOINT WHERE idx = :idx LIMIT 1 ";
    $mPointStmt = $fDB_con->prepare($mPointQuery);
    $mPointStmt->bindparam(":idx",$idx);
    $mPointStmt->execute();
    $mPointNum = $mPointStmt->rowCount();
    
    if($mPointNum < 1)  { //아닐경우
    } else {
        while($mPointRow=$mPointStmt->fetch(PDO::FETCH_ASSOC)) {
            $mData['pointTitle'] =  trim($mPointRow['point_Title']);    // 패널티 제목
            $mData['pointNum'] =  trim($mPointRow['point_Num']);        // 패널티 점수
        }
        
        return $mData;
    }
    
    dbClose($fDB_con);
    $mPointStmt = null;
}


/* 대기생성 회원 목록 삭제 */
function standbyDel($mem_Id) {
    
    $fDB_con = db1();
    
    $mem_SId = memSIdInfo($mem_Id);   //회원 주아이디
    
    $standbyQuery = "";
    $standbyQuery = "SELECT idx FROM TB_SHARING_STANDBY WHERE taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId ORDER BY idx DESC LIMIT 1 ";
    $standbyStmt = $fDB_con->prepare($standbyQuery);
    $standbyStmt->bindparam(":taxi_SMemId",$mem_SId);
    $standbyStmt->bindparam(":taxi_MemId",$mem_Id, PDO::PARAM_STR);
    $standbyStmt->execute();
    $standbyNum = $standbyStmt->rowCount();
    
    
    if($standbyNum < 1)  { //아닐경우
        // return 0; //없을 경우
    } else {
        while($standbyRow=$standbyStmt->fetch(PDO::FETCH_ASSOC)) {
            $stIdx = trim($standbyRow['idx']);	  //대기 고유 idx
        }
        
        //쉐어링 대기 테이블 삭제
        $delStandbyQuery = "DELETE FROM TB_SHARING_STANDBY WHERE taxi_SMemId = :taxi_SMemId AND taxi_MemId = :taxi_MemId AND idx = :idx LIMIT 1";
        $delStandbyStmt = $fDB_con->prepare($delStandbyQuery);
        $delStandbyStmt->bindparam(":taxi_SMemId",$mem_SId);
        $delStandbyStmt->bindparam(":taxi_MemId",$mem_Id);
        $delStandbyStmt->bindParam(":idx", $stIdx);
        $delStandbyStmt->execute();
        
        return 1;    //성공
    }
    
    dbClose($fDB_con);
    $standbyStmt = null;
    $delStandbyStmt = null;
}







?>