<?php
require_once __DIR__ . '/includes/funciones.php';

$buscar = trim($_GET['q'] ?? '');
$tipo   = $_GET['tipo'] ?? '';
if (!in_array($tipo, TIPOS, true)) {
    $tipo = '';
}

$sql    = 'SELECT id, codigo, nombre, tipo, ubicacion, estado FROM equipos WHERE 1 = 1';
$params = [];

if ($buscar !== '') {
    $sql .= ' AND (codigo LIKE :q1 OR nombre LIKE :q2 OR ubicacion LIKE :q3)';
    $params[':q1'] = $params[':q2'] = $params[':q3'] = "%$buscar%";
}
if ($tipo !== '') {
    $sql .= ' AND tipo = :tipo';
    $params[':tipo'] = $tipo;
}
$sql .= ' ORDER BY codigo';

$stmt = conectar()->prepare($sql);
$stmt->execute($params);
$equipos = $stmt->fetchAll();

$titulo = 'Listado de equipos';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta">
  <div class="encabezado">
    <h1>Listado de equipos</h1>
    <a href="reservar.php" class="btn btn-primario">+ Nueva reserva</a>
  </div>

  <form method="get" class="filtros">
    <input type="search" name="q" value="<?= e($buscar) ?>"
           placeholder="Buscar por código, nombre o ubicación">
    <select name="tipo">
      <option value="">— Todos los tipos —</option>
      <?php foreach (TIPOS as $t): ?>
        <option value="<?= e($t) ?>" <?= $t === $tipo ? 'selected' : '' ?>><?= e($t) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Filtrar</button>
    <?php if ($buscar !== '' || $tipo !== ''): ?>
      <a href="index.php" class="enlace">Limpiar</a>
    <?php endif; ?>
  </form>

  <div class="tabla-scroll">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Código</th><th>Nombre</th><th>Tipo</th>
          <th>Ubicación</th><th>Estado</th><th>Acción</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$equipos): ?>
        <tr><td colspan="7" class="vacio">No se encontraron equipos.</td></tr>
      <?php endif; ?>

      <?php foreach ($equipos as $i => $eq): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="codigo"><?= e($eq['codigo']) ?></td>
          <td><?= e($eq['nombre']) ?></td>
          <td><?= e($eq['tipo']) ?></td>
          <td><?= e($eq['ubicacion']) ?></td>
          <td>
            <span class="estado <?= $eq['estado'] === 'Disponible' ? 'estado-ok' : 'estado-mant' ?>">
              <?= e($eq['estado']) ?>
            </span>
          </td>
          <td>
            <?php if ($eq['estado'] === 'Disponible'): ?>
              <a href="reservar.php?equipo=<?= (int) $eq['id'] ?>" class="btn btn-chico">Reservar</a>
            <?php else: ?>
              <span class="sin-accion">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
