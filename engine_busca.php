<?php
// engine_busca.php
// Motor de Busca Modular (adaptado para DuckDuckGo HTML Version)

class SearchEngine {
    
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function buscar($termo, $local, $paginas = 1) {
        $resultados = [];
        $query = urlencode("$termo $local");
        
        // Loop por páginas (DuckDuckGo HTML não tem paginação numérica fácil, vamos tentar pegar a primeira página bem feita primeiro)
        // Para paginação no DDG HTML, precisariamos parsear o form de 'next'. Por enquanto, vamos focar na página 1.
        
        $url = "https://html.duckduckgo.com/html/";
        $postData = "q=$query";

        // Simulando delay humano entre requisições se fosse paginado
        // sleep(2);

        $html = $this->fazerRequisicao($url, $postData);
        
        if ($html) {
            $resultados = array_merge($resultados, $this->parsearHTML($html));
        }

        return $resultados;
    }

    private function fazerRequisicao($url, $postData = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita erro de SSL em local
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<div class='alert alert-danger'>Erro cURL: $error</div>";
            return null;
        }
        return $response;
    }

    private function parsearHTML($html) {
        $leads = [];
        $dom = new DOMDocument();
        @$dom->loadHTML($html); // O @ suprime warnings de HTML mal formatado
        $xpath = new DOMXPath($dom);

        // DDG HTML Structure: div.result -> h2.result__title -> a.result__a
        $nodes = $xpath->query("//div[contains(@class, 'result')]");

        foreach ($nodes as $node) {
            $titleNode = $xpath->query(".//a[contains(@class, 'result__a')]", $node)->item(0);
            $snippetNode = $xpath->query(".//a[contains(@class, 'result__snippet')]", $node)->item(0);
            $urlNode = $xpath->query(".//a[contains(@class, 'result__url')]", $node)->item(0); // Às vezes é separado

            if ($titleNode) {
                $link = $titleNode->getAttribute('href');
                $title = $titleNode->nodeValue;
                $snippet = $snippetNode ? $snippetNode->nodeValue : '';
                
                // Limpar link do DDG (as vezes vem como /l/?kh=-1&uddg=...)
                // Mas na versão HTML costuma ser direto ou redirecionado. 
                // Vamos tentar extrair a URL real se for um redirecionador.
                if (strpos($link, 'uddg=') !== false) {
                   parse_str(parse_url($link, PHP_URL_QUERY), $params);
                   if (isset($params['uddg'])) {
                       $link = $params['uddg'];
                   }
                }

                // Filtrar lixo
                if ($this->ehLixo($link)) continue;

                $leads[] = [
                    'nome' => trim($title),
                    'link' => trim($link),
                    'descricao' => trim($snippet),
                    'origem' => 'DuckDuckGo_Robot'
                ];
            }
        }
        return $leads;
    }

    private function ehLixo($url) {
        $blacklist = [
            'duckduckgo.com', 'google.com', 'youtube.com', 'facebook.com', 
            'instagram.com', 'linkedin.com', 'jusbrasil.com.br', 'cnpj.biz',
            'tripadvisor', 'yelp', 'reclameaqui.com.br', 'maplink', 'waze'
        ];
        foreach ($blacklist as $bad) {
            if (stripos($url, $bad) !== false) return true;
        }
        return false;
    }
}
?>
