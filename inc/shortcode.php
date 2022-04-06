<?php namespace aoe

/**
 * @param device the name of the device to query "e.g. a0201"
 * @param gpio the pin number to query; for raspberry pi, this is the wiringPi number.
 * @return the value of the given gpio on the given device.
 */
function aoe_read_gpio($device, $gpio) {

    $url = "http://forwarder-1-svc:80/forward";
    
    $data = array();
    $data["device"] = $device;
    $data["gpio"] = $gpio;

    $url = sprintf("%s?%s", $url, http_build_query($data));
    
    $curl = curl_init();
    $result = curl_exec($curl);
    curl_close($curl);

    return $result
}
add_shortcode('aoe_read_gpio',  __NAMESPACE__ . '\\' . 'pod_field_from_user_profile');
