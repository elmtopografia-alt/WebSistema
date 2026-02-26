<?php
$dir = __DIR__ . '/modelos_gerados/';
$files = glob($dir . 'Modelo*.php');

foreach ($files as $file) {
    echo "Processando: $file\n";
    $content = file_get_contents($file);
    
    // 1. Corrigir propriedades para protected
    $content = str_replace('private $blocos;', 'protected $blocos;', $content);
    $content = str_replace('private $variaveisDetectadas;', 'protected $variaveisDetectadas;', $content);
    $content = str_replace('private $cssCustom;', 'protected $cssCustom;', $content);
    
    // 2. Corrigir getConfig duplicado ou ausente
    // Vamos reconstruir o método getConfig para ser idêntico em todos
    $pattern = '/public function getConfig\(\)(?:: array)?\s*\{.*?\}/s';
    $replacement = 'public function getConfig(): array {
        return [
            \'nome\' => self::NOME,
            \'blocos\' => $this->blocos,
            \'variaveis\' => $this->variaveisDetectadas,
            \'css\' => $this->cssCustom
        ];
    }';
    
    $content = preg_replace($pattern, $replacement, $content);
    
    file_put_contents($file, $content);
}
echo "Concluído!\n";
