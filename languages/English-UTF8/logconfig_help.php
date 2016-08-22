<?php
require '../../helplib.php';
echo help_header("Help: Log Settings");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Log Settings</h2>
    <span>Log File Name</span>
    <p>The Log File Name is the file where visitor actions are recorded. You shouldn't have to change this from "genlog.txt".</p>

    <span>Max Log Lines</span>
    <p>Max Log Lines indicates how many actions should be
      retained at any one time. If this number gets too high, you may experience a performance hit.</p>

    <span>Exclude Host Names</span>
    <p>Before making any log entry, TNG will check this list. If the host of the visitor responsible for the potential log entry
      is on the list, no log entry will be made. Host names should be separated by commas (no spaces) and can consist of entire
      host names, IP addresses, or portions of either. For example, "googlebot" will block "crawler4.googlebot.com".</p>

    <span>Exclude User Names</span>
    <p>Before making any log entry, TNG will check this list as well. If the logged-in user
      is on the list, no log entry will be made. User names should be separated by commas (no spaces).</p>

    <span>Log File Name (Admin)</span>
    <p>The log file where actions in the Admin area are recorded. You shouldn't have to change this from "genlog.txt".</p>

    <span>Max Log Lines (Admin)</span>
    <p>Indicates how many actions should be retained at any one time in the Admin log file. If this number gets too high, you may experience a performance hit.</p>

    <span>Block Suggestions or New User Registrations</span>

    <span>Address contains</span>
    <p>Block any incoming suggestion or new user registration where the e-mail address of the sender contains any of the entered words or word segments.
      Separate multiple words with commas.</p>

    <span>Message contains</span>
    <p>Block any incoming suggestion or new user registration where the message body contains any of the entered words or word segments.
      Separate multiple words with commas.</p>
</section> <!-- .container -->
</body>
</html>
