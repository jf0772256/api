<?php
    namespace JesseFender\Database{
        /**
         * Class to handle read only aspects of the database world
         */
        class Database
        {
            /**
             * Connection object
             *
             * @var object
             */
            private $dbConn;
            /**
             * Database Name
             *
             * @var string
             */
            private $dbname;
            /**
             * Database Host
             *
             * @var string
             */
            private $dbhost;
            /**
             * Database User
             *
             * @var string
             */
            private $dbuser;
            /**
             * Database Password
             *
             * @var string
             */
            private $dbpw;
            /**
             * Database class connects and does work related to database.
             *
             * @param string $databaseName The name of the database that is being connected to.
             * @param string $dataHost The host address where the database can be found.
             * @param string $user The user that will be connecting to the database.
             * @param string $password The password for that user in order to authenticate to the server.
             */
            protected function __construct($databaseName, $dataHost, $user, $password){
                $this->dbname = $databaseName;
                $this->dbhost = $dataHost;
                $this->dbuser = $user;
                $this->dbpw = $password;
                $this->dbConn = new \mysqli($this->dbhost, $this->dbuser, $this->dbpw, $this->dbname);
                if ($this->dbConn->connect_errno) {
                    echo "There was an error when attempting to connect to " . $this->dbname . " with user " . $this->dbuser . ".";
                    die();
                }
            }
            /**
             * Checks current connection
             *
             * @return boolean
             */
            protected function checkConnection(){
                if ($this->dbConn->ping()) {
                    return true;
                } else {
                    return false;
                }
            }
            protected function get_db_connection(){
                return $this->dbConn;
            }
            /**
             * Query is prepared and escaped before execution.
             * Reads data from database.
             * Returns a values array as 'result_set', status and message and insert id will be null
             *
             * @param string $sql (STRING)SQL string to prepare
             * @param array $bavArray (ARRAY)Values to be bound to the statement object.
             * @return array
             */
            protected function executePreparedReadingQuery($sql, $bavArray){
                if (!is_string($sql) || empty($sql)) {
                    return ["result"=>false,"message"=>"SQL statement in an incorrect format or empty.", "insert_id"=>null,"result_set"=>[]];
                }
                if ($stmnt = $this->dbConn->prepare($sql)) {
                    // bind params if they are set
                    if (!empty($bavArray)) {
                        $types = '';
                        foreach ($bavArray as $param) {
                            // set param type
                            switch ($param) {
                                case is_string($param) == true:
                                    $types .= 's';  // strings
                                    break;
                                case is_int($param) == true:
                                    $types .= 'i';  // integer
                                    break;
                                case is_float($param) == true:
                                    $types .= 'd';  // double
                                    break;
                                default:
                                    $types .= 'b';  // default: blob and unknown types
                            }
                        }

                        $bind_names[] = $types;
                        for ($i = 0; $i < count($bavArray); $i++) {
                            $bind_name = 'bind' . $i;
                            $$bind_name = $bavArray[$i];
                            $bind_names[] = &$$bind_name;
                        }

                        call_user_func_array(array($stmnt, 'bind_param'), $bind_names);
                    }
                    $stmnt->execute();
                    if (!$stmnt) {
                        return ["result"=>false,"message"=>"(Reading) SQL statement could not be executed: " . $stmnt->error, "insert_id"=>null,"result_set"=>[]];
                        exit;
                    }
                    $values = array();
                    $stmnt->store_result();
                    $meta = $stmnt->result_metadata();
                    $fields = array();
                    while ($field = $meta->fetch_field()) {
                        $var = $field->name;
                        $$var = null;
                        $fields[$var] = &$$var;
                    }
                    //echo var_dump($stmnt,$meta,$fields);
                    call_user_func_array(array($stmnt, 'bind_result'), $fields);
                    $i = 0;
                    while ($stmnt->fetch()) {
                        $values[$i] = [];
                        foreach ($fields as $k => $v)
                            $values[$i][$k] = $v;
                        $i++;
                    }
                    $stmnt->free_result();
                    return ["result"=>true,"message"=>null,"insert_id"=>null,"result_set"=>$values];
                }else{
                    $msg = $this->dbConn->error;
                    return ["result"=>false,"message"=>"(Reading) SQL statement could not be prepared: " . $msg, "insert_id"=>null,"result_set"=>[]];
                }
            }
            /**
             * Query is prepared and escaped before execution.
             * writes(update) data in the database.
             * Returns class standard array [result=>bool,message=>string,insert_id=>(Int,Null)]
             *
             * @param string $sql Statement to be prepared, bound and executed. Should be a DML query.
             * @param array $bavArray Send the correct number of parameters in an array, if no binding is being done pass an empty array like: [] or new array()
             * @return array
             */
            protected function executePreparedWriteingQuery($sql, $bavArray){
                if (!is_string($sql) || empty($sql)) {
                    return ['result'=>false,"message"=>"SQL statement in an incorrect format or empty.","insert_id"=>null,"result_set"=>[]];
                }
                if ($stmnt = $this->dbConn->prepare($sql)) {
                    // bind params if they are set
                    if (!empty($bavArray)) {
                        $types = '';
                        foreach ($bavArray as $param) {
                            // set param type
                            switch ($param) {
                                case is_string($param) == true:
                                    $types .= 's';  // strings
                                    break;
                                case is_int($param) == true:
                                    $types .= 'i';  // integer
                                    break;
                                case is_float($param) == true:
                                    $types .= 'd';  // double
                                    break;
                                default:
                                    $types .= 'b';  // default: blob and unknown types
                            }
                        }

                        $bind_names[] = $types;
                        for ($i = 0; $i < count($bavArray); $i++) {
                            $bind_name = 'bind' . $i;
                            $$bind_name = $bavArray[$i];
                            $bind_names[] = &$$bind_name;
                        }

                        call_user_func_array(array($stmnt, 'bind_param'), $bind_names);
                    }
                    $result = $stmnt->execute();
                    if (!$result) {
                        $msg = $stmnt->error;
                        $stmnt->free_result();
                        return ["result"=>false,"message"=>"SQL statement has errors and could not be completed: " . $msg, "insert_id"=>null,"result_set"=>[]];
                    }else{
                        $id = $this->dbConn->insert_id;
                        $stmnt->free_result();
                        return ["result"=>true,"message"=>"Data has been written to the database with out error.","insert_id"=>(substr(strtolower($sql),0,6)=="insert")?$id:null,"result_set"=>[]];
                    }
                }else{
                    $msg = $this->dbConn->error;
                    return ["result"=>false,"message"=>"(Writing) SQL statement could not be prepared: " . $msg, "insert_id"=>null,"result_set"=>[]];
                }
            }
            /**
             * Query is prepared and escaped before execution.
             * Writes or Updates data in the database. ONLY DO WRITES WITH THIS METHOD!!!
             * Returns class standard array [result=>bool,message=>string,insert_id=>(Int,Null)]
             *
             * @param string $sql Statement to be prepared, bound and executed. Should be a DML query.
             * @return array
             */
            protected function executeMultiWriteingQuery($sql){
                if (!is_string($sql) || empty($sql)) {
                    return ['result'=>false,"message"=>"SQL statement in an incorrect format or empty.","insert_id"=>null,"result_set"=>[]];
                }
                $result = $this->dbConn->multi_query($sql);
                if (!$result) {
                    return ['result'=>false,"message"=>"SQL statement has errors and could not be completed: " . $this->dbConn->error,"insert_id"=>null,"result_set"=>[]];
                }else{
                    $id = $this->dbConn->insert_id;
                    try{
                        //use supression to allow completion of action with out folly;
                        while (@$this->dbConn->next_result()) {;}
                    }
                    catch(Exception $e){
                        //do nothing
                    }
                    finally{
                        return ['result'=>true,"message"=>"SQL ran successfully.","insert_id"=>(substr(strtolower($sql),0,6)=="insert")?$id:null,"result_set"=>[]];
                    }
                }
            }
        }
        /**
         * PublicDatabase is the public wrapper to the protected class, This is so that you can use it rather than sticking the class as a extended value
         * which you still can do, but this just gives a bit more public access as needed.
         *
         * Please note though that this option is not as fully featured as the protected class actions so you can only read and write using the prepared options.
         */
        class PublicDatabase extends Database
        {
            /**
             * Constructor
             *
             * @param string $d Database name
             * @param string $h Database host
             * @param string $u User to connect with
             * @param string $p Password for the user
             */
            public function __construct($d,$h,$u,$p){
                parent::__construct($d,$h,$u,$p);
            }
            /**
             * Writes to the database
             *
             * @param string $sql SQL Query
             * @param array $value_array parameter array, empty if no parameters
             * @return array
             */
            public function write_to_database($sql,$value_array){
                return parent::executePreparedWriteingQuery($sql,$value_array);
            }
            /**
             * Reads from the database
             *
             * @param string $sql SQL Query
             * @param array $value_array parameter array, empty if no parameters
             * @return array
             */
            public function read_from_database($sql,$value_array){
                return parent::executePreparedReadingQuery($sql,$value_array);
            }
            /**
             * Checks that the connection is active, returns either true or false.
             *
             * @return bool
             */
            public function check_connection(){
                $val = parent::checkConnection();
                return ($val==1||val==true)?true:false;
            }
        }
    }
?>