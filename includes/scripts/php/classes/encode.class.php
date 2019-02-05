<?php
    namespace JesseFender\Encoder{

        class Encoder{
            private $Token;
            public function __construct($value){
                $this->Token = $value;
            }
            public function do_encode(){
                $this->Token=$this->make_url_safe(\base64_encode(\gzdeflate(\str_rot13($this->Token))));
                return $this->Token;
            }
            public function do_decode(){
                $this->Token = $this->make_b64_decodable(@\str_rot13(@\gzinflate(\base64_decode($this->Token))));
                return $this->Token;
            }
            private function make_url_safe($value){
                return \str_ireplace(['+','/','='],['.','_','-'],$value);
            }
            private function make_b64_decodable($value){
                return \str_ireplace(['.','_','-'],['+','/','='],$value);
            }
        }
    }
?>