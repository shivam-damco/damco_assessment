<?php

class Damco_Db_Adapter_Mysqli extends Zend_Db_Adapter_Mysqli {

    function processSpCall($call) {
        // (profiling start...)
        // process database call
        $results = array();
        if ($this->getConnection()->multi_query($call)) {
            do {
                if ($result = $this->getConnection()->store_result()) {
                    $results[] = $result;
                } else {
                    $finished = true;
                }
            } while (@$this->getConnection()->next_result());

            if (empty($finished)) {
                if (count($results)) {
                    // get last result
                    $last_result = array_pop($results);
                }
                // throw last error encounted
                throw new Zend_Db_Adapter_Mysqli_Exception($this->getConnection()->errno, $this->getConnection()->error, $this->getConnection()->info, $call);
            }

            if (count($results) == 1) {
                // one mysqli object only (return single result set - or choose to always return an array...
                $results = $results[0];
            }
        } else {
            throw new Zend_Db_Adapter_Mysqli_Exception($this->getConnection()->error);
        }
        // (profiling end...)
        // save result sets in private/protected property? Or save as rowsets (= result set)
        $this->_results = $results;
    }

    /*
      function getSPparams(array $out_params) {
      $out_params = "@" . implode(', @', $out_params);
      $result = $this->getConnection()->query("select $out_params");
      $value_array = $result->fetch_array();

      // save result in object
      $values = new stdObject();
      foreach ($out_params AS $i => $value) {
      $values->$value = $value_array[$i];
      }
      return $values;
      }

      function getSPparam($out_param) {
      $result = $this->getConnection()->query("select @$out_param");
      $row = $result->fetch_array();
      $value = $row[0];
      return $value;
      }
     */

    // helper method to build the call string with escaped parameters - should be
    // refactored using the available adapter methods for escaping... but here's the idea
    function getSpCallString($sp_name, $in_params, $debug, $out_params, $length ) {
        $vars_string = '';
        $i = 0;
        $magic_quotes = false;

        // IN PARAMETERS
        if (is_array($in_params)) {
            // multiple in_params

            $last = count($in_params);
            foreach ($in_params AS $var) {
                $i++;
                // adjust length of each string
                if ($length) {
                    $var = (strlen($var) <= $length) ? $var : substr($var, 0, $length) . '...';
                }
                // Stripslashes
                if ($magic_quotes) {
                    $var = stripslashes($var);
                }
                // passed value
                if (is_numeric($var) AND preg_match('/e/', $var) == 0) {
                    // integer/float (not exponential)
                    $vars_string .= "'" . $var . "'";
                } elseif ($var) {
                    // string
                    $vars_string .= "'" . $this->getConnection()->real_escape_string(trim($var)) . "'";
                } elseif ($var === NULL) {
                    // NULL
                    $vars_string .= "''";
                } else {
                    // empty
                    $vars_string .= "''";
                }
                $vars_string .= $i == $last ? "" : ", ";
            }
        } elseif ($in_params != '_NOT_PASSED') {
            // single in_param
            // adjust length of each string
            if ($length) {
                $var = (strlen($var) <= $length) ? $var : substr($var, 0, $length) . '...';
            }
            // single parameter
            if ($magic_quotes) {
                // Stripslashes
                $in_params = stripslashes($in_params);
            }
            // passed value
            if (is_numeric($in_params) AND preg_match('/e/', $in_params) == 0) {
                // integer/float (not exponential)
                $vars_string = "'" . $in_params . "'";
            } elseif ($in_params) {
                // string
                $vars_string = "'" . $this->getConnection()->real_escape_string($in_params) . "'";
            } elseif ($in_params === NULL) {
                // NULL
                $vars_string = "NULL";
            } else {
                // empty
                $vars_string = "''";
            }
        }

        // OUT PARAMETERS
        if (isset($out_params)) {
            if (is_array($out_params) AND count($out_params)) {
                $vars = array();
                foreach ($out_params AS $var) {
                    if ($var) {
                        $vars[] = "@" . $this->getConnection()->real_escape_string(trim($var));
                    }
                }
                $vars_string .= ", " . implode(", ", $vars);
            } elseif ($out_params) {
                $vars_string .= $vars_string ?
                        ", @" . $this->getConnection()->real_escape_string(trim($out_params)) :
                        "@" . $this->getConnection()->real_escape_string(trim($out_params));
            }
        }
        //echo "<br>CALL $sp_name($vars_string)";//die;
        
        if ( $debug ) {
            echo "<!-- CALL $sp_name($vars_string) -->";die;
        }
        return "CALL $sp_name($vars_string)";
    }

    /**
     * Method to call and return Stored Procedure data
     * @param type $sp_name
     * @param type $in_params
     * @param type $debug
     * @param type $out_params
     * @param type $length
     * @return type
     */
    function getSpData($sp_name, $in_params, $debug = FALSE, $out_params = null,
        $length = null) {
        
        $this->processSpCall(
            $this->getSpCallString($sp_name, $in_params, $debug, $out_params, $length)
        );
        
        $DataArr = array();
        $Results = $this->_results;
        if (is_array($Results)) {
            foreach ($Results as $Result) {
                $DataRow = array();

                while ($Row = mysqli_fetch_assoc($Result)) {
                    $DataRow[] = $Row;
                }
                $DataArr[] = $DataRow;
            }
        } else {
            $DataRow = array();

            while ($Row = mysqli_fetch_assoc($Results)) {
                $DataRow[] = $Row;
            }
            $DataArr = $DataRow;
        }
        
//        if ( $debug ) {
//            echo '<pre>';
//            print_r($DataArr);
//            exit;
//        }
        return $DataArr;
    }

}