<?php
require_once __DIR__ . '/includes/funciones.php';

$reservas = conectar()->query(
    'SELECT r.id, r.nombre, r.carne, r.fecha, r.hora_inicio, r.hora_fin, r.motivo,
            e.codigo, e.nombre AS equipo
       FROM reservas r
       JOIN equipos e ON e.id = r.equipo_id
      ORDER BY r.fecha DESC, r.hora_inicio'
)->fetchAll();

$titulo = 'Reservas';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta">
  <div class="encabezado">
    <h1>Reservas registradas</h1>
    <a href="reservar.php" class="btn btn-primario">+ Nueva reserva</a>
  </div>

  <div class="tabla-scroll">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Equipo</th><th>Solicitante</th><th>Carné</th>
          <th>Fecha</th><th>Horario</th><th>Motivo</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$reservas): ?>
        <tr><td colspan="7" class="vacio">Aún no hay reservas.</td></tr>
      <?php endif; ?>

      <?php foreach ($reservas as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><span class="codigo"><?= e($r['codigo']) ?></span> <?= e($r['equipo']) ?></td>
          <td><?= e($r['nombre']) ?></td>
          <td><?= e($r['carne']) ?></td>
          <td><?= e(date('d/m/Y', strtotime($r['fecha']))) ?></td>
          <td><?= e(substr($r['hora_inicio'], 0, 5) . ' – ' . substr($r['hora_fin'], 0, 5)) ?></td>
          <td><?= e($r['motivo'] ?? '—') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
