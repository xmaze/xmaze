<?php
header("Access-Control-Allow-Origin: *");

function __autoload($cn) {
    include_once "classes/" . $cn . ".php";
}

function reload() {
    header("Location: /room/home/");
}

function redirect($redirect) {
    header("Location: " . $redirect);
}

$routing = new Routing();
$rooms = new Rooms();
$template = new Template();

require_once "classes/extras/class.phpmailer.php";
require_once "classes/extras/class.smtp.php";
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Port = 465;

// -
$mail->Host = "smtp.sendgrid.net";
$mail->Username = "";
$mail->Password = "";
$email_from = 'noreply@mindey.com';
$name_from = '[maze.mindey.com]';
$maze_owner_email = 'mindey@mindey.com';
// -

// -
$method = $routing->getRequestMethod();
if ($method == 'POST') {
    $contents = $routing->getPostVariables();

    $files = $routing->getPostFiles();

    // $data = print_r($files['file'], TRUE);
    // $file = fopen("data.dat","wb");
    // fwrite($file, $data);
    // fclose($file);

    // Email 1 -- To Maze Owner.
    $email_gamer = $contents['email'];
    $name_gamer = $contents['email'];
    $subject = $_SERVER['REQUEST_URI'];
    $text = $contents['text'];

    $mail->SetFrom($email_from, $name_from);
    $mail->AddAddress($maze_owner_email, $maze_owner_email);
    $mail->AddReplyTo($email_gamer, $name_gamer);
    $mail->Subject = $subject;
    $mail->Body = $text;
    $file_tmp  = $files['file']['tmp_name'];
    $file_name = $files['file']['name'];

    if($files['file']['size'] == 0) {
    }
    else {
        $mail->AddAttachment($file_tmp, $file_name);
    }

    try{
        $mail->Send();
        echo "Success!";
    } catch(Exception $e){
        //Something went bad
        echo "Fail - " . $mail->ErrorInfo;
    }

    // Email 2 -- To Maze Visitor.
    $mail->ClearAllRecipients();
    $mail->SetFrom($email_from, $name_from);
    $mail->AddAddress($email_gamer, $name_gamer);
    $mail->Subject = $subject;
    $file_name = $_FILES['file']['name'];
    $mail->Body = 'Message succesfully sent to maze-owner, with the attachment and text: "'.$text.'".';

    try{
        $mail->Send();
        echo "Success!";
    } catch(Exception $e){
        //Something went bad
        echo "Fail - " . $mail->ErrorInfo;
    }

}
// -

$routes = $routing->getRoutesArray();
if ($routes[0] !== "room" || (isset($routes[2]) && $routes[2] !== "door") ) {
    reload();
}

$room = $rooms->getRoom($routes);
if (empty($room)) {
    reload();
}

$doors = $room[0]->doors;
$tokens = $routing->getUriTokens();
$format = isset($tokens["format"]) ? $tokens["format"] : null;

if (isset($routes[2])) {
    $door = $rooms->getDoor($routes, $doors);
    if (empty($door)) {
        reload();
    }
    if (isset($tokens["key"])) {
        $answer = $rooms->checkAnswer($tokens["key"], $door);
        if ($rooms->checkAnswer($tokens["key"], $door)) {
            if ($format === "json") {
                redirect($door[0]->redirect . "?format=json");
            }
            else {
                redirect($door[0]->redirect  . "/");
            }
        }
    }
    if (!isset($answer)) {
        if ($method == 'POST') {
            $result = array(
                "result" => "message sent",
            );
        }
        else {
            $result = $template->styleDoor($door);
        }
    }
    else if (!$answer) {
        $result = array(
            "result" => "answer incorrect",
        );
    }
    else {
        $result = $template->styleDoor($door);
    }

} else {
    $result = $template->styleRoom($room, $doors, $format);
}


if ($format === "json") {
    // For programmatic clients.
    //

    header('Content-Type: application/json');
    echo json_encode($result);

} else {
    // For human eyes.
    //

    // For now, leave sequences, not to confuse developments. :)
    // unset($result["room"]["seq"]);

    $tex = NULL;

    if (isset($result["room"])) {
        if (array_key_exists("tex", $result["room"])) {
            $tex = $result["room"]["tex"];
            unset($result["room"]["tex"]);
        }

    }

    else if (isset($result["door"])) {
        if (array_key_exists("tex", $result["door"])) {
            $tex = $result["door"]->tex;
            unset($result["door"]->tex);
        }
    }


    echo "
        <html>
            <head>
                <meta name='viewport' content='width=device-width'>
                <script type='text/javascript' src='/static/jsonview.js'></script>
                <!-- Support for inclusion of 3D models via https://googlewebcomponents.github.io/model-viewer/ -->
                <script type='module' src='/static/extras/model-viewer.js'></script>
                <script nomodule src='/static/extras/model-viewer-legacy.js'></script>
            </head>
            <body onload=\"xmaze('/static/default')\" background=\"$tex\">
                <div id='zone'>
                    " . json_encode($result) . "
                </div>
            </body>
        </html>
    ";
}
?>
