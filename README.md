# IESB - Sistema de Controle de Acesso Acadêmico

Sistema simplificado de controle de acesso acadêmico desenvolvido para o IESB (Instituto de Educação Superior de Brasília), com foco em arquitetura limpa, regras de negócio no banco de dados e integração futura com sistemas de catraca e BI.

---

## 📋 Descrição da Solução

Este sistema permite:

- **Cadastro e gestão de alunos** com controle de status acadêmico (ATIVO/INATIVO)
- **Registro de solicitações acadêmicas** (matrícula, trancamento, cancelamento, etc.)
- **Controle de fluxo de status** das solicitações (ABERTA → ANÁLISE → FINALIZADA)
- **Auditoria completa** de alterações via tabela de logs
- **Consulta de acesso** que determina se aluno pode ou não acessar as dependências do IESB

### Regra de Negócio Principal

> **Acesso é liberado apenas para alunos ATIVOS sem solicitações pendentes (ABERTA ou ANÁLISE)**

---

## 🏗️ Arquitetura do Projeto

```
iesb-acesso-academico/
├── sql/
│   └── database.sql          # Script completo do banco de dados
├── php/
│   ├── index.php             # Dashboard principal
│   ├── includes/
│   │   ├── config.php        # Configuração de conexão
│   │   ├── functions.php     # Funções utilitárias
│   │   ├── AlunoService.php  # Camada de serviço - Alunos
│   │   └── SolicitacaoService.php  # Camada de serviço - Solicitações
│   ├── pages/
│   │   ├── aluno-cadastrar.php     # Cadastro de alunos
│   │   ├── aluno-listar.php        # Listagem de alunos
│   │   ├── solicitacao-abrir.php   # Abertura de solicitações
│   │   ├── solicitacao-listar.php  # Listagem e alteração de status
│   │   └── acesso-consultar.php    # Consulta de acesso
│   └── assets/
│       └── css/
│           └── style.css     # Estilos CSS
└── README.md                 # Este arquivo
```

---

## 🛠️ Tecnologias Utilizadas

| Componente | Versão | Descrição |
|------------|--------|-----------|
| PHP | 8.0+ | Linguagem de programação (estruturada, sem framework) |
| PostgreSQL | 13+ | Banco de dados relacional |
| PDO | Nativo | Driver de conexão com PostgreSQL |
| HTML5 | - | Estrutura das páginas |
| CSS3 | - | Estilização responsiva |

---

## ⚙️ Como Executar o Projeto

### Pré-requisitos

1. **PHP 8.0+** com extensão PDO PostgreSQL
2. **PostgreSQL 13+** instalado e configurado
3. **Servidor Web** (Apache/Nginx) ou PHP Built-in Server

### Passo a Passo

#### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd iesb-acesso-academico
```

#### 2. Configure o Banco de Dados

```bash
# Acesse o PostgreSQL
psql -U postgres

# Crie o banco de dados
CREATE DATABASE iesb_acesso_academico;

# Execute o script SQL
\c iesb_acesso_academico
\i sql/database.sql
```

Ou execute diretamente:

```bash
psql -U postgres -d iesb_acesso_academico -f sql/database.sql
```

#### 3. Configure a Conexão PHP

Edite o arquivo `php/includes/config.php` ou use variáveis de ambiente:

```php
// Opção 1: Editar diretamente
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'iesb_acesso_academico');
define('DB_USER', 'postgres');
define('DB_PASS', 'sua_senha');

// Opção 2: Variáveis de ambiente (recomendado)
export DB_HOST=localhost
export DB_PORT=5432
export DB_NAME=iesb_acesso_academico
export DB_USER=postgres
export DB_PASS=sua_senha
```

#### 4. Inicie o Servidor

**Opção 1: PHP Built-in Server (desenvolvimento)**

```bash
cd php
php -S localhost:8000
```

Acesse: `http://localhost:8000`

**Opção 2: Apache/Nginx (produção)**

Configure o document root para a pasta `php/` e acesse via navegador.

---

## 📊 Estrutura do Banco de Dados

### Tabelas

| Tabela | Descrição |
|--------|-----------|
| `alunos` | Cadastro de alunos com dados pessoais e status |
| `solicitacoes` | Registro de solicitações acadêmicas |
| `log_solicitacoes` | Auditoria de alterações em solicitações |

### Stored Procedures

| Procedure | Descrição |
|-----------|-----------|
| `sp_criar_aluno()` | Cria novo aluno com validações |
| `sp_criar_solicitacao()` | Cria solicitação (valida se aluno está ativo) |
| `sp_alterar_status_solicitacao()` | Altera status com auditoria em log |
| `sp_consulta_acesso_aluno()` | Consulta se aluno tem acesso liberado |

### Views

| View | Descrição |
|------|-----------|
| `vw_status_academico` | Visão consolidada do status de todos os alunos |

### Functions

| Function | Descrição |
|----------|-----------|
| `fn_dias_em_aberto()` | Calcula dias que solicitação está em aberto |

---

## 🔒 Regras de Negócio Implementadas

### No Banco de Dados (SQL)

1. **Aluno INATIVO não pode abrir solicitação**
   - Implementado em: `sp_criar_solicitacao()`
   - Lança exceção se tentativa for detectada

2. **Solicitações FINALIZADAS não podem ser alteradas**
   - Implementado em: `sp_alterar_status_solicitacao()`
   - Validação antes de qualquer alteração

3. **Toda alteração gera log**
   - Implementado em: `sp_alterar_status_solicitacao()`
   - INSERT automático na tabela `log_solicitacoes`

4. **Acesso liberado apenas para ATIVOS sem pendências**
   - Implementado em: `sp_consulta_acesso_aluno()` e `vw_status_academico`

### Validações Adicionais

- CPF único e formatado
- Matrícula única
- Constraints de NOT NULL em campos obrigatórios
- Tratamento de exceções com TRY/CATCH

---

## 🎯 Decisões Técnicas

### 1. Uso de Stored Procedures

**Decisão:** Implementar toda a lógica de negócio crítica em stored procedures.

**Justificativa:**
- Centraliza regras de negócio no banco
- Facilita manutenção e auditoria
- Permite reuso por diferentes aplicações (API, BI, etc.)
- Melhor performance em operações complexas

### 2. PostgreSQL ao invés de SQL Server

**Decisão:** Usar PostgreSQL (conforme permitido no escopo).

**Justificativa:**
- Custo zero (open source)
- Excelente suporte a procedures, functions e triggers
- Melhor integração com PHP via PDO
- Comunidade ativa e documentação robusta

### 3. PHP Estruturado (Sem Framework)

**Decisão:** Desenvolver em PHP puro, sem frameworks.

**Justificativa:**
- Cumpre requisito do projeto
- Maior controle sobre o código
- Menor curva de aprendizado para manutenção
- Facilita compreensão da arquitetura

### 4. Camada de Serviço (Service Layer)

**Decisão:** Criar classes `AlunoService` e `SolicitacaoService`.

**Justificativa:**
- Separação de responsabilidades
- Facilita testes unitários
- Centraliza chamadas às procedures
- Prepara para futura migração para API REST

### 5. Índices no Banco

**Decisão:** Criar índices em campos de busca frequente.

**Justificativa:**
- Melhora performance de consultas
- Campos indexados: matrícula, CPF, status, id_aluno
- Essencial para integração com catracas (consultas em tempo real)

### 6. Script Idempotente

**Decisão:** SQL script remove e recria objetos (DROP IF EXISTS).

**Justificativa:**
- Permite execução múltipla sem erros
- Facilita deploy em diferentes ambientes
- Garante estado consistente do banco

---

## 🚀 Pontos de Melhoria Futura

### Curto Prazo

1. **Autenticação e Autorização**
   - Login para administradores
   - Níveis de permissão (admin, atendente, aluno)

2. **Validações Adicionais**
   - Validação de CPF no PHP
   - Máscaras de input (matrícula, CPF)

3. **Relatórios**
   - Exportação para PDF/Excel
   - Gráficos de solicitações por período

### Médio Prazo

4. **API REST**
   - Endpoints para integração com catracas
   - Autenticação via JWT
   - Rate limiting

5. **Sistema de Catraca**
   - Integração via API
   - Leitura de QR Code/cartão
   - Logs de acesso físico

6. **Dashboard de BI**
   - Indicadores de retenção
   - Tempo médio de resolução de solicitações
   - Alunos com pendências críticas

### Longo Prazo

7. **Microserviços**
   - Separar módulo de acesso
   - Fila de processamento assíncrono
   - Cache com Redis

8. **Mobile App**
   - App para alunos consultarem status
   - Notificações push

---

## 📝 Exemplos de Uso

### Cadastrar Aluno (via SQL)

```sql
CALL sp_criar_aluno(
    '20250010', 
    'João da Silva', 
    '123.456.789-00', 
    'Engenharia de Software', 
    'Brasília'
);
```

### Abrir Solicitação (via SQL)

```sql
CALL sp_criar_solicitacao(
    1, 
    'DECLARACAO', 
    'Solicito declaração para estágio'
);
```

### Consultar Acesso (via SQL)

```sql
CALL sp_consulta_acesso_aluno(1);
```

### Ver Status Acadêmico (via SQL)

```sql
SELECT * FROM vw_status_academico;
```

### Calcular Dias em Aberto (via SQL)

```sql
SELECT fn_dias_em_aberto(1);
```

---

## 🔧 Configuração para Produção

### Segurança

1. **Alterar senhas padrão**
2. **Configurar SSL/TLS**
3. **Restringir acesso ao banco** (firewall)
4. **Habilitar logs de auditoria**
5. **Fazer backups regulares**

### Performance

1. **Configurar pool de conexões**
2. **Habilitar cache de queries**
3. **Monitorar índices (análise periódica)**
4. **Configurar replicação** (leitura em slave)

---

## 👥 Contribuição

1. Faça um fork do projeto
2. Crie uma branch com sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'feat: adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto é de uso interno do IESB.

---

## 📞 Contato

**IESB - Instituto de Educação Superior de Brasília**

- Site: [www.iesb.edu.br](https://www.iesb.edu.br)
- Suporte Técnico: suporte@iesb.br

---

## 📚 Referências

- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [PHP PDO Documentation](https://www.php.net/manual/pt_BR/book.pdo.php)
- [IESB - Site Oficial](https://www.iesb.edu.br)

---

**Versão:** 1.0  
**Última Atualização:** 2026-03-02  
**Desenvolvido por:** Analista de Sistemas IESB
#   s i s - a c a d  
 