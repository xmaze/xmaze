<?php
    // TODO: limit links to only ones in urls in data/data.json values
    $url = $_REQUEST["url"];

    if (substr ($url, 0, 7) != "http://"
        && substr ($url, 0, 8) != "https://"
        && substr ($url, 0, 6) != "ftp://") {
        die("ERROR: The argument 'url' must be an absolute URL beginning with 'http://', 'https://', or 'ftp://'.");
    }

    ini_set("user_agent", $_SERVER['HTTP_USER_AGENT']);

    enable_cors();

    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            get($url);
            break;
        default:
            post($url);
            break;
    }


    function get($url) {
            $contents = file_get_contents($url);
            if (strlen($contents) > 0) {
                echo $contents;
            }
            else {
                http_response_code(404);
                die();
            }
        // }
    }

    function enable_cors() {
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');;
            header('Access-Control-Max-Age: 86400');
        } else {
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Credentials: true');;
            header('Access-Control-Max-Age: 86400');
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    }
?>
