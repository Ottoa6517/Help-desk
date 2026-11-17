<?php
/**
 * generar-docs.php - Escribe tickets/README.md y tickets/ticket-NN.md a partir de datos/tickets.json.
 * No necesita osTicket. Uso: php scripts/generar-docs.php
 */
$repo = realpath(__DIR__ . '/..');
$t = json_decode(file_get_contents(__DIR__ . '/datos/tickets.json'), true);
$u = [];
foreach (json_decode(file_get_contents(__DIR__ . '/datos/usuarios.json'), true) as $x) $u[$x['clave']] = $x;
$canal = ['Web' => 'Portal web', 'Phone' => 'Teléfono', 'Other' => 'En persona', 'Email' => 'Correo'];
$prio  = ['emergency' => 'Emergency', 'high' => 'High', 'normal' => 'Normal', 'low' => 'Low'];
@mkdir("$repo/tickets");

$md  = "# Los 20 tickets\n\n";
$md .= "Escenarios ficticios de soporte de TI para Daily Brew. Cada ticket tiene su archivo con la nota interna completa "
     . "(síntoma, diagnóstico, causa raíz, solución, prevención y tiempo) y la respuesta al usuario.\n\n";
$md .= "| # | Usuario | Tema | Prioridad | SLA | Canal | Asunto | Escalado | Artículo KB |\n|---|---|---|---|---|---|---|---|---|\n";
foreach ($t as $x) {
    $f = sprintf('ticket-%02d.md', $x['n']);
    $md .= sprintf("| %d | %s | %s | %s | %s | %s | [%s](%s) | %s | %s |\n", $x['n'], $u[$x['usuario']]['nombre'],
        $x['tema'], $prio[$x['prioridad']], $x['sla'], $canal[$x['canal']], $x['asunto'], $f,
        $x['escalar'] ? 'Nivel 2' : '-', $x['kb'] ?: '-');

    $n = $x['nota'];
    $d  = "# Ticket {$x['n']}: {$x['asunto']}\n\n";
    $d .= "| Campo | Valor |\n|---|---|\n";
    $d .= "| Usuario | {$u[$x['usuario']]['nombre']} ({$u[$x['usuario']]['puesto']}), cuenta de dominio `DAILYBREW\\{$u[$x['usuario']]['ad']}` |\n";
    $d .= "| Tema de ayuda | {$x['tema']} |\n| Prioridad | {$prio[$x['prioridad']]} |\n| Plan de SLA | {$x['sla']} |\n";
    $d .= "| Canal | {$canal[$x['canal']]} |\n| Escalado | " . ($x['escalar'] ? 'Sí, a ' . $x['escalar']['equipo'] : 'No') . " |\n\n";
    $d .= "## Lo que escribió el usuario\n\n> {$x['mensaje']}\n\n";
    $d .= "## Nota interna\n\n```\nSÍNTOMA: {$n['sintoma']}\nDIAGNÓSTICO: {$n['diagnostico']}\nCAUSA RAÍZ: {$n['causa']}\n"
        . "SOLUCIÓN: {$n['solucion']}\nPREVENCIÓN: {$n['prevencion']}\nTIEMPO: {$n['tiempo_min']} min | ESCALADO: "
        . ($x['escalar'] ? 'Sí' : 'No') . "\n```\n\n";
    if ($x['escalar']) $d .= "## Por qué se escaló\n\n{$x['escalar']['motivo']}\n\n";
    $d .= "## Respuesta al usuario\n\n> {$x['respuesta']}\n";
    if ($x['kb']) {
        $kb = glob("$repo/base-de-conocimiento/{$x['kb']}-*.md");
        if ($kb) $d .= "\n## Artículo de base de conocimiento\n\n[" . basename($kb[0]) . "](../base-de-conocimiento/" . basename($kb[0]) . ")\n";
    }
    file_put_contents("$repo/tickets/$f", $d);
}
$esc = count(array_filter($t, fn($x) => $x['escalar']));
$md .= "\nEscalados a Nivel 2: $esc de " . count($t) . ". Los tickets 2, 7, 15 y 20 se pueden reproducir de verdad en el dominio "
     . "del [laboratorio de Active Directory](https://github.com/Ottoa6517/lab-active-directory-dailybrew) con "
     . "[`scripts/ad/tickets-en-el-dominio.ps1`](../scripts/ad/tickets-en-el-dominio.ps1).\n";
file_put_contents("$repo/tickets/README.md", $md);
echo "tickets/README.md y " . count($t) . " archivos de ticket escritos.\n";
