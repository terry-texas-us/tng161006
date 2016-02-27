<?php
   if( isset( $_POST['filter'] ) ) {
      session_start();
      if( $_POST['filter'] != 0 ) {
      $_SESSION['filter'] = $_POST['filter'];
      $_SESSION['fbox'] = 1;
      }
      else {
         unset( $_SESSION['filter'] );
         unset( $_SESSION['fbox'] );
      }
   }