<?

// $DB_con = dbmember();

// // 기본 설정값 확인
// $query = "
// 	SELECT c_Name, c_BankName, c_BankCode, mat_Dia_Rate, point_Dia, point_MDia, slot_add_Dia, penalty_Dia, motivate_Time, stop_Dia, send_Dia, min_Dia,
//      event_Date, event_Dia, event_Date2, event_Point, event_charTrdBit, appr_1st_Time, appr_2st_Time, report_Time, mat_DepostDate, u_Guide FROM TB_CONFIG";
// $stmt = $DB_con->prepare($query);
// $stmt->execute();
// $row=$stmt->fetch(PDO::FETCH_ASSOC);
// $c_Name = $row['c_Name'];									// 예금주
// $c_BankName = $row['c_BankName'];							// 은행명
// $c_BankCode = $row['c_BankCode'];							// 계좌번호
// $mat_Dia_Rate = $row['mat_Dia_Rate'];						    // 매칭수수료(%)
// $matDiaRate = $mat_Dia_Rate / 100;
// $point_Dia = $row['point_Dia'];								// 다이아현금구매시 추천인에게 적립되는 포인트(%)
// $point_MDia = $row['point_MDia'];							// 다이아현금구매시 나에게 적립되는 포인트(%)
// $slot_add_Dia = $row['slot_add_Dia'];						// 슬롯 구매시 소모되는 다이아(개)
// $penalty_Dia = $row['penalty_Dia'];							// 페널티해제시 필요 다이아 수(개)
// $motivate_Time = $row['motivate_Time'];						// 구매자 재독촉시간(분)
// $stop_Dia = $row['stop_Dia'];								// 최소다이아보유수(적을 경우 매칭 실패)
// $send_Dia = $row['send_Dia'];								// 다이아전송 시 최소 전송 가능 다이아 수(개)
// $min_Dia = $row['min_Dia'];									// 다이아전송 후 최소 보유다이아 수(개)
// $event_Date = $row['event_Date'];							// 회원가입시 다이아 이벤트 종료일
// $event_Dia = $row['event_Dia'];								// 회원가입시 다이아 이벤트 적립 다이아
// $event_Date2 = $row['event_Date2'];							// 다이아현금구매시 이벤트 종료일
// $event_Point = $row['event_Point'];							// 다이야현금구매시 이벤트 적립율(%)
// $event_charTrdBit = $row['event_charTrdBit'];							// 다이야현금구매시 이벤트 적립율(%)
// $appr_1st_Time = $row['appr_1st_Time'];						// 독촉가능시간(1차 - 입금독촉)
// $appr_2st_Time = $row['appr_2st_Time'];						// 독촉가능시간(2차 - 입금독촉 및 계장잠김안내)
// $report_Time = $row['report_Time'];							// 신고가능시간
// $mDepostDate = $row['mat_DepostDate'];						// 매칭입금시간
// $uGuide = $row['u_Guide'];						            // 거래안내

// dbClose($DB_con);
// $stmt = null;	
// $etc_stmt = null;	

// /*
// $slotPdate = 2; // 마감시간 기준일 설정(패널티)
// $h_carCnt = 1; // 보유상품수 비율
// $h_slotCnt = 1
// */

?>


