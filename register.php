<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

function msg($success, $status, $message, $extra = [])
{
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

// DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST") :

    $returnData = msg(0, 404, 'Page Not Found!');

elseif (
    !isset($data->user_name)
    || !isset($data->email_id)
    || !isset($data->password)
    || !isset($data->address)
    || !isset($data->gender)
    || !isset($data->dob)
    || !isset($data->pincode)
    || !isset($data->state)
    || empty(trim($data->user_name))
    || empty(trim($data->email_id))
    || empty(trim($data->dob))
    || empty(trim($data->address))
    || empty(trim($data->password))
    || empty(trim($data->gender))
    || empty(trim($data->pincode))
    || empty(trim($data->state))
) :

    $fields = ['fields' => ['user_name', 'email_id', 'password', 'address', 'gender', 'dob', 'state', 'pincode']];
    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :

    $name = trim($data->user_name);
    $email = trim($data->email_id);
    $address = trim($data->address);
    $password = trim($data->password);
    $gender = trim($data->gender);
    $dob = trim($data->dob);
    $state = trim($data->state);
    $pincode = trim($data->pincode);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) :
        $returnData = msg(0, 422, 'Invalid Email Address!');

    elseif (strlen($password) < 8) :
        $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');

    elseif (strlen($name) < 3) :
        $returnData = msg(0, 422, 'Your name must be at least 3 characters long!');

    else :
        try {

            $check_email = "SELECT `email_id` FROM `user` WHERE `email_id`=:email_id";
            $check_email_stmt = $conn->prepare($check_email);
            $check_email_stmt->bindValue(':email_id', $email, PDO::PARAM_STR);
            $check_email_stmt->execute();

            if ($check_email_stmt->rowCount()) :
                $returnData = msg(0, 422, 'This E-mail already in use!');

            else :
                $insert_query = "INSERT INTO `user`(`user_name`,`email_id`,`password`,`address`,`gender`,`dob`,`state`,`pincode`) VALUES(:user_name,:email_id,:password,:address,:gender,:dob,:state,:pincode)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':user_name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':address', htmlspecialchars(strip_tags($address)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':gender', htmlspecialchars(strip_tags($gender)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':email_id', $email, PDO::PARAM_STR);
                $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_stmt->bindValue(':dob', htmlspecialchars(strip_tags($dob)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':state', htmlspecialchars(strip_tags($state)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':pincode', htmlspecialchars(strip_tags($pincode)), PDO::PARAM_STR);

                $insert_stmt->execute();

                $returnData = msg(1, 201, 'You have successfully registered.');

            endif;
        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }
    endif;
endif;

echo json_encode($returnData);
?>