<?php
namespace JesseFender\Database\V2{
  class Database{
    private $connection;
    private $allow_transactions;
    private $sql_string;
    private $values_array;
    private $transaction_inprogress;
    private $stmnt;
    private $errors;
    private $result;
    protected function __construct(string $name, string $host, string $user, string $password, ?bool $allow_transactions = FALSE){
      $this->connection=new mysqli($host,$user,$password,$name);
      $this->allow_transactions=$allow_transactions;
      if (!$this->connection->connect_errno) {
        die("There was a connection error and we had to abort. " . $this->connection->connect_error);
      }
    }
    protected function check_connection():bool{
      return ($this->connection->ping())?TRUE:FALSE;
    }
    protected function set_sql_string(string $value){
      $this->sql_string=$value;
    }
    protected function set_values_array(array $values){
      $this->values_array=$values;
    }
    protected function set_allow_transactions(bool $value){
      $this->allow_transactions = $value;
    }
    protected function get_results():array{
      $x=$this->result;
      $this->result=NULL;
      return $x;
    }
    protected function trans_inprogress():bool{
      return $this->transaction_inprogress?TRUE:FALSE;
    }
    protected function allows_trans():bool{
      return $this->allow_transactions?TRUE:FALSE;
    }
    protected function prepare_sql(){
      $this->stmnt=$this->connection->prepare($this->sql_string);
      if(!$this->stmnt){
        $this->errors=$this->stmnt->error;
      }
    }
    protected function bind_values(){
      if (empty($this->$errors)) {
        if (!empty($this->values_array)) {
            $types = '';
            foreach ($this->values_array as $param) {
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
            for ($i = 0; $i < \count($this->values_array); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $this->values_array[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($this->stmnt, 'bind_param'), $bind_names);
        }
      }else{
        throw new DatabaseError("Error Processing Request: " . $this->error, 1);
      }
    }
    protected function read(){
      if (empty($this->errors)) {
        $stmnt->execute();
        if (!$stmnt) {
          $this->errors= $stmnt->error;
        } else {
          $values = [];
          $this->stmnt->store_result();
          $meta = $this->stmnt->result_metadata();
          $fields = [];
          while ($field = $meta->fetch_field()) {
            $var = $field->name;
            $$var = null;
            $fields[$var] = &$$var;
          }
          //echo var_dump($stmnt,$meta,$fields);
          call_user_func_array(array($this->stmnt, 'bind_result'), $fields);
          $i = 0;
          while ($this->stmnt->fetch()) {
            $values[$i] = [];
            foreach ($fields as $k => $v)
              $values[$i][$k] = $v;
            $i++;
          }
          $rc=$this->stmnt->affected_rows;
          $this->stmnt->free_result();
          if (!$this->transaction_inprogress) {
            $this->result = ["result"=>true,"message"=>null,"insert_id"=>null,"row_count"=>$rc,"result_set"=>$values];
          }
        }
      }
      if (!empty($this->errors)) {
        throw new DatabaseError("Error Processing Request: " . $this->errors, 1);
      }  
    }
    protected function write(){
      $result = $this->stmnt->execute();
      if (!$result) {
          $msg = $stmnt->error;
          $stmnt->free_result();
          if (!$this->transaction_inprogress) {
            $this->errors = "SQL statement has errors and could not be completed: " . $msg;
          } else {
            $this->errors[] = "SQL statement has errors and could not be completed: " . $msg;
          }
      }else{
          $id = $this->connection->insert_id;
          $rc = $this->connection->affected_rows;
          $this->stmnt->free_result();
          if (!$this->transaction_inprogress) {
            $this->result = ["result"=>true,"message"=>"Data has been written to the database with out error.","insert_id"=>(substr(strtolower($sql),0,6)=="insert")?$id:null,"row_count"=>$rc,"result_set"=>[]];
          }
      }
      if (!empty($this->errors)) {
        throw new DatabaseError("Error Processing Request: " . $this->errors, 1);
      }
    }
    protected function begin(){
      if ($this->allow_transactions) {
        $this->connection->autocommit(FALSE);
        $this->transaction_inprogress=TRUE;
      } 
    }
    protected function commit(){
      if ($this->transaction_inprogress) {
        $this->connection->commit();
        $this->connection->autocommit(TRUE);
        $this->transaction_inprogress=FALSE;
      }
    }
    protected function rollback(){
      if ($this->transaction_inprogress) {
        $this->connection->rollback();
        $this->connection->autocommit(TRUE);
        $this->transaction_inprogress=FALSE;
      }
    }
  }
  class DatabaseError extends \Exception {
    public function __construct(string $message,int $code,?\Throwable $prev=NULL){
      parent::__construct($message,$code,$prev);
    }
  }
  class PublicDatabase{
    public function __construct($name,$host,$user,$password,$transactions_allowed){
      parent::__construct($name,$host,$user,$password,$transactions_allowed);
    }
    public function check():bool{
      return parent::check_connection()?TRUE:FALSE;
    }
    public function allow_transactions(bool $value):self{
      parent::set_allow_transactions($value);
      return $this;
    }
    public function get_results():array{
      return parent::get_results();
    }
    public function begin(){
      if (allows_trans()&&!trans_inprogress()){
        parent::begin();
      }
    }
    public function commit(){
      if (allows_trans()&&trans_inprogress()){
        parent::commit();
      }
    }
    public function rollback(){
      if (allows_trans()&&trans_inprogress()){
        parent::rollback();
      }
    }
    public function write_to_database(string $sql, array $values){
      parent::set_sql_string($sql);
      parent::set_values_array($values);
      parent::prepare_sql();
      parent::bind_values();
      parent::write();
      if (!$transaction_inprogress) {
        return parent::get_results();
      }
    }
    public function read_from_database(string $sql, array $values){
      parent::set_sql_string($sql);
      parent::set_values_array($values);
      parent::prepare_sql();
      parent::bind_values();
      parent::read();
      if (!$transaction_inprogress) {
        return parent::get_results();
      }
    }
  }
}

?>