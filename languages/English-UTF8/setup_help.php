<?php
require '../../helplib.php';
echo help_header("Help: Setup");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Setup</h2>
    <h4>Configuration</h4>
    <p>This page contains access points to various categories of TNG settings. Edit the settings in each category to reflect your web site's file layout, your
      MySQL database,	and other configurable options. Change other settings to affect the display of your various pages.</p>

    <h4>Diagnostics</h4>

    <span>Run Diagnostics</span>
    <p>This page shows information about your web server setup, including warnings about settings that may interfere with TNG's performance.</p>

    <span>PHP Info Screen</span>
    <p>This page shows information about your PHP installation. The display of this information is a function of PHP, not TNG. The page is divided into blocks
      that describe separate areas of the configuration. If you are not able to connect to the MySQL database, check this page and look for a "mysql" block. If
      you do not see it, that means that PHP is not yet communicating with MySQL. That indicates a problem with the PHP setup, not with TNG.</p>

    <h4>Table Creation</h4>

    <span>Create Tables</span>
    <p>Click on this button <strong>ONLY</strong> when setting up your site for the first time, as this will create the database tables needed to
      hold your data. <strong>Note: If the tables already exist, any and all previous data will be lost!</strong> You may want to perform this operation anyway
      if your data has been corrupted and you can be restored from backups after recreating the tables.</p>

    <span>Collation</span>
    <p>If you're using UTF-8 as your character set, you might need to enter utf8_unicode_ci, utf8_general_ci or similar in this field prior to creating the tables.
      Otherwise, just leave this field blank to accept the default collation.</p>
</section> <!-- .container -->
</body>
</html>
