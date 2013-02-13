<?php
// Phrame error handler
//
// This generates a page that displays an error message with error message, code sniplet and
// Stacktrace.
//
// This page can be activated or deactivated using $conf_env['error_page'] inside
// config/enviroment.php
// It should NEVER be activated in production mode, since it reveals a lot of your projects
// internals or secrets.
//
// This should get more sophisticated.
// ToDo-List:
// - Display full functions as code sniplet
// - Expand stacktrace to code sniplet

global $error;

$parts = explode(':', $error->getMessage(), 2);
if (count($parts) == 2) {
  $title = $parts[0].' Exception occured!';
  $body = $parts[1];
} else {
  $title = 'Uncaught exception!';
  $body = $parts[0];
}

$code_line = $error->getLine();
$code_begin = $code_line - 3;
$code_end = $code_line + 3;

$file = explode("\n", file_get_contents($error->getFile()));
if ($code_begin < 1) $code_begin = 1;
if ($code_end > count($file)) $code_end = count($file);
?>

<html>
  <head>
    <title>Phrame Error</title>
    <style type="text/css">
      code {
        display: block;
        background: #DDD;
      }
      
      .active_line {
        margin: 0; padding: 0;
        background: #AAA;
      }
    </style>
  </head>
  <body>
    <h1><?php echo $title ?></h1>
    <p class="error_message">
      <?php
      echo str_replace("\n", "<br />\n", $body)
      ?>
    </p>
    <p>
      Error occured inside <?php echo $error->getFile() ?>
      at line <?php echo $code_line ?>:<br /><br />
      <code>
        <?php
        for ($i = $code_begin; $i <= $code_end; $i++) {
          if ($i == $code_line) echo '<span class="active_line">';
          echo $file[$i-1];
          if ($i == $code_line) echo '</span>';
          echo "<br />\n";
        }
        ?>
      </code>
    </p>
    <p>
      Stacktrace:
      <code>
        <?php
        echo str_replace("\n", "<br />\n", $error->getTraceAsString())
        ?>
      </code>
    </p>
  </body>
</html>
<?php
die();
?>
