<?php
$data = json_decode(file_get_contents('../../door.json'), true);
$door_list = $data['maze']['door'];

// find the door based on URL //
$url = strtok($_SERVER['REQUEST_URI'], '?');

$segments = explode('/', $url);
$door_name = $segments[count($segments)-1];
if ($door_name == '' & !($door_name == 'door')) {
  $door_name = $segments[count($segments)-2];
}

foreach ($door_list as &$value) {
    if ($value['name'] == $door_name) {
        $door = $value;
    }
}

if ($_GET['key'] == $door['key']) { 
    header('Location: '.$door['redirect']);
}
else { ?>
<html>
<meta name="viewport" content="width=device-width">
<script type="text/javascript" src="/static/jsonview.js"></script>

<!-- DOOR STYLE -->
<body onload="xmaze('/static/default')">

<div id="x0n">
<?php 
    // Remove the answer.
    unset($door['name']);
    unset($door['key']);
    unset($door['redirect']);
	// Display the question.
    echo json_encode(array("maze" => array( "door" => $door)));
?>
</div>

</body>
</html>
<?php } ?>
