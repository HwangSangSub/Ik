<?
include $_SERVER['DOCUMENT_ROOT'] . "/lib/common.php";
$db = db();

$result = 'F';
//트렌젝션 시작

$sql = "SELECT * FROM ikapp_default";
$stmt = $db->prepare($sql);
$stmt->execute();
$tran_cnt_default = $stmt->rowCount();
if ($tran_cnt_default > 0) {
	$row = $stmt->fetch();
	$companyname = $row['company_name'];							// 회사명
	$ownername = $row['owner_name'];								// 대표자명
	$companyzip = $row['company_zip'];								// 회사우편번호
	$companyaddress = $row['company_address'];						// 회사주소
	$companytel = $row['company_tel'];								// 회사연락처
	$companyfax = $row['company_fax'];								// 회사팩스번호
	$businessno = $row['business_no'];								// 사업자등록번호
	$onlinebusinessno = $row['online_business_no'];					// 통신판매업신고번호
	$valueonlinebusinessno = $row['value_online_business_no'];		// 부가통신사업신고번호

	// $response['result'] = 'T';
	$response['companyname'] = $companyname;
	$response['ownername'] = $ownername;
	$response['companyzip'] = $companyzip;
	$response['companyaddress'] = $companyaddress;
	$response['companytel'] = $companytel;
	$response['companyfax'] = $companyfax;
	$response['businessno'] = $businessno;
	$response['onlinebusinessno'] = $onlinebusinessno;
	$response['valuebusinessno'] = $valueonlinebusinessno;
} else {
	// $response['result'] = $result;
	$response['msg'] = '등록된 설정이 없습니다.';
}
echo json_encode($response);
