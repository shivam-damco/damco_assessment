<?php

/**
 * Helper class to show flash messages
 * 
 * @author Harpreet Singh
 * @date   2 June, 2014
 * @version 1.0
 */

class Damco_View_Helper_FlashMessages {

    /**
     * Constructor to initialize Flash Messages class
     */
    function __construct( ) {
    }

    public function flashMessages( $messages = array() ) {
        $retString = '';
        if ( !empty( $messages ) ) {
            $retString = '<div class="">';
            foreach ( $messages as $value ) {
                if ( isset( $value['error'] ) ) {
                    $retString .= '<div class="alert alert-danger">';  
                    $retString .= '<a class="close" data-dismiss="alert">×</a>';
                    $retString .= '<strong>Error! </strong>';
                    $retString .= $value['error'].'</div';
                }
                if ( isset( $value['success'] ) ) {
                    $retString .= '<div class="alert alert-success">';  
                    $retString .= '<a class="close" data-dismiss="alert">×</a>';
                    $retString .= '<strong>Success: </strong>';
                    $retString .= $value['success'].'</div';
                }
            }
            $retString .= '</div></div></div>';
        }
        return $retString;
    }
}