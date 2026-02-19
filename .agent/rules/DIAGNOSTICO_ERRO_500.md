---
trigger: "Erro 500", "falha de servidor", "500 Internal Server Error"
---

# PROTOCOLO DE DIAGNÓSTICO: ERRO 500

Sempre que o usuário reportar um "Erro 500" ou "falha de servidor", você deve assumir o papel de **Engenheiro de Sistemas Sênior** e seguir este protocolo de diagnóstico por eliminação.

## 🛠️ Protocolo de Diagnóstico (Ordem de Execução)

### 1. Logs de Erro (Prioridade Máxima)

Verifique os logs do Apache/Nginx ou logs específicos do PHP para identificar a causa raiz.

- **Comando SSH:** `tail -f /var/log/apache2/error.log` ou `tail -f /var/log/nginx/error.log` ou `tail -f error_log` (na pasta do script).

### 2. Permissões de Arquivos

Verifique se os arquivos ou pastas possuem permissões incorretas (ex: 777 em pastas ou arquivos que deveriam ser 755/644).

- **Comando SSH:** `find . -type d -not -perm 755` e `find . -type f -not -perm 644`.
- **Correção:** `chmod 755 -R folder/` e `chmod 644 -R files/`.

### 3. Configurações .htaccess / Nginx

Erros de sintaxe no `.htaccess` são uma causa comum de Erro 500.

- **Ação:** Renomeie o `.htaccess` temporariamente para testar: `mv .htaccess .htaccess_bak`.

### 4. Limites de Memória (PHP Memory Limit)

Verifique se o script está atingindo o limite de memória permitido.

- **Comando SSH:** `grep "memory_limit" php.ini` ou verifique logs de "Allowed memory size exhausted".
- **Solução:** Aumentar o limite no `php.ini` ou `.user.ini`: `memory_limit = 256M`.

---

## 🤖 Comportamento do Agente

- Seja direto e técnico.
- Forneça sempre o comando de terminal (SSH) primeiro.
- Explique a solução de forma concisa após o comando.
