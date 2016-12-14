<?php
// For the rooms, we need urls to end with "/".
$url = strtok($_SERVER['REQUEST_URI'], '?');
if (substr($url, -1) != '/') {
    header('Location: '.$url.'/');
}

// generate/update the dummy door folders for the room.
if (!file_exists('./door')) {
    mkdir('./door', 0777, true);
}

$room = json_decode(file_get_contents('./room.json'), true);
$data = json_decode(file_get_contents('./door.json'), true);
$door_list = $data['maze']['door'];
$door_names = array();

// add folders that are in the door.json
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

// remove folders that are not in door.json
$directories = glob('./door/*', GLOB_ONLYDIR);

foreach ($directories as $key => $value) {
    // $directory_name = substr($value, 2);
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

<div id="x0n">
<?php echo json_encode($room); ?>
</div>

</body>
</html>