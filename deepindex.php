<?php
require 'begin.php';
require 'genlib.php';
require 'adminlib.php';

$adminLogin = 1;
if ($link) {
  include 'checklogin.php';
}
?>
<frameset>
  <frame src="<?php echo $page; ?>">
</frameset>