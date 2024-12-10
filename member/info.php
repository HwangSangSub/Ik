<?
include $_SERVER['DOCUMENT_ROOT'] . "/lib/common.php";
$db = db();

$result = 'F';
$logintoken = $_REQUEST['logintoken'];
//트렌젝션 시작
if ($logintoken != "") {

    $sql = "SELECT m.name, me.point, me.coupon FROM ikmember_member AS m INNER JOIN ikmember_member_etc AS me ON m.member_no = me.member_no WHERE m.login_token = :logintoken";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':logintoken', $logintoken);
    $stmt->execute();
    $tran_cnt_member = $stmt->rowCount();
    if ($tran_cnt_member > 0) {
        $row = $stmt->fetch();
        $name = $row['name'];
        $point = $row['point'];
        $coupon = $row['coupon'];

        $response['result'] = 'T';
        $response['name'] = $name;
        $response['namenoti'] = '임인년 기운찬 한 해 되세요!';
        $response['point'] = $point;
        $response['coupon'] = $coupon;
        $response['couponend'] = 0;
    } else {
        $response['result'] = 'T';
        $response['name'] = '게스트';
        $response['namenoti'] = '임인년 기운찬 한 해 되세요!';
        $response['point'] = 0;
        $response['coupon'] = 0;
        $response['couponend'] = 0;
    }
} else {
    $response['result'] = 'T';
    $response['name'] = '게스트';
    $response['namenoti'] = '임인년 기운찬 한 해 되세요!';
    $response['point'] = 0;
    $response['coupon'] = 0;
    $response['couponend'] = 0;
}
echo json_encode($response);
