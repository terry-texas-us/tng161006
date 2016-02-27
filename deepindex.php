<?php
include("begin.php");
include("genlib.php");
include("adminlib.php");

$admin_login = 1;
if ($link) {
  include("checklogin.php");
}
?>
<frameset>
  <frame src="<?php echo $page; ?>">
</frameset>