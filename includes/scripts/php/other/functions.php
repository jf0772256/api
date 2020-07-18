<?php

/**
 * Function to read php pages into a variable to encode.
 *
 * @param string $file Path to file to be included
 * @return string String value of the file content
 */
function file_to_var(string $file)
{
  global $user_info;
  ob_start();
  include($file);
  return ob_get_clean();
}

/**
 * Gets the method used for the request, should only be one of four, GET, POST, PUT, and DELETE.
 *
 * @return string
 */
function get_method ()
{
  return filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_ENCODED);
}

/**
 * function that tests if a request is sent via ajax and returns either a true or false. only true request are process false requests return 403 always
 *
 * @return boolean
 */
function isAJAX()
{
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && \strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    return true;
  } else {
    return false;
  }
}

/**
 * Parses out the request values and returns them as an array
 *
 * @return array parameters array either from json or urlencoded values
 */
function get_request ()
{
  if ($_SERVER['CONTENT_TYPE'] != 'application/json'){
    if (count($_GET) > 0) {
      return $_GET;
    } elseif (count($_POST) > 0) {
      return $_POST;
    } else {
      $query_str = file_get_contents("php://input");
      $array = [];
      parse_str($query_str, $array);
      return $array;
    }
  } else {
    if (!empty($_GET)){
      reset($_GET);
      $tmp = json_decode(key($_GET), true);
      if (isset($tmp['values'])) {
        $tmp['values'] = do_values_array_things($tmp['values']);
      }
      return $tmp;
    } elseif (!empty($_POST)) {
      $tmp = json_decode(key($_POST), true);
      if (isset($tmp['values'])) {
        $tmp['values'] = do_values_array_things($tmp['values']);
      }
      return $tmp;
    } else {
      $sw = json_decode(file_get_contents("php://input"), true);
      if (isset($sw['values'])) {
        if (!is_array($sw['values'])){
          $sw['values'] = do_values_array_things($sw['values']);
        }
      }
      return $sw;
    }
  }
}

/**
 * Takes stringified array and reverts it to an array again, use ',' as the value delimiter.
 *
 * @param string $instring Stringified array to return into an array.
 * @return array The array of the stringified values.
 */
function do_values_array_things( string $instring)
{
  return explode(',', $instring);
}