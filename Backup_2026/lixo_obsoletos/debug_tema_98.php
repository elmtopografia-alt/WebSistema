<?php
// Mock do banco de dados para evitar erros de conexão
class Database {
    public static function getProd() {
        return new mysqli('localhost', 'root', '', 'test'); // Dummy connection
    }
}

// Mock da classe TemaProposta (caso falhe o require)
if (!class_exists('TemaProposta')) {
    class TemaProposta {
        public static function detectar($data) {
            // Lógica replicada para teste
            $timestampProposta = strtotime($data);
            $timestampMudanca = strtotime('2026-02-15 00:00:00');
            return ($timestampProposta < $timestampMudanca) ? 'classica' : 'moderna';
        }
        public static function getClasse($data) {
            return 'tema-' . self::detectar($data);
        }
    }
}

// Simulando a lógica do gerar_documento_html.php
// Vamos assumir que conseguimos a data de criação de alguma forma, ou testar as duas possibilidades.

echo "--- CENÁRIO 1: Proposta Antiga (Data < 15/02/2026) ---\n";
$dataCriacaoAntiga = '2026-02-10 10:00:00';
$classeTemaAntiga = TemaProposta::getClasse($dataCriacaoAntiga);
$cssTemaPath = 'assets/css/tema_proposta.css';

echo "Data Criação: $dataCriacaoAntiga\n";
echo "Classe Tema: $classeTemaAntiga\n";
echo "Link CSS Esperado: <link rel=\"stylesheet\" href=\"$cssTemaPath\">\n";
echo "Seletor CSS Ativo: .proposta-wrapper.$classeTemaAntiga\n";

echo "\n--- CENÁRIO 2: Proposta Nova (Data >= 15/02/2026) ---\n";
$dataCriacaoNova = '2026-02-16 10:00:00';
$classeTemaNova = TemaProposta::getClasse($dataCriacaoNova);

echo "Data Criação: $dataCriacaoNova\n";
echo "Classe Tema: $classeTemaNova\n";
echo "Link CSS Esperado: <link rel=\"stylesheet\" href=\"$cssTemaPath\">\n";
echo "Seletor CSS Ativo: .proposta-wrapper.$classeTemaNova (O CSS azul NÃO funcionará pois requer .tema-classica)\n";

?>
