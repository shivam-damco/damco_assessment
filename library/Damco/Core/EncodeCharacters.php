<?php

/**
 * @author  Harpreet Singh
 * @date    28 July, 2014
 * @version 1.0
 * 
 * Class to handle junk characters  
 */
class Damco_Core_EncodeCharacters {
    public function encode( $str ) {
        return  iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($str));
    }
}
