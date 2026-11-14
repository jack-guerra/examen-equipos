<?php $pagina = basename($_SERVER['SCRIPT_NAME']); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo ?? 'Reservas de Equipos') ?></title>
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<nav class="barra">
  <a href="index.php" class="marca">Reservas de Equipos</a>
  <div class="menu">
    <a href="index.php"    class="<?= $pagina === 'index.php' ? 'activo' : '' ?>">Equipos</a>
    <a href="reservas.php" class="<?= $pagina === 'reservas.php' ? 'activo' : '' ?>">Reservas</a>
  </div>
</nav>

<main class="contenedor">
<?php if ($msg = leerFlash()): ?>
  <div class="aviso aviso-ok"><?= e($msg) ?></div>
<?php endif; ?>
