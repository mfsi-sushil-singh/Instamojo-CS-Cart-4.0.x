<?php

use Tygh\Registry;


function url_handler($url){
    if(strpos($url, '?') === FALSE){
        return $url . "?";
    }
    else{
        return $url . '&';
    }
}

function instamojo_error_logger($msg, $add_newline=TRUE){

    $base_dir = dirname(dirname(dirname(__FILE__)));
    $LOG_FILE = $base_dir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'imojo.log';
    date_default_timezone_set('Asia/Calcutta');
    $date = date('m/d/Y h:i:s a', time());

    $msg = $date . " | " . $msg;

    if($add_newline){
        $msg .= "\n";
    }
    error_log($msg, 3, $LOG_FILE);
}

// http://stackoverflow.com/a/768469/846892
function Redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}


if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'process') {
        $payment_id = $_REQUEST['payment_id'];
        // Get Instamojo details from backend
        instamojo_error_logger("Callback called with Payment ID: " . $payment_id);
        $processor_details = fn_get_processor_data_by_name('instamojo.php');
        $pdata = db_get_row("SELECT * FROM ?:payments WHERE processor_id = ?i", $processor_details['processor_id']);
        instamojo_error_logger("Instamojo processor details from DB: ". print_r($pdata, true));
        $processor_details['processor_params'] = unserialize($pdata['processor_params']);
        instamojo_error_logger("Instamojo's settings from DB: ". print_r($processor_details['processor_params'], true));
        $api_key = $processor_details['processor_params']['instamojo_api_key'];
        $auth_token = $processor_details['processor_params']['instamojo_auth_token'];
        $custom_field = $processor_details['processor_params']['instamojo_custom_field'];
        # Callback Instamojo
        $response = check_instamojo_payment_status($api_key, $auth_token, $payment_id);
        instamojo_error_logger ("$api_key | $auth_token | $custom_field");
        instamojo_error_logger ("Response from Instamojo is: " . print_r($response, true));
        instamojo_error_logger (print_r($response, true));
        
        $pp_response = array();

        if($response['payment']['status'] == 'Credit'){
            instamojo_error_logger ("Payment credited for Payment ID: " . $payment_id);
            $order_id = $response['payment']['custom_fields'][$custom_field]['value'];
            instamojo_error_logger ("And the Order ID is: " . $order_id);
            $order_info = fn_get_order_info($order_id);            
            $pp_response['order_status'] = 'P';
            $pp_response['transaction_id'] = $payment_id;

            if (fn_check_payment_script('instamojo.php', $order_id)){
                fn_finish_payment($order_id, $pp_response, false);
            }
        }
        else if(!empty($response['payment']['status'])){

            // Non-empty status means either the payment was not done or the Payment ID they are trying to access doesn't exist.
            instamojo_error_logger("Non-empty status but not equal to Credit: " . $response['payment']['status']);

            if(isset($response['payment']['custom_fields'])){
                instamojo_error_logger("Non-empty status withbut custom fields, i.e no way to get any Order ID here.");
                $order_id = $response['payment']['custom_fields'][$custom_field]['value'];

            }
            
            if(!empty($order_id)){
                // No BS.
                instamojo_error_logger("Order ID found but Payment was not credited for :" . $payment_id);
                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = $response['payment']['status'];
                fn_finish_payment($order_id, $pp_response, false);
            }
            else{
                // Pure BS.
                instamojo_error_logger("No Order ID found either, no need to do anything.");
                fn_order_placement_routines('route');
            }
            
        }
        else{
            instamojo_error_logger("Empty status from Server for Payment ID: " . $payment_id);
            fn_order_placement_routines('route');
        }
        fn_order_placement_routines('route', $order_id);
    }

} else {

    $post_data = array();

    $currencies = Registry::get('currencies');
    $currency_code = $processor_data['processor_params']['instamojo_currency_code'];

    instamojo_error_logger("Currency code fetched from settings: $currency_code");

    $amount = fn_format_price($order_info['total'] / $currencies[$currency_code]['coefficient']);
    $payment_url = $processor_data['processor_params']['instamojo_payment_url'];
    $api_key = $processor_data['processor_params']['instamojo_api_key'];
    $auth_token = $processor_data['processor_params']['instamojo_auth_token'];
    $private_salt = $processor_data['processor_params']['instamojo_private_salt'];

    // $amount = $order_info['total'];
    $email = substr($order_info['email'], 0, 75);
    $phone = substr($order_info['phone'], 0, 20);
    $name = substr(trim($order_info['b_firstname'] . ' ' . $order_info['b_lastname']), 0, 20);
    $custom_field = 'data_' . $processor_data['processor_params']['instamojo_custom_field'];

    $data = array();
    $data['data_amount'] = $amount;
    $data['data_email'] = $email;
    $data['data_name'] = $name;
    $data['data_phone'] = $phone;
    $data[$custom_field] = $order_id;

    $ver = explode('.', phpversion());
    $major = (int) $ver[0];
    $minor = (int) $ver[1];

    instamojo_error_logger("$api_key | $auth_token | $private_salt | $payment_url");

    instamojo_error_logger("Data before sorting: " . print_r($data, true));

    if($major >= 5 and $minor >= 4){
        ksort($data, SORT_STRING | SORT_FLAG_CASE);
    }
    else{
        uksort($data, 'strcasecmp');
    }

    instamojo_error_logger("Data after sorting: " . print_r($data, true));

    $str = hash_hmac("sha1", implode("|", $data), $private_salt);

    instamojo_error_logger("Signature is: $str");

    $link= url_handler($payment_url) . "intent=buy&emded=form&";
    $link.="data_readonly=data_email&data_readonly=data_amount&data_readonly=data_phone&data_readonly=data_name&data_readonly={$custom_field}&data_hidden={$custom_field}";
    $link.="&data_amount=$amount&data_name=$name&data_email=$email&data_phone=$phone&{$custom_field}=$order_id&data_sign=$str";

    instamojo_error_logger("Marking Order: $order_id as open before redirecting to Instamojo for payment.");

    fn_change_order_status($order_id, 'O');

    Redirect($link);

exit;
}


function check_instamojo_payment_status($api_key, $auth_token, $payment_id){
    instamojo_error_logger("Calling Instamojo for Payment ID: $payment_id with API: $api_key and AUTH: $auth_token");
    $cUrl = 'https://www.instamojo.com/api/1.1/payments/' . $payment_id . '/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cUrl);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Api-Key:$api_key",
                                               "X-Auth-Token:$auth_token"));
    $response = curl_exec($ch);
    $error_number = curl_errno($ch);
    $error_message = curl_error($ch);
    curl_close($ch);
    instamojo_error_logger("Error number from cURL: $error_number");
    instamojo_error_logger("Error message from cURL: $error_message");
    $response_obj = json_decode($response, true);
    instamojo_error_logger("Response from CURL is: " . print_r($response_obj, true));
    if($response_obj['success'] == false) {
        $message = json_encode($response_obj['message']);
        return Array('payment' => Array('status' => $message));
    }
    if(empty($response_obj) || is_null($response_obj)){
        return Array('payment' => Array('status' => 'No response from the server.'));
    }
    else{
        return $response_obj;
    }
}
