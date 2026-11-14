<?php
require_once __DIR__ . '/includes/funciones.php';

$pdo = conectar();

$equipos = $pdo->query(
    "SELECT id, codigo, nombre FROM equipos WHERE estado = 'Disponible' ORDER BY codigo"
)->fetchAll();

$datos = [
    'nombre'      => '',
    'carne'       => '',
    'equipo_id'   => (int) ($_GET['equipo'] ?? 0),
    'fecha'       => '',
    'hora_inicio' => '',
    'hora_fin'    => '',
    'motivo'      => '',
];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($datos as $campo => $_) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
    }
    $datos['equipo_id'] = (int) $datos['equipo_id'];

    if (!csrfValido($_POST['csrf'] ?? null)) {
        $errores[] = 'La sesión expiró, vuelve a enviar el formulario.';
    }
    if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
        $errores[] = 'Ingresa el nombre completo (máx. 100 caracteres).';
    }
    if (!preg_match('/^\d{4}-\d{4}$/', $datos['carne'])) {
        $errores[] = 'Ingresa un carné válido (ej. 2023-0145).';
    }
    if (!in_array($datos['equipo_id'], array_map('intval', array_column($equipos, 'id')), true)) {
        $errores[] = 'Selecciona un equipo disponible.';
    }

    $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha']);
    if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha']) {
        $errores[] = 'Selecciona una fecha válida.';
    } elseif ($datos['fecha'] < date('Y-m-d')) {
        $errores[] = 'La fecha no puede ser anterior a hoy.';
    }

    $horaOk = '/^([01]\d|2[0-3]):[0-5]\d$/';
    if (!preg_match($horaOk, $datos['hora_inicio']) || !preg_match($horaOk, $datos['hora_fin'])) {
        $errores[] = 'Ingresa la hora de inicio y de fin.';
    } elseif ($datos['hora_fin'] <= $datos['hora_inicio']) {
        $errores[] = 'La hora fin debe ser mayor que la hora de inicio.';
    }

    if (mb_strlen($datos['motivo']) > 255) {
        $errores[] = 'El motivo no puede pasar de 255 caracteres.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'SELECT hora_inicio, hora_fin FROM reservas
              WHERE equipo_id = ? AND fecha = ?
                AND hora_inicio < ? AND hora_fin > ?
              LIMIT 1'
        );
        $stmt->execute([
            $datos['equipo_id'], $datos['fecha'],
            $datos['hora_fin'],  $datos['hora_inicio'],
        ]);

        if ($choque = $stmt->fetch()) {
            $errores[] = sprintf(
                'El equipo ya está reservado el %s de %s a %s.',
                $datos['fecha'],
                substr($choque['hora_inicio'], 0, 5),
                substr($choque['hora_fin'], 0, 5)
            );
        }
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'INSERT INTO reservas (equipo_id, nombre, carne, fecha, hora_inicio, hora_fin, motivo)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $datos['equipo_id'], $datos['nombre'], $datos['carne'], $datos['fecha'],
            $datos['hora_inicio'], $datos['hora_fin'],
            $datos['motivo'] !== '' ? $datos['motivo'] : null,
        ]);

        flash('Reserva registrada correctamente.');
        header('Location: reservas.php');
        exit;
    }
}

$titulo = 'Registrar reserva';
require __DIR__ . '/includes/header.php';
?>

<section class="tarjeta tarjeta-form">
  <h1>Registrar reserva</h1>

  <?php if ($errores): ?>
    <div class="aviso aviso-error">
      <strong>Error:</strong>
      <?php foreach ($errores as $err): ?>
        <div><?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

    <label for="nombre">Nombre completo</label>
    <input type="text" id="nombre" name="nombre" maxlength="100" required
           value="<?= e($datos['nombre']) ?>">

    <label for="carne">Carné</label>
    <input type="text" id="carne" name="carne" maxlength="20" required
           pattern="\d{4}-\d{4}" placeholder="2023-0145"
           value="<?= e($datos['carne']) ?>">
    <small>Ingrese un carné válido (ej. 2023-0145)</small>

    <label for="equipo_id">Equipo</label>
    <select id="equipo_id" name="equipo_id" required>
      <option value="">— Selecciona un equipo —</option>
      <?php foreach ($equipos as $eq): ?>
        <option value="<?= (int) $eq['id'] ?>" <?= (int) $eq['id'] === $datos['equipo_id'] ? 'selected' : '' ?>>
          <?= e($eq['codigo'] . ' - ' . $eq['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <div class="fila">
      <div>
        <label for="fecha">Fecha</label>
        <input type="date" id="fecha" name="fecha" required min="<?= date('Y-m-d') ?>"
               value="<?= e($datos['fecha']) ?>">
      </div>
      <div>
        <label for="hora_inicio">Hora inicio</label>
        <input type="time" id="hora_inicio" name="hora_inicio" required
               value="<?= e($datos['hora_inicio']) ?>">
      </div>
      <div>
        <label for="hora_fin">Hora fin</label>
        <input type="time" id="hora_fin" name="hora_fin" required
               value="<?= e($datos['hora_fin']) ?>">
      </div>
    </div>

    <label for="motivo">Motivo (opcional)</label>
    <textarea id="motivo" name="motivo" rows="3" maxlength="255"><?= e($datos['motivo']) ?></textarea>

    <div class="acciones">
      <button type="submit" class="btn btn-primario">Guardar</button>
      <a href="index.php" class="btn">Cancelar</a>
    </div>
  </form>
</section>
