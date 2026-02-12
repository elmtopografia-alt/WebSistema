<?php
// SGT_ETHICS_BIBLE.php
declare(strict_types=1);

final class SgtEthics {
    const USER_AGENT = 'SGT-Bot/2.0 (comercial@seudominio.com)';
    const ALLOWED = ['public_form', 'wa_link', 'email_publico'];
    
    public static function permitido(string $canal): bool {
        return in_array($canal, self::ALLOWED, true);
    }
    
    public static function validarUrl(string $url) {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        return $url ?: false;
    }
}
