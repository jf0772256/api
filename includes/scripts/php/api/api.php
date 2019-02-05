<?php
  require_once('./../other/functions.php');
  require_once('./../other/autoloader.php');

  $aj = isAjax();
  $method = get_method();
  $request = get_request_info();

  if ($aj) {
    # code...
  } else {
    echo "<html><head><title>403: Unauthorized</title></head><body><style>.center { text-align:center;}</style> <h1 class='center'>403:<small> Unauthorized</small></h1></body></html>";
  }


?>
