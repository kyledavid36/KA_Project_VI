<?php
session_start();
session_destroy();
header("Location: ../GUI_login.html");
exit;
?>
