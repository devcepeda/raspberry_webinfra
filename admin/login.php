<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['admin'] = true;
    header('Location: /raspberry_webinfra/admin/dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="es"><body>
<h2>Admin Login Basico</h2>
<form method="post">
  <input type="text" name="user" placeholder="Usuario">
  <input type="password" name="pass" placeholder="Contrasena">
  <button type="submit">Entrar</button>
</form>
</body></html>
