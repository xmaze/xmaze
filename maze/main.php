<?php

function __autoload($cn) {
    include_once "classes/" . $cn . ".php";
}

function reload() {
    header("Location: /room/sample/");
}

function redirect($redirect) {
    header("Location: " . $redirect);
}

function refresh() {
    header("Location: " . $_SERVER["REQUEST_URI"] . "?format=json");
}

$routing = new Routing();
$rooms = new Rooms();
$template = new Template();

$tokens = $routing->getUriTokens();
if (!isset($tokens["format"]) || $tokens["format"] !== "json") {
    refresh();
}

$routes = $routing->getRoutesArray();
if ($routes[0] !== "room" || (isset($routes[2]) && $routes[2] !== "door") ) {
    reload();
}

$room = $rooms->getRoom($routes);
if (empty($room)) {
    reload();
}

$doors = $room[0]->doors;
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
    $result = $template->styleRoom($room, $doors);
}

?>

<html>
    <head>
        <meta name="viewport" content="width=device-width">
        <script type="text/javascript" src="/static/jsonview.js"></script>
    </head>
    <body onload="xmaze('/static/default')">
        <div id="zone">
            <?php echo json_encode($result); ?>
        </div>
    </body>
</html>