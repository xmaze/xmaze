<?php

$url = strtok($_SERVER['REQUEST_URI'], '?');
if (substr($url, -1) != '/') {
    header('Location: '.$url.'/');
}


if (!file_exists('./door')) {
    mkdir('./door', 0777, true);
}

$room = json_decode(file_get_contents('./room.json'), true);
$data = json_decode(file_get_contents('./door.json'), true);
$door_list = $data['maze']['door'];
$door_names = array();


foreach ($door_list as &$value) {
    $door_name = $value['name'];
    array_push($door_names, $door_name);
    if (!file_exists('./door/'.$door_name)) {
    mkdir('./door/'.$door_name, 0777, true);
    }
    if (!file_exists('./door/'.$door_name.'/index.php')) {
    file_put_contents('./door/'.$door_name.'/index.php', "<?php include '../../../../templates/door.php'; ?>");
    }

    if (!in_array('./door/'.$door_name, $room['maze']['room'])) {
        array_push($room['maze']['room'], './door/'.$door_name);
    }
}


$directories = glob('./door/*', GLOB_ONLYDIR);

foreach ($directories as $key => $value) {

    $segments = explode('/', $value);
    $directory_name = $segments[count($segments)-1];
    
    if (!in_array($directory_name, $door_names)) {
        array_map('unlink', glob('./door/'.$directory_name.'/*.*'));
        rmdir('./door/'.$directory_name.'/');
    }
}

?>
<html>
<meta name="viewport" content="width=device-width">
<script type="text/javascript" src="/static/jsonview.js"></script>

<!-- ROOM STYLE -->
<body onload="xmaze('/static/default')">

<div id="zone">
<?php echo json_encode($room); ?>
</div>

</body>
</html>
