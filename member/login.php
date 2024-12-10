<?
include $_SERVER['DOCUMENT_ROOT'] . "/lib/common.php";
$db = db();

$result = 'F';
$id = $_REQUEST['id'];
$password = $_REQUEST['password'];
$mb_password = password_hash($password, PASSWORD_DEFAULT);  // 비밀번호 암호화 
$push_token = $_REQUEST['token'];
$login_device = $_REQUEST['device'];
$login_ip = $_SERVER['REMOTE_ADDR'];

// 16바이트의 난수 생성
$token = openssl_random_pseudo_bytes(16);
//Convert the binary data into hexadecimal representation.
// 16진수로 변환
$app_token = bin2hex($token);
//Print it out for example purposes.

//트렌젝션 시작
$sql = "SELECT m.member_no, m.password, me.login_cnt FROM ikmember_member AS m INNER JOIN ikmember_member_etc AS me ON m.member_no = me.member_no WHERE m.id = :id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();
$tran_cnt_member = $stmt->rowCount();
if ($tran_cnt_member > 0) {
	$row = $stmt->fetch();
	$member_no = $row['member_no'];
	$hash = $row['password'];
	$login_cnt = $row['login_cnt'];      // 로그인 횟수
	$login_cnt = $login_cnt + 1;

	if (password_verify($password, $hash)) { // 비밀번호가 일치하는지 비교합니다. 

		$db->beginTransaction();
		$mem_sql = "UPDATE ikmember_member SET
			push_token = :push_token,
			app_token = :app_token
			WHERE member_no = :member_no
			LIMIT 1";
		$mem_stmt = $db->prepare($mem_sql);
		$mem_stmt->bindValue(':push_token', $push_token);
		$mem_stmt->bindValue(':app_token', $app_token);
		$mem_stmt->bindValue(':member_no', $member_no);
		$mem_stmt->execute();
		$tran_cnt_mem = $mem_stmt->rowCount();

		$etc_sql = "UPDATE ikmember_member_etc SET
			last_login_datetime = NOW()
			,login_cnt = :login_cnt
			WHERE member_no = :member_no
			LIMIT 1";
		$etc_stmt = $db->prepare($etc_sql);
		$etc_stmt->bindValue(':login_cnt', $login_cnt);
		$etc_stmt->bindValue(':member_no', $member_no);
		$etc_stmt->execute();
		$tran_cnt_etc = $etc_stmt->rowCount();

		$history_sql = "INSERT INTO ikmember_member_history SET
			login_ip = :login_ip
			,login_device = :login_device
			,member_no = :member_no";

		$history_stmt = $db->prepare($history_sql);
		$history_stmt->bindValue(':login_ip', $login_ip);
		$history_stmt->bindValue(':login_device', $login_device);
		$history_stmt->bindValue(':member_no', $member_no);
		$history_stmt->execute();
		$tran_cnt_history = $history_stmt->rowCount();

		if ($tran_cnt_mem > 0 && $tran_cnt_etc > 0 && $tran_cnt_history > 0) {
			$db->commit();
			$response['result'] = 'T';
			$response['token'] = $app_token;
		} else {
			$db->rollBack();
			$response['result'] = $result;
			$response['msg'] = "로그인에 실패했습니다. 잠시 후 다시 시도해주세요.";
		}
	} else {
		$response['result'] = $result;
		$response['msg'] = "비밀번호가 다릅니다. 잠시 후 다시 시도해주세요.";
	}
} else {
	$response['result'] = $result;
	$response['msg'] = "잘못된 아이디 입니다. 잠시 후 다시 시도해주세요.";
}
echo json_encode($response);
