<?php

// Get list of UI files.
$files = array();
$files['Quest'] = 'ui/quest';

?><!DOCTYPE html>
<html>
  <head>
    <title>Lazy Quest UI/UX Tool</title>
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/jquery-ui.js"></script>
    <script src="js/jquery.easy-confirm-dialog.min.js"></script>
    <script src="js/interface.js"></script>
  </head>
  
  <body>
    
    <ul>
      <?php foreach ($files as $name => $file): ?>
        <li><a href="<?php print $file; ?>"><?php print $name; ?></a></li>
      <?php endforeach; ?>
    </ul>

  </body>
</html>