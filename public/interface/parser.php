<?php

// Load up the .ui file.
// Parse from .ui into HTML and spit onto the page.
// Use JS to flip between "steps" (or jump to other steps).

?><!DOCTYPE html>
<html>
  <head>
    <title></title>
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/jquery.easy-confirm-dialog.min.js"></script>
    <script src="js/interface.js"></script>
  </head>
  
  <body>
    
    <?php print var_export($_GET, TRUE); ?>

  </body>
</html>