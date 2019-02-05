<?php
    namespace JesseFender\Message{
        // Message service for Page Push Notifications
        /**
         * Function that checks if a request is don through ajax request
         *
         * @return boolean
         */
        function is_ajax() {
            return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        }

        if (is_ajax()) {
            $jarray = json_decode($_POST['data'],true);
            switch ($jarray['request']) {
                case 'get':
                    if (isset($jarray['msgreq'][0])) {
                        include_once("message.class.php");
                        $m = new PushMessage("checkin_system","localhost","checkInUser","LAylBcGM64j09gQ6");
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
                        $m = new PushMessage("checkin_system","localhost","checkInUser","LAylBcGM64j09gQ6");
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
    }
?>