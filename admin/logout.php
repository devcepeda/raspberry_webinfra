<?php
session_start();
session_destroy();
header('Location: /raspberry_webinfra/admin/login.php');
exit;
