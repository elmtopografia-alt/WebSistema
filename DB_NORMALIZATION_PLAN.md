# Roadmap: Database Normalization (STR-002)

## Contexto

Atualmente, o sistema trata "Clientes", "Autores de Proposta" e "Contatos" como entidades separadas ou duplicadas. Isso gera inconsistência (ex: atualizar o telefone do cliente não atualiza nas propostas antigas ou em novos contextos).

## Objetivo

Unificar todas as entidades de "Pessoas" (Pessoas Físicas e Jurídicas) em uma estrutura relacional robusta.

## Proposta de Schema

### 1. Tabela `Entidades` (Nova)

Centraliza IDs e Tipos.

```sql
CREATE TABLE Entidades (
    id_entidade INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('PF', 'PJ') NOT NULL,
    nome_razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255),
    documento_principal VARCHAR(20) UNIQUE, -- CPF ou CNPJ
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_criador INT NOT NULL -- Multi-tenancy
);
```

### 2. Tabela `Contatos` (Nova)

Permite múltiplos contatos por entidade.

```sql
CREATE TABLE Contatos (
    id_contato INT AUTO_INCREMENT PRIMARY KEY,
    id_entidade INT NOT NULL,
    tipo ENUM('Email', 'Celular', 'Telefone', 'WhatsApp'),
    valor VARCHAR(150) NOT NULL,
    observacao VARCHAR(50),
    FOREIGN KEY (id_entidade) REFERENCES Entidades(id_entidade) ON DELETE CASCADE
);
```

### 3. Tabela `Enderecos` (Nova)

Permite múltiplos endereços (Cobrança, Obra, Correspondência).

```sql
CREATE TABLE Enderecos (
    id_endereco INT AUTO_INCREMENT PRIMARY KEY,
    id_entidade INT NOT NULL,
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    tipo_endereco VARCHAR(50) DEFAULT 'Principal',
    FOREIGN KEY (id_entidade) REFERENCES Entidades(id_entidade) ON DELETE CASCADE
);
```

## Plano de Migração (Safe Strategy)

### Fase 1: Criação e Coexistência (Day 0)

1. Criar as novas tabelas.
2. Não alterar o código existente de leitura.
3. Alterar `salvar_cliente.php` para escrever **TAMBÉM** na nova tabela `Entidades` (Dual Write).

### Fase 2: Backfill (Day 1)

1. Criar script `admin_migrar_clientes.php`.
2. O script lê `Clientes` e insere em `Entidades`, `Contatos` e `Enderecos`.
3. Validar integridade dos dados migrados.

### Fase 3: Virada de Chave (Day 3)

1. Alterar `meus_clientes.php` para ler de `Entidades`.
2. Alterar `criar_proposta.php` para buscar autocomplete de `Entidades`.

### Fase 4: Limpeza (Day 30)

1. Remover tabela legada `Clientes` após garantir que nenhum script a utiliza.

## Ação Imediata

Nenhuma ação de código é necessária hoje. Este documento serve como guia para a próxima Sprint de Refatoração.
