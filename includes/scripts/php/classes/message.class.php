<?php
    namespace JesseFender\Message{
        
        /**
         * PushMessage Class inteacts directly with the json file and adds, edits, reads, and deletes messages from the messages.json file.
         */
        class PushMessage
        {

            private $dbc;

            /**
             * Class Constructor - also connects to the database using the supplied parameters
             *
             */
            public function __construct($db){
                $this->dbc = $db;
                $this->createTables();
            }
            /**
             * Function to create message table in database
             */
            private function createTables(){
                $s = "CREATE TABLE IF NOT EXISTS grc_messages(id BIGINT PRIMARY KEY AUTO_INCREMENT NOT NULL, 
                        _from VARCHAR(150) NOT NULL, 
                        _read TINYINT(1) NOT NULL DEFAULT 0, 
                        _to INT NOT NULL, 
                        TTL BIGINT NOT NULL DEFAULT 0, 
                        sender VARCHAR(150) NOT NULL, 
                        created BIGINT NOT NULL DEFAULT 0, 
                        _message VARCHAR(5000) NOT NULL, 
                        urgency VARCHAR(25) DEFAULT 'low'
                    )";
                $this->dbc->write_to_database($s,[]);
            }
            /**
             * Creates a message row in the database table for messages to be recalled by read
             * @param mixed $to Employee number to which message will be displayed to. may be integer or string represeining an integer number eg "1" will work, however "one" will not.
             * @param string $message Message that will be displayed to the Employee
             * @param mixed $ttl Message time out, not used as of 02/0/2018 but planned to be implemented, and will be used to remove old messages from the database table to maintain as small a footrprint as possible. Mixed values should be simular to the '$to' field, 1 or "1", not "one".
             * @param string $from Where is the message comming from, This will be displayed to the user as a header, so should be short and to the point, I would say no more than 25 chars, but there is not really a limit, but max width is 300px.
             * @param string $urgency Urgency messages have yet to be fully implemented, Thus all messages are defaulted to "low", please use this value until priority can be worked out.
             * @param string $service Special value for name of the service of the messenger, for instance check in would have check in notifier. This doesnt mean much at this time but will have more weight later on.
             * @return void
             */
            public function create_message($to, $message, $ttl, $from, $urgency, $service) {
                $s = "INSERT INTO grc_messages(_from,
                        _to,
                        sender,
                        TTL,
                        created,
                        _message,
                        urgency,
                        _read
                        )VALUES(
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        )";
                $b = [
                    $from,
                    (int)$to,
                    $service,
                    (int)$ttl,
                    time(),
                    $message,
                    $urgency,
                    0
                ];
                $this->dbc->write_to_databse($s, $b);
            }
            /**
             * Retreives all messages for user, and returns the values to an array, then marks the messages as being read so that they dont repeat.
             * @param array $f array contaning user details
             * @return array
             */
            public function read_message($f){
                $e = [];
                $m = [];
                $c = 0;
                $v = [];
                $s = "SELECT * FROM messages WHERE _to = ? AND _read = 0";
                $b = [(int)$f[0]['usr']];
                $e = $this->dbc->read_from_database($s, $b);
                for ($i = 0; $i < count($e); $i++) {
                    $v[] = (int)$e[$i]['id'];
                }
                $m = $e;
                if (count($v)>0) {
                    $this->dbc->write_to_database("UPDATE grc_messages SET _read = 1 WHERE id = ?",$v);
                }
                return $m;
            }
            public function delete_message(){
                //
            }
        }
    }
?>