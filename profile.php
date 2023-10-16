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

elseif(
    !isset($data->user_name)
    && !isset($data->desc)
    && !isset($data->address)
    && !isset($data->state)
    && !isset($data->pincode)
    && !isset($data->profile)
    && !isset($data->email_id)
    && empty(trim($data->user_name))
    && empty(trim($data->desc))
    && empty(trim($data->address))
    && empty(trim($data->state))
    && empty(trim($data->pincode))
    && empty(trim($data->profile))
    && empty(trim($data->email_id))
	) :

        $fields = ['fields' => ['user_name', 'desc', 'address','state','pincode','profile','email_id']];
        $returnData = msg(0, 422, 'Please Fill in all Required Fie', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :

    $name = trim($data->user_name);
    $desc = trim($data->desc);
    $address = trim($data->address);
    $state = trim($data->state);
    $pincode = trim($data->pincode);
    $profile = trim($data->profile);
    $email_id = trim($data->email_id);
    if (strlen($desc) < 10) :
        $returnData = msg(0, 422, 'Yor desc must be at least 10 character');

    elseif (strlen($name) < 3) :
        $returnData = msg(0, 422, 'Your name must be at least 3 characters long!');

    else :
        try {
                $update_query = "UPDATE user SET `user_name`=:user_name,`desc`=:desc,`address`=:address,`state`=:state,`pincode`=:pincode,`profile`=:profile WHERE `email_id`=:email_id ";

                $update_stmt = $conn->prepare($update_query);

                // DATA BINDING
                $update_stmt->bindValue(':user_name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                $update_stmt->bindValue(':desc', htmlspecialchars(strip_tags($desc)), PDO::PARAM_STR);
                $update_stmt->bindValue(':address', htmlspecialchars(strip_tags($address)), PDO::PARAM_STR);
                $update_stmt->bindValue(':state', htmlspecialchars(strip_tags($state)), PDO::PARAM_STR);
                $update_stmt->bindValue(':pincode', htmlspecialchars(strip_tags($pincode)), PDO::PARAM_STR);
                $update_stmt->bindValue(':profile', htmlspecialchars(strip_tags($profile)), PDO::PARAM_STR);
                $update_stmt->bindValue(':email_id', $email_id, PDO::PARAM_STR);

                $update_stmt->execute();

                $returnData = msg(1, 201, 'Profile is Updated Successfully!');

        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }
    endif;
endif;

echo json_encode($returnData);
?>