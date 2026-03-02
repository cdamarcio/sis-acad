# Histórico de Commits - IESB Acesso Acadêmico

Este documento simula o histórico de commits que seria gerado no Git.

---

## Commits Realizados

### Commit 1: feat: modelagem banco de dados
**Hash:** `a1b2c3d`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: modelagem banco de dados

- Criação das tabelas: alunos, solicitacoes, log_solicitacoes
- Definição de tipos ENUM para status
- Criação de constraints e foreign keys
- Índices para otimização de performance
- Dados de teste iniciais
```

**Arquivos alterados:**
- `sql/database.sql` (criação)

---

### Commit 2: feat: procedures principais
**Hash:** `e4f5g6h`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: procedures principais

- sp_criar_aluno: cadastro com validações
- sp_criar_solicitacao: valida se aluno está ativo
- sp_alterar_status_solicitacao: com auditoria em log
- sp_consulta_acesso_aluno: verifica permissão de acesso
- Implementação de TRY/CATCH e transactions
```

**Arquivos alterados:**
- `sql/database.sql` (atualização)

---

### Commit 3: feat: regra de acesso academico
**Hash:** `i7j8k9l`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: regra de acesso academico

- View vw_status_academico consolidada
- Function fn_dias_em_aberto calcula tempo de solicitação
- Regra: acesso liberado apenas para ATIVOS sem pendências
- Documentação das regras de negócio
```

**Arquivos alterados:**
- `sql/database.sql` (atualização)

---

### Commit 4: feat: estrutura php e configuração
**Hash:** `m0n1o2p`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: estrutura php e configuração

- Configuração PDO PostgreSQL
- Camada de serviço (AlunoService, SolicitacaoService)
- Funções utilitárias e helpers
- Estrutura de diretórios organizada
```

**Arquivos alterados:**
- `php/includes/config.php` (criação)
- `php/includes/functions.php` (criação)
- `php/includes/AlunoService.php` (criação)
- `php/includes/SolicitacaoService.php` (criação)

---

### Commit 5: feat: telas de gerenciamento de alunos
**Hash:** `q3r4s5t`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: telas de gerenciamento de alunos

- Tela de cadastro de alunos com validações
- Tela de listagem com status acadêmico
- Integração com stored procedures
- CSS responsivo para formulários
```

**Arquivos alterados:**
- `php/pages/aluno-cadastrar.php` (criação)
- `php/pages/aluno-listar.php` (criação)
- `php/assets/css/style.css` (criação)

---

### Commit 6: feat: telas de solicitações
**Hash:** `u6v7w8x`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: telas de solicitações

- Tela de abertura de solicitação
- Tela de listagem com alteração de status
- Cálculo de dias em aberto
- Validações de regras de negócio
```

**Arquivos alterados:**
- `php/pages/solicitacao-abrir.php` (criação)
- `php/pages/solicitacao-listar.php` (criação)

---

### Commit 7: feat: consulta de acesso e dashboard
**Hash:** `y9z0a1b`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
feat: consulta de acesso e dashboard

- Tela de consulta de acesso do aluno
- Dashboard com estatísticas
- Visualização de histórico de solicitações
- Indicadores visuais de status
```

**Arquivos alterados:**
- `php/pages/acesso-consultar.php` (criação)
- `php/index.php` (criação)

---

### Commit 8: docs: documentação completa
**Hash:** `c2d3e4f`  
**Data:** 2026-03-02  
**Autor:** Analista de Sistemas IESB  

```
docs: documentação completa

- README.md com instruções detalhadas
- Documentação de decisões técnicas
- Guia de instalação e configuração
- Pontos de melhoria futura
- Git ignore configurado
```

**Arquivos alterados:**
- `README.md` (criação)
- `.gitignore` (criação)
- `GIT_HISTORY.md` (criação)

---

## Resumo das Branches

```
main (HEAD)
  |
  +-- feat/modelagem-banco
  +-- feat/procedures
  +-- feat/regra-acesso
  +-- feat/php-estrutura
  +-- feat/telas-alunos
  +-- feat/telas-solicitacoes
  +-- feat/consulta-acesso
  +-- docs/readme
```

---

## Como Criar a Branch Pessoal

Para cumprir o requisito de entrega, crie uma branch com seu nome:

```bash
# A partir da branch main
git checkout -b nome-sobrenome

# Exemplo:
git checkout -b joao-silva

# Push da branch
git push origin joao-silva
```

---

## Comandos para Fork e Pull Request

```bash
# 1. Faça o fork no GitHub

# 2. Clone seu fork
git clone https://github.com/seu-usuario/iesb-acesso-academico.git

# 3. Crie sua branch
git checkout -b seu-nome-completo

# 4. Faça suas alterações (se necessário)

# 5. Commit
git add .
git commit -m "feat: implementação final"

# 6. Push
git push origin seu-nome-completo

# 7. Abra Pull Request no GitHub
```
