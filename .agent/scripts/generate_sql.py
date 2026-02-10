
import json

services = [11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21]
drone_service_id = 19

blocks = [
    {"slug": "cabecalho", "title": "Cabeçalho", "category": "layout", "order": 0, "vars": ["Cidade", "DataExtenso", "numero_proposta"]},
    {"slug": "apresentacao", "title": "Apresentação", "category": "presentation", "order": 1, "vars": ["Empresa"]},
    {"slug": "finalidade", "title": "Finalidade", "category": "presentation", "order": 2, "vars": ["finalidade"]},
    {"slug": "escopo", "title": "Escopo dos Serviços", "category": "technical", "order": 3, "vars": ["area_obra"]},
    {"slug": "metodologia", "title": "Metodologia", "category": "technical", "order": 4, "vars": []},
    {"slug": "documentacao", "title": "Documentação Técnica", "category": "technical", "order": 5, "vars": []},
    {"slug": "equipamentos", "title": "Equipamentos Utilizados", "category": "technical", "order": 6, "vars": ["Estacao_Total", "GPS", "Veiculo"]},
    {"slug": "cronograma", "title": "Cronograma de Execução", "category": "technical", "order": 7, "vars": ["prazo_execucao", "dias_campo", "dias_escritorio"]},
    {"slug": "investimento", "title": "Investimento", "category": "financial", "order": 8, "vars": ["ValorProposta", "ValorExtenso"], "null_content": True},
    {"slug": "condicoes_pagamento", "title": "Condições de Pagamento", "category": "financial", "order": 9, "vars": ["mobilizacao_percentual", "mobilizacao_valor", "restante_percentual", "restante_valor"], "null_content": True},
    {"slug": "dados_bancarios", "title": "Dados Bancários", "category": "financial", "order": 10, "vars": ["Banco", "Agencia", "Conta", "PIX"], "null_content": True},
    {"slug": "consideracoes", "title": "Considerações Finais", "category": "legal", "order": 11, "vars": ["Empresa"]},
    {"slug": "rodape", "title": "Rodapé", "category": "layout", "order": 99, "vars": ["Empresa", "CNPJ", "whatsapp"]}
]

sql = """
CREATE TABLE IF NOT EXISTS `service_type_blocks` (
  `id` int(11) AUTO_INCREMENT PRIMARY KEY,
  `service_type_id` int(11) NOT NULL,
  `block_slug` varchar(50) NOT NULL,
  `block_title` varchar(100) NOT NULL,
  `category` enum('layout','presentation','technical','financial','legal') DEFAULT 'technical',
  `display_order` int(11) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `default_content` longtext,
  `allowed_vars` json,
  `is_active` tinyint(1) DEFAULT 1
);

INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
"""

rows = []

for svc_id in services:
    for block in blocks:
        # Handle Vars
        current_vars = list(block["vars"])
        if block["slug"] == "equipamentos" and svc_id == drone_service_id:
            current_vars.append("Drone")
        
        vars_json = json.dumps(current_vars)
        
        # Handle Content
        if block.get("null_content"):
            content = "NULL"
        else:
            # Simple wrapper to avoid escaping specific chars issue in python string but valid for SQL
            # Escape single quotes in content if any (using double single quote for SQL)
            raw_content = f"<p>Conteúdo padrão para {block['title']}</p>"
            content = f"'{raw_content}'"
            
        row = f"({svc_id}, '{block['slug']}', '{block['title']}', '{block['category']}', {block['order']}, 1, {content}, '{vars_json}', 1)"
        rows.append(row)

sql += ",\n".join(rows) + ";"


output_file = "setup_service_table.sql"
with open(output_file, "w", encoding="utf-8") as f:
    f.write(sql)

print(f"File {output_file} generated successfully.")

