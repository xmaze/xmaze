<?php

function __autoload($cn) {
    include_once "classes/" . $cn . ".php";
}

function reload() {
    header("Location: /room/sample/");
}

function redirect($redirect) {
    header("Location: " . $redirect . "/");
}

$routing = new Routing();
$rooms = new Rooms();
$template = new Template();

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
            redirect($door[0]->redirect);
        }
    }
    $result = $template->styleDoor($door);
} else {
    $result = $template->styleRoom($room, $doors, $format);
}



if ($format === "json") {
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    echo "
        <html>
            <head>
                <meta name='viewport' content='width=device-width'>
                <script type='text/javascript' src='/static/jsonview.js'></script>
            </head>
            <body onload=\"xmaze('/static/default')\">
                <div id='zone'>
                    " . json_encode($result) . "
                </div>
            </body>
        </html>
    ";
}
?>
