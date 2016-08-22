<?php
require 'begin.php';
require 'adminlib.php';

if ($link) {
  $adminLogin = 1;
  include 'checklogin.php';
}
echo phpinfo();