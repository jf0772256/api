<?php
    // Message service for Page Push Notifications
    if (isAjax()) {
        $jarray = json_decode($_POST['data'],true);
        switch ($jarray['request']) {
            case 'get':
                if (isset($jarray['msgreq'][0])) {
                    include_once("message.class.php");
                    $m = new PushMessage("DatabaseName","Host","User","password");
                    $s=$m->read_message($jarray['msgreq']);
                    echo json_encode($s);
                } else {
                    echo "{error:'Unable to complete, user or message id must be provided'}";
                }
                break;
            case 'post':
                if(isset($jarray['msgreq'][0])){
                    //
                    include_once("message.class.php");
                    $m = new PushMessage("DatabaseName","Host","User","password");
                    $t = $jarray['msgreq'][0];
                    $m->create_message($t['to'],$t['message'],$t['ttl'],$t['from'],$t['urgency'],$t['service']);
                }
                break;
            default:
                echo "{error:'No Request Found'}";
                break;
        }
    }else{
        die("You accessed this page with out proper authorization.");
    }
?>