<?php namespace aoe;

/**
 * @param device the name of the device to query "e.g. a0201"
 * @param gpio the pin number to query; for raspberry pi, this is the wiringPi number.
 * @return the value of the given gpio on the given device.
 */
function aoe_read_gpio($args) {
    $url = "http://forwarder-1-svc:80/v1/gpio/get?device=".$args["device"]."&pin=".$args["gpio"];
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
add_shortcode('aoe_read_gpio',  __NAMESPACE__ . '\\' . 'aoe_read_gpio');

function aoe_current_device_status() {
    $device = pods('device', get_the_ID());
    $read_arr = array();
    $read_arr["device"] = $device->field("controller_name");
    $read_arr["gpio"] = $device->field("gpio_pin");
    return aoe_read_gpio($read_arr);
}
add_shortcode('aoe_current_device_status',  __NAMESPACE__ . '\\' . 'aoe_current_device_status');

/**
 * @param device the name of the device to query "e.g. a0201"
 * @param gpio the pin number to query; for raspberry pi, this is the wiringPi number.
 * @return NULL.
 */
function aoe_toggle_gpio($args) {
    $url = "http://forwarder-1-svc:80/v1/gpio/set";
    $payload = json_encode(array(
        "device" => $args["device"],
        "pin" => $args["gpio"],
        "state" => "TOGGLE"
    ));
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    //$result is unused.
    return;
}
add_shortcode('aoe_toggle_gpio',  __NAMESPACE__ . '\\' . 'aoe_toggle_gpio');

function aoe_toggle_current_device() {
    $device = pods('device', get_the_ID());
    $write_arr = array();
    $write_arr["device"] = $device->field("controller_name");
    $write_arr["gpio"] = $device->field("gpio_pin");
    aoe_toggle_gpio($write_arr);
    header("refresh:5;url=https://aoegreens.com/devices/");
}
add_shortcode('aoe_toggle_current_device',  __NAMESPACE__ . '\\' . 'aoe_toggle_current_device');

function aoe_dumb_bg_image() {
    $image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail' );
    return "style = \"background-image: url('".$image[0].1"')\"";
}
add_shortcode('aoe_dumb_bg_image',  __NAMESPACE__ . '\\' . 'aoe_dumb_bg_image');
