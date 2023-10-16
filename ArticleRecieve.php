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
// $DIR = "wamp64/www/php-auth-api/";
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// $file_chunks = explode(";base64,", $data->image);
       
// $fileType = explode("images/", $file_chunks[0]);
// $image_type = $fileType[1];
// $base64Img = base64_decode($file_chunks[1]);

// $file = $DIR . uniqid() . '.jpg';

if ($_SERVER["REQUEST_METHOD"] != "POST") :

    $returnData = msg(0, 404, 'Page Not Found!');

elseif (
    !isset($data->ar_title)
    || !isset($data->meta_desc)
    || !isset($data->user_name)
    || !isset($data->description)
    || !isset($data->image)
    || empty(trim($data->ar_title))
    || empty(trim($data->meta_desc))
    || empty(trim($data->user_name))
    || empty(trim($data->description))
    || empty(trim($data->image))
) :

    $fields = ['fields' => ['ar_title', 'meta_desc', 'user_name', 'description','image']];
    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :

    // file_put_contents($file, $base64Img);

    $ar_title = trim($data->ar_title);
    $meta_desc = trim($data->meta_desc);
    $user_name = trim($data->user_name);
    $description = trim($data->description);
    $image = trim($data->image);

    if (strlen($description) < 5) :
        $returnData = msg(0, 422, 'Your Desc must be at least 5 characters long!');

    elseif (strlen($ar_title) < 3) :
        $returnData = msg(0, 422, 'Your Title must be at least 3 characters long!');

    else :
        try {
                $insert_query = "INSERT INTO `user_article`(`user_name`,`ar_title`,`meta_desc`,`description`,`image`) VALUES(:user_name,:ar_title,:meta_desc,:description,:image)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':user_name', htmlspecialchars(strip_tags($user_name)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':meta_desc', htmlspecialchars(strip_tags($meta_desc)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':description', $description, PDO::PARAM_STR);
                $insert_stmt->bindValue(':ar_title', htmlspecialchars(strip_tags($ar_title)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':image', $image, PDO::PARAM_STR);

                $insert_stmt->execute();


                $returnData = msg(1, 201, 'Your Article has been recieved Successfully!');

        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }
    endif;
endif;

echo json_encode($returnData);
?>