<?
include $_SERVER['DOCUMENT_ROOT'] . "/lib/common.php";
$db = db();

$result = 'F';
$id = $_REQUEST['id'];
//트렌젝션 시작
if(strpos($id, "admin")){
    $response['result'] = $result;
    $response['msg'] = "금지된 단어가 포함된 아이디입니다. 확인 후 다시 시도해주세요.";
}else{
    $sql = "SELECT member_no FROM ikmember_member WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $tran_cnt_member = $stmt->rowCount();
    if ($tran_cnt_member < 1) {
        $response['result'] = 'T';
    } else {
        $response['result'] = $result;
        $response['msg'] = "이미 등록된 아이디입니다.";
    }
}
echo json_encode($response);
