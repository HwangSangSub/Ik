<?
include $_SERVER['DOCUMENT_ROOT'] . "/lib/common.php";
$db = db();

$result = 'F';
$id = isset($_POST['id']) ? $_POST['id'] : "";
$password = isset($_POST['password']) ? $_POST['password'] : "";
$mb_password = password_hash($password, PASSWORD_DEFAULT);  // 비밀번호 암호화 
$name = isset($_POST['name']) ? $_POST['name'] : "";
$tel = isset($_POST['tel']) ? $_POST['tel'] : "";
$email = isset($_POST['email']) ? $_POST['email'] : "";
$push_token = isset($_POST['token']) ? $_POST['token'] : "";
$certification_no = isset($_POST['certification']) ? $_POST['certification'] : "";
$birthday = isset($_POST['birthday']) ? $_POST['birthday'] : "2022-02-25";
//트렌젝션 시작

$member_check_sql = "SELECT member_no FROM ikmember_member WHERE id = :id";
$member_check_stmt = $db->prepare($member_check_sql);
$member_check_stmt->bindValue(':id', $id);
$member_check_stmt->execute();
$tran_cnt_member = $member_check_stmt->rowCount();
if($tran_cnt_member < 1){
    $db->beginTransaction();
    $sql = "INSERT INTO ikmember_member SET
        id = :id
        ,password = :password
        ,name = :name
        ,push_token = :push_token
        ,status = 1";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':password', $mb_password);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':push_token', $push_token);
    $stmt->execute();
    $member_no = $db->lastInsertId();
    $tran_cnt = $stmt->rowCount();

    $etc_sql = "INSERT INTO ikmember_member_etc SET
        member_no = :member_no
        ,certification_no = :certification_no
        ,birthday = :birthday
        ,tel = :tel
        ,email = :email";

    $etc_stmt = $db->prepare($etc_sql);
    $etc_stmt->bindValue(':member_no', $member_no);
    $etc_stmt->bindValue(':certification_no', $certification_no);
    $etc_stmt->bindValue(':birthday', $birthday);
    $etc_stmt->bindValue(':tel', $tel);
    $etc_stmt->bindValue(':email', $email);
    $etc_stmt->execute();
    $tran_cnt_etc = $etc_stmt->rowCount();

    if ($tran_cnt > 0 && $tran_cnt_etc > 0) {
        $db->commit();
        $response['result'] = 'T';
    } else {
        $db->rollBack();
        $response['result'] = $result;
        $response['msg'] = "회원등록에 실패했습니다. 잠시 후 다시 시도해주세요.";
    }
}else{
    $response['result'] = $result;
    $response['msg'] = "이미 등록된 회원입니다. 확인 후 다시 시도해주세요.";
}
echo json_encode($response);
