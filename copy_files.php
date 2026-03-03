<?php
$navbar = '<nav class="w-full glass-panel sticky top-0 z-50 border-b border-white/10 mb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-4">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgNjAiIHdpZHRoPSIyMDAiIGhlaWdodD0iNjAiPjxkZWZzPjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZEljb24iIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNmOTczMTY7c3RvcC1vcGFjaXR5OjEiIC8+PHN0b3Agb2Zmc2V0PSIxMDAlIiBzdHlsZT0ic3RvcC1jb2xvcjojZWE1ODBjO3N0b3Atb3BhY2l0eToxIiAvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHg9IjUiIHk9IjUiIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgcng9IjEwIiBmaWxsPSJ1cmwoI2dyYWRJY29uKSIvPjxwYXRoIGQ9Ik0yOCAxNSBMMjIgMjggTDMwIDI4IEwyNiA0NSBMMzggMzAgTDMwIDMwIEwzNCAxNSBaIiBmaWxsPSJ3aGl0ZSIvPjx0ZXh0IHg9IjY1IiB5PSIzNSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXdlaWdodD0iYm9sZCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiPlNHVDwvdGV4dD48dGV4dCB4PSI2NSIgeT0iNTAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgZmlsbD0iI2ZiOTIzYyI+UHJvcG9zdGFzPC90ZXh0Pjwvc3ZnPg==" class="h-10">
                <span class="font-bold text-white text-lg" style="margin-left: 10px;">SGT <span class="text-brand-accent">SaaS</span></span>
            </div>
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                    <i class="ph ph-arrow-left"></i> Voltar ao Painel
                </a>
            </div>
        </div>
    </div>
</nav>';

$dest = 'C:\\xampp\\htdocs\\SistemaSaaS\\';

// 1. meus_clientes.php
$c1 = file_get_contents(__DIR__ . '/meus_clientes.php');
$c1 = preg_replace(
    "/require_once 'session_validator\.php';\s*require_once 'config\.php';\s*require_once 'ConnectionManager\.php';\s*require_once 'PropostaRepository\.php';/s",
    "require_once __DIR__ . '/shared/session_validator.php';\nrequire_once __DIR__ . '/core/ConnectionManager.php';\nrequire_once __DIR__ . '/core/PropostaRepository.php';",
    $c1
);
$c1 = str_replace(
    "\$repo = new PropostaRepository();",
    "\$repo = new \\SGT\\PropostaRepository();",
    $c1
);
$c1 = str_replace("<?php include 'components/navbar.php'; ?>", $navbar, $c1);
file_put_contents($dest . 'meus_clientes.php', $c1);

// 2. minha_empresa.php
$c2 = file_get_contents(__DIR__ . '/minha_empresa.php');
$c2 = preg_replace(
    "/require_once 'session_validator\.php';\s*require_once 'config\.php';\s*require_once 'db\.php';/s",
    "require_once __DIR__ . '/shared/session_validator.php';\nrequire_once __DIR__ . '/core/ConnectionManager.php';",
    $c2
);
$c2 = str_replace(
    "\$conn = \$is_demo ? Database::getDemo() : Database::getProd();",
    "\$conn = \\ConnectionManager::get();",
    $c2
);
$c2 = preg_replace("/<head>(.*?)<head>/s", "<head>", $c2); 
$c2 = str_replace("<?php include 'components/navbar.php'; ?>", $navbar, $c2);
file_put_contents($dest . 'minha_empresa.php', $c2);

// 3. admin_parametros.php
$c3 = file_get_contents(__DIR__ . '/admin_parametros.php');
$c3 = preg_replace(
    "/session_start\(\);\s*require_once 'config\.php';\s*require_once 'db\.php';/s",
    "require_once __DIR__ . '/shared/session_validator.php';\nrequire_once __DIR__ . '/core/ConnectionManager.php';",
    $c3
);
$c3 = str_replace(
    "\$conn = Database::getProd();",
    "\$conn = \\ConnectionManager::get();",
    $c3
);
$c3 = preg_replace("/\\\$connDemo = Database::getDemo\(\);/s", "\$connDemo = \\ConnectionManager::get();", $c3);
$c3 = str_replace('href="painel.php"', 'href="index.php"', $c3);
file_put_contents($dest . 'admin_parametros.php', $c3);

echo "OK";
