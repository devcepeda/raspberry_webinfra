<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: /raspberry_webinfra/admin/login.php');
    exit;
}
?>
<!doctype html>
<html lang="es"><body>
<h2>Dashboard Basico</h2>
<p>Panel en construccion.</p>
<a href="/raspberry_webinfra/admin/logout.php">Salir</a>
</body></html>
