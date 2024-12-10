<?
include $_SERVER['DOCUMENT_ROOT'] . "/lib/common.php";
$db = db();

$result = 'F';
//트렌젝션 시작

$sql = "SELECT * FROM ikapp_main_ad WHERE is_ad_on = 1 AND DATE_FORMAT(NOW(), '%Y-%m-%d') BETWEEN DATE_FORMAT(ad_date, '%Y-%m-%d')  AND DATE_FORMAT(ad_end_date, '%Y-%m-%d')";
$stmt = $db->prepare($sql);
$stmt->execute();
$tran_cnt_member = $stmt->rowCount();
$ad_arr = array();
$ad_list = array();
if ($tran_cnt_member > 0) {
	while ($row = $stmt->fetch()) {
		unset($ad_arr);
		$ad_arr['adtype'] = $row['ad_type'];
		$ad_arr['adname'] = $row['ad_name'];
		$ad_arr['admemo'] = $row['ad_memo'];
		$ad_arr['adtarget'] = $row['ad_target'];
		$adimgpath = $row['ad_img_path'];
		$adimgname = $row['ad_img_name'];
		$http_host = $_SERVER['HTTP_HOST'];
		$adimgurl = "http://file.brightenmall.kr". $adimgpath."/".$adimgname;
		$ad_arr['adimgurl'] = $adimgurl;
		// $ad_arr['adimgorgname'] = $row['ad_img_org_name'];
		$ad_arr['adurl'] = $row['ad_url'];
		$ad_arr['adfileurl'] = $row['ad_file_url'];
		$ad_arr['adpay'] = $row['ad_pay'];
		$ad_arr['adgivetype'] = $row['ad_give_type'];
		$ad_arr['adgiveunit'] = $row['ad_give_unit'];
		$ad_arr['adgivenow'] = $row['ad_give_now'];
		$ad_arr['adgivemax'] = $row['ad_give_max'];
		array_push($ad_list, $ad_arr);
	}
	$response['result'] = 'T';
	$response['adlist'] = $ad_list;
} else {
	$response['result'] = $result;
	$response['msg'] = '등록된 광고가 없습니다.';
}
echo json_encode($response);
