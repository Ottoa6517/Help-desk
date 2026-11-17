<?php
/**
 * osticket-lab.php - Configura y carga el help desk de Daily Brew en osTicket 1.18.x
 *
 * Usa las clases internas de osTicket (las mismas que usa el panel de
 * administración), así que aplica las mismas validaciones que la interfaz web.
 * Todas las fases son idempotentes: si algo ya existe, lo deja como está.
 *
 * Uso (desde la carpeta del repositorio):
 *   php scripts/osticket-lab.php <fase> --osticket=C:\xampp\htdocs\osticket --admin=otto.admin [opciones]
 *
 * Fases:
 *   configurar   Ajustes del sistema, departamento, agente N2, equipos, SLA, temas, respuestas, categorías KB
 *   usuarios     Crea los 8 usuarios ficticios
 *   tickets      Abre los 20 tickets
 *   resolver     Asigna, acusa recibo, documenta, escala y cierra los 20 tickets
 *   kb           Publica los 10 artículos de base de conocimiento
 *   cronologia   Reparte fechas de apertura y cierre en dos semanas (datos simulados)
 *   metricas     Calcula métricas desde la base de datos y escribe metricas.md
 *   todo         Todas las fases en orden
 *
 * Opciones:
 *   --solo=1,2,3   Limita tickets/resolver a esos números de ticket
 *   --seco         Muestra lo que haría sin escribir nada (solo fases configurar/usuarios/tickets)
 *
 * Proyecto de portafolio. Todos los datos son ficticios.
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("Solo por línea de comandos.\n"); }

// ---------------------------------------------------------------- argumentos
$fase = $argv[1] ?? '';
$opts = [];
foreach (array_slice($argv, 2) as $a) {   // --clave=valor o --bandera
    if (preg_match('/^--([a-z]+)(?:=(.*))?$/', $a, $m)) $opts[$m[1]] = $m[2] ?? true;
}
$FASES = ['configurar', 'usuarios', 'tickets', 'resolver', 'kb', 'cronologia', 'metricas', 'todo'];
if (!in_array($fase, $FASES, true) || empty($opts['osticket']) || empty($opts['admin'])) {
    fwrite(STDERR, "Uso: php osticket-lab.php <" . implode('|', $FASES) . "> --osticket=RUTA --admin=USUARIO [--solo=1,2] [--seco]\n");
    exit(2);
}
$ROOT  = rtrim(realpath($opts['osticket']) ?: $opts['osticket'], '/\\');
$REPO  = realpath(__DIR__ . '/..');
$DATOS = __DIR__ . '/datos';
$SECO  = isset($opts['seco']);
$SOLO  = isset($opts['solo']) ? array_map('intval', explode(',', $opts['solo'])) : null;
$INICIO = $opts['inicio'] ?? '2026-11-02';   // lunes de la primera semana simulada

if (!is_file("$ROOT/main.inc.php")) { fwrite(STDERR, "No encuentro osTicket en $ROOT\n"); exit(2); }
if (is_dir("$ROOT/setup")) {
    fwrite(STDERR, "AVISO: la carpeta setup/ sigue existiendo. Bórrala (paso 1.6) antes de seguir.\n");
}

// ---------------------------------------------------------------- arranque de osTicket
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
chdir($ROOT);
require "$ROOT/main.inc.php";
global $thisstaff, $cfg, $ost;

$thisstaff = Staff::lookup(['username' => $opts['admin']]);
if (!$thisstaff) { fwrite(STDERR, "No existe el agente '{$opts['admin']}'.\n"); exit(2); }

// ---------------------------------------------------------------- utilidades
function leer_json($archivo) {
    $d = json_decode(file_get_contents($archivo), true);
    if ($d === null) throw new RuntimeException("JSON inválido: $archivo");
    return $d;
}
function ok($m)    { echo "  [OK]  $m\n"; }
function ya($m)    { echo "  [--]  $m (ya existía)\n"; }
function fallo($m, $e = []) {
    echo "  [ERR] $m" . ($e ? ': ' . json_encode($e, JSON_UNESCAPED_UNICODE) : '') . "\n";
}
function titulo($m) { echo "\n== $m ==\n"; }
function html($texto) {   // texto plano -> HTML seguro con saltos de línea
    return nl2br(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'), false);
}
function prioridad_id($tag) {
    $p = Priority::objects()->filter(['priority' => $tag])->first();
    return $p ? $p->getId() : 2;
}
function sla_id($nombre)  { return SLA::getIdByName($nombre); }
function tema_id($nombre) { return Topic::getIdByName($nombre); }
function dept_id($nombre) { return Dept::getIdByName($nombre); }
function equipo($nombre)  { return Team::lookup(['name' => $nombre]); }
function agente_por_clave($clave, $conf) {
    global $thisstaff;
    if ($clave === 'admin') return $thisstaff;
    foreach ($conf['agentes'] as $a)
        if ($a['clave'] === $clave) return Staff::lookup(['username' => $a['usuario']]);
    return null;
}
function clave_temporal() {   // se muestra una sola vez, nunca se guarda en el repo
    $a = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $s = '';
    for ($i = 0; $i < 20; $i++) $s .= $a[random_int(0, strlen($a) - 1)];
    return $s;
}
function canned($titulo) { return Canned::lookup(Canned::getIdByTitle($titulo)); }
function seleccionado($n) { global $SOLO; return $SOLO === null || in_array($n, $SOLO, true); }
function ticket_por_asunto($asunto) {
    return Ticket::objects()->filter(['cdata__subject' => $asunto])->first();
}

$conf     = leer_json("$DATOS/configuracion.json");
$usuarios = leer_json("$DATOS/usuarios.json");
$tickets  = leer_json("$DATOS/tickets.json");
$resp     = leer_json("$DATOS/respuestas.json");
$U = [];
foreach ($usuarios as $u) $U[$u['clave']] = $u;

// ================================================================ FASE: configurar
function fase_configurar() {
    global $conf, $resp, $cfg, $thisstaff, $SECO;

    titulo('1. Ajustes del sistema');
    $ajustes = [
        'default_timezone'  => $conf['helpdesk']['zona_horaria'],
        'date_format'       => 'dd/MM/y',
        'datetime_format'   => 'dd/MM/y HH:mm',
        'daydatetime_format'=> 'EEE, dd/MM/y HH:mm',
        'time_format'       => 'HH:mm',
        'date_formats'      => 'custom',   // sin esto osTicket ignora los formatos de arriba
        // Sin servidor de correo: se apagan alertas y respuestas automáticas (paso 2.1)
        'ticket_autoresponder' => 0, 'message_autoresponder' => 0, 'ticket_notice_active' => 0,
        'ticket_alert_active' => 0, 'message_alert_active' => 0, 'note_alert_active' => 0,
        'assigned_alert_active' => 0, 'transfer_alert_active' => 0, 'overdue_alert_active' => 0,
        // La guía original no lo menciona: sin esto el portal no muestra la base de conocimiento
        'enable_kb' => 1,
        // Seguridad: nadie se registra solo (mitigación recomendada para CVE-2026-22200)
        'client_registration' => 'closed',
        'enable_captcha' => extension_loaded('gd') ? 1 : 0,
        // Los dominios .local no tienen registros DNS: sin esto osTicket rechaza
        // el correo de los agentes ficticios con "Valid email is required"
        'verify_email_addrs' => 0,
        // osTicket marca tickets vencidos solo cuando corre su cron. En el laboratorio no hay
        // Programador de tareas, así que se usa la actividad de los agentes como disparador
        'enable_auto_cron' => 1,
    ];
    foreach ($ajustes as $k => $v) {
        if ($SECO) { ok("(seco) $k = $v"); continue; }
        $cfg->update($k, $v) ? ok("$k = $v") : fallo("No se pudo guardar $k");
    }
    if (!$SECO) $cfg->load();   // recargar: las claves nuevas no quedan en caché hasta recargar

    titulo('2. Planes de SLA');
    foreach ($conf['sla'] as $s) {
        if (sla_id($s['nombre'])) { ya($s['nombre']); continue; }
        if ($SECO) { ok("(seco) $s[nombre]"); continue; }
        $sla = SLA::create(); $e = [];
        $sla->update(['name' => $s['nombre'], 'grace_period' => $s['horas'], 'isactive' => 1,
                      'notes' => $s['nota'], 'schedule_id' => 0], $e)
            ? ok("$s[nombre] ($s[horas] h)") : fallo($s['nombre'], $e);
    }

    titulo('3. Departamento');
    $d = $conf['departamento'];
    if (!($did = dept_id($d['nombre'])) && !$SECO) {
        $dept = Dept::create(); $e = [];
        $ok = $dept->update([
            'name' => $d['nombre'], 'ispublic' => 1, 'status' => 'active',
            'sla_id' => sla_id('Normal - 24h'), 'manager_id' => $thisstaff->getId(),
            'email_id' => $cfg->getDefaultEmailId(), 'signature' => $d['firma'],
            'assignment_flag' => 'none', 'group_membership' => 1,
            'ticket_auto_response' => 0, 'message_auto_response' => 0,
        ], $e);
        $ok ? ok($d['nombre'] . ' (gerente: ' . $thisstaff->getUserName() . ')') : fallo($d['nombre'], $e);
        $did = dept_id($d['nombre']);
    } else ya($d['nombre']);
    if ($did && !$SECO) {
        if (($em = Email::lookup($cfg->getDefaultEmailId())) && $em->name !== $d['nombre']) {
            $em->name = $d['nombre']; $em->save();   // remitente visible: "Soporte de TI"
        }
        $cfg->update('default_dept_id', $did);
        $cfg->update('default_sla_id', sla_id('Normal - 24h'));
        // El administrador también queda con su departamento principal en Soporte de TI
        $thisstaff->dept_id = $did; $thisstaff->save();
        ok('Departamento y SLA por defecto actualizados');
        // El SLA de ejemplo del instalador (18 h) ya no se usa
        if (($demo = SLA::lookup(SLA::getIdByName('Default SLA'))) && $demo->getId() != $cfg->getDefaultSLAId()) {
            $demo->delete(); ok('SLA de ejemplo borrado: Default SLA');
        }
    }

    titulo('4. Agentes');
    foreach ($conf['agentes'] as $a) {
        if (Staff::getIdByUsername($a['usuario'])) { ya($a['usuario']); continue; }
        if ($SECO) { ok("(seco) $a[usuario]"); continue; }
        $role = Role::objects()->filter(['name' => $a['rol']])->first();
        $clave = clave_temporal();
        $st = Staff::create(); $e = [];
        $ok = $st->update([
            'firstname' => $a['nombre'], 'lastname' => $a['apellido'], 'username' => $a['usuario'],
            'email' => $a['correo'], 'dept_id' => $did, 'role_id' => $role ? $role->getId() : 2,
            'isadmin' => '0', 'isvisible' => 1, 'passwd1' => $clave, 'change_passwd' => 1,
            'backend' => '', 'notes' => $a['nota'], 'perms' => [],
        ], $e);
        if ($ok) {
            ok("$a[usuario] creado. Contraseña temporal (anótala, no se vuelve a mostrar): $clave");
        } else fallo($a['usuario'], $e);
    }

    titulo('5. Equipos (Nivel 1 y Nivel 2)');
    foreach ($conf['equipos'] as $t) {
        if ($SECO) { ok("(seco) $t[nombre]"); continue; }
        $lider = agente_por_clave($t['lider'], $conf);
        $miembros = [];
        foreach ($t['miembros'] as $m) if ($s = agente_por_clave($m, $conf)) $miembros[] = $s->getId();
        $team = equipo($t['nombre']);
        $nuevo = !$team;
        if ($nuevo) $team = Team::create();
        elseif (count($team->getMembers()) === count($miembros)) { ya($t['nombre']); continue; }
        $e = [];
        $vars = ['name' => $t['nombre'], 'isenabled' => 1, 'lead_id' => $lider ? $lider->getId() : 0,
                 'members' => $miembros];
        if (!$nuevo) $vars['id'] = $team->getId();
        $team->update($vars, $e)
            ? ok("$t[nombre] (" . count($miembros) . " miembro/s)") : fallo($t['nombre'], $e);
    }

    // El equipo de ejemplo que crea el instalador no se usa
    if (!$SECO && ($demo = equipo('Level I Support')) && !count($demo->getMembers())) { $demo->delete(); ok('Equipo de ejemplo borrado: Level I Support'); }

    titulo('6. Temas de ayuda');
    foreach ($conf['temas'] as $t) {
        if (tema_id($t['nombre'])) { ya($t['nombre']); continue; }
        if ($SECO) { ok("(seco) $t[nombre]"); continue; }
        $topic = Topic::create(); $e = [];
        $topic->update([
            'topic' => $t['nombre'], 'topic_pid' => 0, 'dept_id' => $did, 'sla_id' => sla_id($t['sla']),
            'priority_id' => prioridad_id($t['prioridad']), 'ispublic' => 1, 'status' => 'active',
            'noautoresp' => 1, 'assign' => 0, 'notes' => '',
        ], $e) ? ok("$t[nombre] -> $t[sla]") : fallo($t['nombre'], $e);
    }
    // Desactivar los temas de ejemplo que trae osTicket para que el portal quede limpio
    $nuevos = array_column($conf['temas'], 'nombre');
    $todos = count(array_filter($nuevos, 'tema_id')) === count($nuevos);
    foreach (Topic::objects() as $topic) {
        if (!$todos || in_array($topic->getName(), $nuevos, true) || $SECO) continue;
        if ($topic->isActive()) {
            $topic->flags &= ~Topic::FLAG_ACTIVE; $topic->save();
            ok('Tema de ejemplo desactivado: ' . $topic->getName());
        }
    }
    if (!$SECO && ($tid = tema_id('Hardware'))) $cfg->update('default_help_topic', $tid);

    titulo('7. Respuestas predefinidas');
    foreach (Canned::objects() as $c) {   // las dos respuestas de ejemplo de osTicket
        if (stripos($c->getTitle(), 'sample') !== false && !$SECO) { $t = $c->getTitle(); $c->delete(); ok("Ejemplo borrado: $t"); }
    }
    foreach ($resp as $r) {
        if (Canned::getIdByTitle($r['titulo'])) { ya($r['titulo']); continue; }
        if ($SECO) { ok("(seco) $r[titulo]"); continue; }
        $c = Canned::create(); $e = [];
        $c->update(['title' => $r['titulo'], 'response' => html($r['texto']), 'isenabled' => 1,
                    'dept_id' => 0, 'notes' => $r['uso']], $e) ? ok($r['titulo']) : fallo($r['titulo'], $e);
    }

    titulo('8. Portal de clientes en español');
    $paginas = [
        'landing' => ['Bienvenido a Soporte de TI de Daily Brew',
            '<h2>¿En qué te ayudamos?</h2><p>Antes de abrir un ticket, revisa la <strong>base de conocimiento</strong>: '
          . 'ahí están las soluciones a los problemas más comunes (contraseñas, impresora, Wi-Fi, correo).</p>'
          . '<p>Si no encuentras la respuesta, usa <strong>Open a New Ticket</strong> y describe qué pasa, desde cuándo y '
          . 'si afecta a más personas. <strong>Si toda la tienda está sin internet o sin cobrar, llama a la extensión 100.</strong></p>'],
        'thank-you' => ['Recibimos tu solicitud',
            '<p>Hola %{ticket.name.first}: recibimos tu solicitud <strong>#%{ticket.number}</strong>. '
          . 'Un agente de Soporte de TI la revisará en el tiempo indicado para su tipo. Guarda este número para darle seguimiento.</p>'],
    ];
    foreach ($paginas as $tipo => [$nombre, $cuerpo]) {
        if ($SECO) { ok("(seco) página $tipo"); continue; }
        $pg = Page::objects()->filter(['type' => $tipo])->first();
        if (!$pg) { fallo("No existe la página $tipo"); continue; }
        if ($pg->name === $nombre) { ya("Página $tipo"); continue; }
        $pg->name = $nombre; $pg->body = $cuerpo; $pg->updated = SqlFunction::NOW();
        $pg->save() ? ok("Página '$tipo' traducida") : fallo("Página $tipo");
    }

    titulo('9. Categorías de la base de conocimiento (públicas)');
    foreach ($conf['categorias_kb'] as $k) {
        if (Category::objects()->filter(['name' => $k['nombre']])->exists()) { ya($k['nombre']); continue; }
        if ($SECO) { ok("(seco) $k[nombre]"); continue; }
        $cat = Category::create(); $e = [];
        $cat->update(['name' => $k['nombre'], 'description' => $k['descripcion'],
                      'ispublic' => Category::VISIBILITY_PUBLIC, 'notes' => '', 'pid' => 0], $e)
            ? ok($k['nombre']) : fallo($k['nombre'], $e);
    }
}

// ================================================================ FASE: usuarios
function fase_usuarios() {
    global $usuarios, $SECO;
    titulo('Usuarios ficticios (@dailybrew.local)');
    foreach ($usuarios as $u) {
        if (User::lookupByEmail($u['correo'])) { ya($u['nombre']); continue; }
        if ($SECO) { ok("(seco) $u[nombre]"); continue; }
        $user = User::fromVars(['name' => $u['nombre'], 'email' => $u['correo'],
                                'notes' => "$u[puesto] | cuenta de dominio: DAILYBREW\\$u[ad] (OU $u[ou])"]);
        $user ? ok("$u[nombre] <$u[correo]> - $u[puesto]") : fallo($u['nombre']);
    }
}

// ================================================================ FASE: tickets
function fase_tickets() {
    global $tickets, $U, $SECO, $conf, $thisstaff;
    titulo('Apertura de los 20 tickets');
    $did = dept_id($conf['departamento']['nombre']);
    foreach ($tickets as $t) {
        if (!seleccionado($t['n'])) continue;
        if (ticket_por_asunto($t['asunto'])) { ya("#$t[n] $t[asunto]"); continue; }
        $u = $U[$t['usuario']];
        $user = User::lookupByEmail($u['correo']);
        if (!$user) { fallo("#$t[n]: falta el usuario $u[correo]; corre la fase usuarios"); continue; }
        if ($SECO) { ok("(seco) #$t[n] $t[asunto] [$t[canal]]"); continue; }

        $vars = [
            'uid' => $user->getId(), 'name' => $user->getName(), 'email' => $u['correo'],
            'topicId' => tema_id($t['tema']), 'deptId' => $did, 'slaId' => sla_id($t['sla']),
            'priorityId' => prioridad_id($t['prioridad']), 'priority' => prioridad_id($t['prioridad']),
            'subject' => $t['asunto'], 'message' => html($t['mensaje']), 'source' => $t['canal'],
            'ip' => '127.0.0.1', 'autorespond' => false, 'alert' => false, 'reply-to' => 'none',
        ];
        $e = [];
        if ($t['canal'] === 'Web') {
            // Como lo haría el empleado desde el portal de clientes
            $ticket = Ticket::create($vars, $e, 'web', false, false);
        } else {
            // Llamada, correo o visita en persona: lo registra el agente
            $ticket = Ticket::open($vars, $e);
        }
        if ($ticket) {
            // El tema pone un SLA por defecto; aquí se aplica la matriz de prioridad
            $ticket->selectSLAId(sla_id($t['sla']));
            $ticket->save();
            ok("#$t[n] -> ticket " . $ticket->getNumber() . " | $t[asunto] | $t[prioridad] | $t[canal]");
        } else fallo("#$t[n] $t[asunto]", $e);
    }
}

// ================================================================ FASE: resolver
function nota_interna($t) {
    $n = $t['nota'];
    $esc = $t['escalar'] ? 'Sí, a ' . $t['escalar']['equipo'] : 'No';
    return "SÍNTOMA: $n[sintoma]\nDIAGNÓSTICO: $n[diagnostico]\nCAUSA RAÍZ: $n[causa]\n"
         . "SOLUCIÓN: $n[solucion]\nPREVENCIÓN: $n[prevencion]\nTIEMPO: $n[tiempo_min] min | ESCALADO: $esc";
}

function fase_resolver() {
    global $tickets, $thisstaff, $conf;
    titulo('Flujo de los 20 tickets: asignar > acusar recibo > diagnosticar > escalar > responder > resolver');
    $acuse  = canned('01 - Acuse de recibo');
    $escal  = canned('05 - Escalado a Nivel 2');
    $cierre = canned('06 - Cierre del ticket');
    $resuelto = TicketStatus::lookup(['state' => 'closed', 'name' => 'Resolved']);
    $n2 = equipo('Nivel 2');
    $agenteN2 = agente_por_clave('n2', $conf);

    foreach ($tickets as $t) {
        if (!seleccionado($t['n'])) continue;
        $ticket = ticket_por_asunto($t['asunto']);
        if (!$ticket) { fallo("#$t[n]: no existe; corre la fase tickets"); continue; }
        if ($ticket->isClosed()) { ya("#$t[n] " . $ticket->getNumber() . ' resuelto'); continue; }
        $e = [];

        // 1. Asignar
        if (!$ticket->getStaffId()) $ticket->assignToStaff($thisstaff, false, false);

        // 2. Acusar recibo con la respuesta predefinida (osTicket rellena las variables)
        $ticket->postReply(['response' => $ticket->replaceVars($acuse->getResponse()),
                            'reply-to' => 'none', 'cannedResp' => $acuse->getId()], $e, false, false);

        // 3. Nota interna de diagnóstico (el usuario no la ve)
        $ticket->postNote(['title' => 'Diagnóstico y causa raíz', 'note' => html(nota_interna($t))],
                          $e, $thisstaff, false);

        // 3b. Plantillas adicionales según el caso (pedir datos, reinicio básico, contraseña)
        foreach ($t['plantillas'] ?? [] as $pl)
            if ($c = canned($pl))
                $ticket->postReply(['response' => $ticket->replaceVars($c->getResponse()), 'reply-to' => 'none'], $e, false, false);

        // 4. Escalar al equipo Nivel 2 con el motivo (en osTicket los equipos se ASIGNAN;
        //    "transferir" es solo entre departamentos)
        if ($t['escalar'] && $n2) {
            $ticket->assignToTeam($n2, html('Escalado a Nivel 2: ' . $t['escalar']['motivo']), false);
            $ticket->postReply(['response' => $ticket->replaceVars($escal->getResponse()),
                                'reply-to' => 'none'], $e, false, false);
            if ($agenteN2)
                $ticket->postNote(['title' => 'Trabajo de Nivel 2', 'note' => html('Caso tomado por Nivel 2. ' .
                    'Detalle de la solución en la nota de diagnóstico.')], $e, $agenteN2, false);
        }

        // 5. Explicar al usuario en lenguaje simple + plantilla de cierre
        //    (se quita el saludo de la plantilla para no repetir "Hola ...:")
        $pie = preg_replace('/^\s*Hola[^<]*:(<br\s*\/?>\s*)+/u', '', $ticket->replaceVars($cierre->getResponse()));
        $ticket->postReply(['response' => html($t['respuesta']) . '<br><br>' . $pie,
                            'reply-to' => 'none'], $e, false, false);

        // 6. Resolver
        $ok = $ticket->setStatus($resuelto, '', $e);
        $ok ? ok("#$t[n] " . $ticket->getNumber() . ($t['escalar'] ? ' (escalado a Nivel 2)' : '') . ' -> Resolved')
            : fallo("#$t[n]", $e);
    }
}

// ================================================================ FASE: kb
function md_a_html($md) {
    $out = []; $lista = null;
    $cerrar = function () use (&$out, &$lista) { if ($lista) { $out[] = "</$lista>"; $lista = null; } };
    foreach (preg_split('/\R/', $md) as $linea) {
        $esc = htmlspecialchars($linea, ENT_QUOTES, 'UTF-8');
        $esc = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $esc);
        $esc = preg_replace('/`(.+?)`/', '<code>$1</code>', $esc);
        if (preg_match('/^###\s+(.*)/', $esc, $m))            { $cerrar(); $out[] = "<h3>$m[1]</h3>"; }
        elseif (preg_match('/^\s{3,}\d+\.\s+(.*)/', $esc, $m)) { $out[] = "<br>&nbsp;&nbsp;&bull; $m[1]"; }
        elseif (preg_match('/^\d+\.\s+(.*)/', $esc, $m))     { if ($lista !== 'ol') { $cerrar(); $out[] = '<ol>'; $lista = 'ol'; } $out[] = "<li>$m[1]</li>"; }
        elseif (preg_match('/^\s*-\s+(.*)/', $esc, $m))     { if ($lista !== 'ul') { $cerrar(); $out[] = '<ul>'; $lista = 'ul'; } $out[] = "<li>$m[1]</li>"; }
        elseif (trim($esc) === '')                            { $cerrar(); }
        else                                                  { $cerrar(); $out[] = "<p>$esc</p>"; }
    }
    $cerrar();
    return implode("\n", $out);
}

function fase_kb() {
    global $REPO;
    titulo('Base de conocimiento: 10 artículos publicados');
    foreach (glob("$REPO/base-de-conocimiento/kb*.md") as $archivo) {
        $txt = file_get_contents($archivo);
        if (!preg_match('/^---\R(.*?)\R---\R(.*)$/s', $txt, $m)) { fallo(basename($archivo) . ': sin encabezado'); continue; }
        $meta = [];
        foreach (preg_split('/\R/', $m[1]) as $l) if (strpos($l, ':')) { [$k, $v] = array_map('trim', explode(':', $l, 2)); $meta[$k] = $v; }
        $cat = Category::objects()->filter(['name' => $meta['categoria']])->first();
        if (!$cat) { fallo("Categoría '$meta[categoria]' no existe; corre la fase configurar"); continue; }
        if (FAQ::objects()->filter(['question' => $meta['pregunta']])->exists()) { ya($meta['pregunta']); continue; }
        $pie = "<p><em>Para quién es: " . htmlspecialchars($meta['audiencia']) . " · Última revisión: "
             . htmlspecialchars($meta['revision']) . "</em></p>";
        $faq = FAQ::create(); $e = [];
        $faq->update(['question' => $meta['pregunta'], 'answer' => md_a_html(trim($m[2])) . $pie,
                      'category_id' => $cat->getId(), 'ispublished' => FAQ::VISIBILITY_PUBLIC,
                      'notes' => "Origen: ticket $meta[ticket_origen]", 'topics' => []], $e)
            ? ok("[$meta[categoria]] $meta[pregunta]") : fallo($meta['pregunta'], $e);
    }
}

// ================================================================ FASE: cronologia (simulada)
/**
 * Los tickets se crean todos en segundos, así que el dashboard y los SLA no dirían nada.
 * Esta fase reparte las fechas en dos semanas laborales según datos/tickets.json
 * (crono.dia, crono.hora, primera_respuesta_min, resolucion_min). ES UNA SIMULACIÓN
 * y así se declara en el README y en metricas.md.
 */
function dia_habil($inicio, $n) {
    $d = new DateTime($inicio, new DateTimeZone('America/Guatemala'));
    while ($n > 0) { $d->modify('+1 day'); if ($d->format('N') < 6) $n--; }
    return $d;
}
function fase_cronologia() {
    global $tickets, $INICIO;
    titulo("Cronología simulada desde el $INICIO (dos semanas laborales)");
    $tz = new DateTimeZone('America/Guatemala');
    foreach ($tickets as $t) {
        if (!seleccionado($t['n'])) continue;
        $ticket = ticket_por_asunto($t['asunto']);
        if (!$ticket) { fallo("#$t[n] no existe"); continue; }
        $c = $t['crono'];
        $abre = dia_habil($INICIO, $c['dia']);
        [$h, $mi] = explode(':', $c['hora']);
        $abre->setTime((int) $h, (int) $mi);
        $resp = (clone $abre)->modify("+{$c['primera_respuesta_min']} minutes");
        $cierra = (clone $abre)->modify("+{$c['resolucion_min']} minutes");
        $sla = $ticket->getSLA();
        $vence = (clone $abre)->modify('+' . ($sla ? $sla->getGracePeriod() : 24) . ' hours');
        // FROM_UNIXTIME convierte a la zona horaria de la sesión de la BD, igual que guarda osTicket
        $f = function (DateTime $d) { return 'FROM_UNIXTIME(' . (int) $d->getTimestamp() . ')'; };
        $tid = $ticket->getId(); $thid = $ticket->getThreadId();
        $fuera = $cierra > $vence;

        db_query(sprintf("UPDATE %s SET created=%s, updated=%s, lastupdate=%s, closed=%s, est_duedate=%s, duedate=NULL, isoverdue=%d WHERE ticket_id=%d",
            TICKET_TABLE, $f($abre), $f($cierra), $f($cierra), $f($cierra),
            $f($vence), $fuera ? 1 : 0, $tid));
        db_query(sprintf("UPDATE %s SET created=%s, lastmessage=%s, lastresponse=%s WHERE id=%d",
            THREAD_TABLE, $f($abre), $f($abre), $f($cierra), $thid));
        // Mensaje del usuario = apertura; primera respuesta = acuse; el resto se reparte hasta el cierre
        $entradas = db_query(sprintf("SELECT id, type FROM %s WHERE thread_id=%d ORDER BY id", THREAD_ENTRY_TABLE, $thid));
        $ids = []; while ($r = db_fetch_array($entradas)) $ids[] = $r;
        $total = count($ids);
        foreach ($ids as $i => $r) {
            if ($i === 0)       $cuando = $abre;
            elseif ($i === 1)   $cuando = $resp;
            else                $cuando = (clone $resp)->modify('+' . (int) round(($c['resolucion_min'] - $c['primera_respuesta_min']) * ($i - 1) / max(1, $total - 2)) . ' minutes');
            db_query(sprintf("UPDATE %s SET created=%s, updated=%s WHERE id=%d", THREAD_ENTRY_TABLE,
                $f($cuando), $f($cuando), $r['id']));
            // El acuse de recibo menciona la fecha límite calculada al momento real de la respuesta
            db_query(sprintf("UPDATE %s SET body=REGEXP_REPLACE(body, 'antes de [0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}', %s) WHERE id=%d",
                THREAD_ENTRY_TABLE, db_input('antes de ' . $vence->format('d/m/Y H:i')), $r['id']));
        }
        $eventos = db_query(sprintf("SELECT id, event_id FROM %s WHERE thread_id=%d ORDER BY id", THREAD_EVENT_TABLE, $thid));
        $ev = []; while ($r = db_fetch_array($eventos)) $ev[] = $r;
        $nev = count($ev);
        foreach ($ev as $i => $r) {
            $cuando = $i === 0 ? $abre : ($i === $nev - 1 ? $cierra :
                (clone $abre)->modify('+' . (int) round($c['resolucion_min'] * $i / max(1, $nev - 1)) . ' minutes'));
            db_query(sprintf("UPDATE %s SET timestamp=%s WHERE id=%d", THREAD_EVENT_TABLE, $f($cuando), $r['id']));
        }
        ok(sprintf('#%-2d %s  abre %s  cierra %s  SLA %s', $t['n'], $ticket->getNumber(),
            $abre->format('d/m H:i'), $cierra->format('d/m H:i'), $fuera ? 'VENCIDO' : 'cumplido'));
    }
}

// ================================================================ FASE: metricas
function fase_metricas() {
    global $REPO, $tickets, $conf;
    titulo('Métricas desde la base de datos');
    $porN = []; foreach ($tickets as $t) $porN[$t['asunto']] = $t;
    $filas = [];
    foreach (Ticket::objects()->order_by('created') as $tk) {
        $asunto = $tk->getSubject();
        if (!isset($porN[$asunto])) continue;
        $t = $porN[$asunto];
        $sla = $tk->getSLA();
        // Fechas leídas directo de la BD (el ORM guarda en caché los valores previos a la fase cronologia)
        $row = db_fetch_array(db_query(sprintf('SELECT UNIX_TIMESTAMP(created) c, UNIX_TIMESTAMP(closed) z FROM %s WHERE ticket_id=%d', TICKET_TABLE, $tk->getId())));
        $creado = (int) $row['c']; $cerrado = $row['z'] ? (int) $row['z'] : null;
        $horas = $cerrado ? ($cerrado - $creado) / 3600 : null;
        $limite = $sla ? $sla->getGracePeriod() : null;
        $filas[] = [
            'n' => $t['n'], 'numero' => $tk->getNumber(), 'asunto' => $asunto,
            'tema' => $tk->getTopic() ? $tk->getTopic()->getName() : '-', 'prioridad' => $t['prioridad'],
            'sla' => $sla ? $sla->getName() : '-', 'limite' => $limite, 'horas' => $horas,
            'cumple' => $horas !== null && $limite !== null ? $horas <= $limite : null,
            'escalado' => (bool) $t['escalar'], 'canal' => $t['canal'],
            'estado' => $tk->getStatus()->getName(), 'fcr' => !$t['escalar'] && !in_array('02 - Pedir más información', $t['plantillas'] ?? [], true),
            'tiempo_agente' => $t['nota']['tiempo_min'],
            'primera' => $t['crono']['primera_respuesta_min'],
        ];
    }
    if (!$filas) { fallo('No hay tickets del laboratorio'); return; }
    usort($filas, fn($a, $b) => $a['n'] <=> $b['n']);
    $total = count($filas);
    $res   = count(array_filter($filas, fn($f) => $f['horas'] !== null));
    $cumple = count(array_filter($filas, fn($f) => $f['cumple'] === true));
    $esc   = count(array_filter($filas, fn($f) => $f['escalado']));
    $fcr   = count(array_filter($filas, fn($f) => $f['fcr']));
    $porTema = []; foreach ($filas as $f) $porTema[$f['tema']] = ($porTema[$f['tema']] ?? 0) + 1; arsort($porTema);
    $porCanal = []; foreach ($filas as $f) $porCanal[$f['canal']] = ($porCanal[$f['canal']] ?? 0) + 1; arsort($porCanal);
    $horasRes = array_filter(array_column($filas, 'horas'), fn($h) => $h !== null);
    sort($horasRes);
    $mediana = $horasRes ? $horasRes[intdiv(count($horasRes), 2)] : 0;
    $promResp = array_sum(array_column($filas, 'primera')) / $total;
    $agente = array_sum(array_column($filas, 'tiempo_agente'));
    $pct = fn($a, $b) => $b ? round(100 * $a / $b) . ' %' : '-';
    $fmt = fn($h) => $h === null ? '-' : ($h < 1 ? round($h * 60) . ' min' : number_format($h, 1, ',', '') . ' h');

    $temaTop = array_key_first($porTema);
    $cuentas = $porTema['Cuentas y accesos'] ?? 0;
    $md  = "# Métricas del help desk\n\n";
    $md .= "> **Datos simulados.** Los 20 tickets son ficticios y sus fechas se repartieron en dos semanas laborales con la fase `cronologia` del script, para que el dashboard tenga datos con sentido. Las cifras de esta página se calculan desde la base de datos de osTicket con `php scripts/osticket-lab.php metricas`.\n\n";
    $md .= "## Resumen\n\n| Indicador | Valor | Cómo se calcula |\n|---|---|---|\n";
    $md .= "| Tickets recibidos | $total | |\n";
    $md .= "| Tickets resueltos | $res de $total | Estado Resolved |\n";
    $md .= "| Cumplimiento de SLA | $cumple de $res (" . $pct($cumple, $res) . ") | Resueltos antes del límite de su plan de SLA |\n";
    $md .= "| Escalados a Nivel 2 | $esc (" . $pct($esc, $total) . ") | Asignados al equipo Nivel 2 |\n";
    $md .= "| Resueltos al primer contacto (FCR) | $fcr (" . $pct($fcr, $total) . ") | Sin escalar y sin tener que pedir más datos al usuario |\n";
    $md .= "| Tiempo medio de primera respuesta | " . round($promResp) . " min | Del mensaje del usuario al acuse de recibo |\n";
    $md .= "| Mediana de tiempo de resolución | " . $fmt($mediana) . " | De la apertura al estado Resolved |\n";
    $md .= "| Tiempo de trabajo del agente | " . number_format($agente / 60, 1, ',', '') . " h en total | Suma del campo TIEMPO de las notas internas |\n\n";
    $md .= "## Tickets por tema\n\n| Tema | Tickets | % |\n|---|---|---|\n";
    foreach ($porTema as $k => $v) $md .= "| $k | $v | " . $pct($v, $total) . " |\n";
    $md .= "\n## Tickets por canal\n\n| Canal | Tickets |\n|---|---|\n";
    $nombresCanal = ['Web' => 'Portal web', 'Phone' => 'Teléfono', 'Other' => 'En persona', 'Email' => 'Correo'];
    foreach ($porCanal as $k => $v) $md .= '| ' . ($nombresCanal[$k] ?? $k) . " | $v |\n";
    $md .= "\n## Detalle\n\n| # | Ticket | Asunto | Tema | Prioridad | SLA | Resolución | SLA cumplido | Escalado |\n|---|---|---|---|---|---|---|---|---|\n";
    foreach ($filas as $f)
        $md .= "| $f[n] | $f[numero] | $f[asunto] | $f[tema] | $f[prioridad] | $f[sla] | " . $fmt($f['horas']) . ' | '
             . ($f['cumple'] === null ? '-' : ($f['cumple'] ? 'Sí' : '**No**')) . ' | ' . ($f['escalado'] ? 'Sí' : 'No') . " |\n";
    $vencidos = array_filter($filas, fn($f) => $f['cumple'] === false);
    $md .= "\n## Tres conclusiones\n\n";
    $md .= "1. **$temaTop** fue el tema con más tickets (" . $porTema[$temaTop] . " de $total). ";
    $md .= "**Cuentas y accesos** sumó $cuentas (" . $pct($cuentas, $total) . "): contraseña olvidada, cuenta bloqueada y permisos. "
         . "Propuesta: habilitar el autoservicio de restablecimiento de contraseña (SSPR) y publicar el artículo kb01 en la pantalla de inicio de sesión.\n";
    $md .= "2. Se cumplió el SLA en $cumple de $res tickets (" . $pct($cumple, $res) . "). Los vencidos fueron "
         . implode(' y ', array_map(fn($f) => "el #$f[n] ($f[asunto])", $vencidos))
         . ": uno por un cambio programado fuera de horario sin pausar el SLA y otro por la cola de Nivel 2. "
         . "Propuesta: usar el estado «en espera» o acordar la ventana con el usuario, y que Nivel 2 tenga una alerta al 75 % del SLA.\n";
    $md .= "3. Se escalaron $esc tickets (" . $pct($esc, $total) . "), todos de red o seguridad, que necesitaban permisos o contratos que Nivel 1 no tiene. "
         . "Propuesta: un procedimiento escrito para incidentes de seguridad (aislar, documentar, escalar) para que Nivel 1 contenga el problema en los primeros 10 minutos.\n";
    file_put_contents("$REPO/metricas.md", $md);
    ok("metricas.md escrito: SLA " . $pct($cumple, $res) . ", escalados $esc, FCR " . $pct($fcr, $total));
}

// ---------------------------------------------------------------- ejecución
$orden = $fase === 'todo' ? ['configurar', 'usuarios', 'tickets', 'resolver', 'kb', 'cronologia', 'metricas'] : [$fase];
foreach ($orden as $f) call_user_func("fase_$f");
echo "\nListo.\n";
