<?php
/**
 * restablecer-agente.php - Cambia la contraseña de un agente de osTicket desde la consola.
 * La contraseña llega por la variable de entorno OST_NUEVA (nunca como argumento, para que
 * no quede en el historial). Úsalo a través de scripts/windows/06-restablecer-agente.ps1.
 * Uso: php restablecer-agente.php C:\xampp\htdocs\osticket otto.admin
 */
if (PHP_SAPI !== 'cli') exit(1);
[$_, $root, $usuario] = $argv + [null, null, null];
$nueva = getenv('OST_NUEVA');
if (!$root || !$usuario || !$nueva) { fwrite(STDERR, "Faltan datos.\n"); exit(2); }
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
chdir($root); require "$root/main.inc.php";
$st = Staff::lookup(['username' => $usuario]);
if (!$st) { fwrite(STDERR, "No existe el agente $usuario\n"); exit(3); }
try {
    $st->setPassword($nueva, null);
    $st->save();
    echo "Contraseña de $usuario actualizada.\n";
} catch (Exception $e) { fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n"); exit(4); }
