<?php

/**
 * Function to enforce xmlhttprequest...
 *
 * @return boolean
 */
function isAjax()
{
  return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

/**
 * Returns the method used for the ajax call
 *
 * @return string
 */
function get_method()
{
  return filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_ENCODED);
}

/**
 * Should return an array of values passed via ajax
 *
 * @return array
 */
function get_request_info()
{
  if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
    if (count($_GET) > 1) {
      return $_GET;
    } elseif (count($_POST) > 1) {
      return $_POST;
    } else {
      $query_str = file_get_contents("php://input"); 
      $array = array(); 
      parse_str($query_str, $array); 
      return $array;
    }
  } else {
    if (count($_GET) == 1) {
      return json_decode($_GET[0], true);
    } elseif (count($_POST) == 1) {
      return json_decode($_POST[0], true);
    } else {
      $date = file_get_contents("php://input");
      return json_decode($date, true);
    }
  }
}

?>
