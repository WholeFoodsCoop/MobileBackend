<?php
/**
  Webservices URL to collect mobile device
  tokens for push notifications
*/
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
if (!class_exists('PhpAutoLoader')) {
    require(dirname(__FILE__) . '/../../vendor-code/PhpAutoLoader/PhpAutoLoader.php');
}

if (is_array($input) && isset($input['token']) && isset($input['platform'])) {
    $dbc = Database::pDataConnect();
    $chkP = $dbc->prepare("SELECT * FROM PushRegistrations WHERE token=?");
    $chkR = $dbc->execute($chkP, array($input['token']));
    if ($dbc->num_rows($chkR) == 0) {
        $prep = $dbc->prepare("
            INSERT INTO PushRegistrations
                (token, platform, added)
            VALUES
                (?, ?, NOW())");
        $params = array($input['token'], $input['platform']);
        $res = $dbc->execute($prep, $params);
        if ($res) {
            http_response_code(200);
            echo "OK\n";
        } else {
            http_response_code(500);
            echo "Internal Server Error\n";
        }
    } else {
        // token already known
        http_response_code(200);
        echo "OK\n";
    }
} else {
    http_response_code(400);
    echo "Bad Request\n";
}

