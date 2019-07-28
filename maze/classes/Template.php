<?php

class Template {
    public function __construct() {}

    public function styleRoom($room, $doors, $format) {
        $style = array(
            "room" => array(
                "doors" => array()
            )
        );

        foreach($room[0] as $key => $val) {
            if (!in_array($key, ["name", "doors"])) {
                $style["room"][$key] = $val;
            }
        }

        foreach ($doors as $door) {

            if ($format === "json") {

                $door_properties = array();
                foreach ($door as $key => $val) {
                    if (!in_array($key, ["key", "redirect"])) {
                        $door_properties[$key] = $val;

                        if ($key === "name") {
                            $door_properties["endpoint"] = "/" . $room[0]->name . "/door/" . $val;
                        }
                    }
                }

                array_push($style["room"]["doors"], $door_properties);

            }
            else {

                array_push($style["room"]["doors"], "./door/" . $door->name);

            }


        }

        return $style;
    }

    public function styleDoor($door) {
        $door = $door[0];
        $door_properties = array();

        foreach ($door as $key => $val) {
            if (!in_array($key, ["name", "key", "redirect"])) {
                $door_properties[$key] = $val;
            }
        }
        $style = array(
            "door" => (object) $door_properties
        );

        return $style;
    }

}
