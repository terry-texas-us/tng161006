<?php
require 'begin.php';
include("genlib.php");
require 'adminlib.php';

$adminLogin = 1;
if ($link) {
  require 'checklogin.php';
}
?>
<frameset>
  <frame src="<?php echo $page; ?>">
</frameset>