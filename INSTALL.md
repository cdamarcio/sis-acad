# Guia de Instalação - IESB Acesso Acadêmico

## Requisitos do Sistema

### Mínimos
- PHP 8.0+
- PostgreSQL 13+
- Extensão PDO pgsql
- 512MB RAM
- 100MB espaço em disco

### Recomendados
- PHP 8.2+
- PostgreSQL 15+
- 1GB RAM
- SSD para banco de dados

---

## Instalação Passo a Passo

### 1. Instalar PHP e PostgreSQL

#### Ubuntu/Debian

```bash
# Atualizar repositórios
sudo apt update

# Instalar PHP e extensões
sudo apt install php8.1 php8.1-pgsql php8.1-pdo php8.1-mbstring

# Instalar PostgreSQL
sudo apt install postgresql postgresql-contrib

# Iniciar PostgreSQL
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

#### Windows

1. Baixe XAMPP ou WAMP
2. Instale PostgreSQL do site oficial
3. Habilite a extensão `pgsql` no php.ini

#### macOS

```bash
# Usando Homebrew
brew install php
brew install postgresql
brew services start postgresql
```

---

### 2. Configurar Banco de Dados

```bash
# Acessar PostgreSQL como superusuário
sudo -u postgres psql

# Criar banco de dados
CREATE DATABASE iesb_acesso_academico;

# Criar usuário (opcional, mas recomendado)
CREATE USER iesb_user WITH PASSWORD 'senha_segura';
GRANT ALL PRIVILEGES ON DATABASE iesb_acesso_academico TO iesb_user;

# Sair
\q
```

---

### 3. Executar Script SQL

```bash
# Navegar até a pasta do projeto
cd iesb-acesso-academico

# Executar script
psql -U postgres -d iesb_acesso_academico -f sql/database.sql

# Ou com usuário específico
psql -U iesb_user -d iesb_acesso_academico -f sql/database.sql
```

**Verificação:**
```sql
-- Conectar ao banco
psql -U postgres -d iesb_acesso_academico

-- Listar tabelas
\dt

-- Deve mostrar: alunos, solicitacoes, log_solicitacoes

-- Testar view
SELECT * FROM vw_status_academico;

-- Sair
\q
```

---

### 4. Configurar PHP

#### Opção A: Variáveis de Ambiente (Recomendado)

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar com suas configurações
nano .env
```

Conteúdo do `.env`:
```
DB_HOST=localhost
DB_PORT=5432
DB_NAME=iesb_acesso_academico
DB_USER=postgres
DB_PASS=sua_senha
```

#### Opção B: Editar Configuração Diretamente

Edite `php/includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'iesb_acesso_academico');
define('DB_USER', 'postgres');
define('DB_PASS', 'sua_senha');
```

---

### 5. Iniciar Servidor

#### Opção A: PHP Built-in Server (Desenvolvimento)

```bash
cd php
php -S localhost:8000
```

Acesse: http://localhost:8000

#### Opção B: Apache

```bash
# Criar link simbólico (Ubuntu)
sudo ln -s /caminho/do/projeto/php /var/www/html/iesb-acesso

# Acessar: http://localhost/iesb-acesso
```

#### Opção C: Nginx

Configuração básica:

```nginx
server {
    listen 80;
    server_name iesb-acesso.local;
    root /caminho/do/projeto/php;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

---

### 6. Testar Instalação

1. Acesse a página inicial
2. Verifique se o dashboard carrega
3. Teste cadastrar um aluno
4. Teste abrir uma solicitação
5. Teste alterar status
6. Teste consulta de acesso

---

## Solução de Problemas

### Erro: "could not find driver"

**Solução:** Instalar extensão PDO PostgreSQL

```bash
# Ubuntu
sudo apt install php-pgsql

# Reiniciar servidor
sudo systemctl restart apache2
# ou
sudo systemctl restart php8.1-fpm
```

### Erro: "permission denied for database"

**Solução:** Conceder permissões

```sql
-- No PostgreSQL
GRANT ALL PRIVILEGES ON DATABASE iesb_acesso_academico TO seu_usuario;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO seu_usuario;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO seu_usuario;
```

### Erro: "function does not exist"

**Solução:** Reexecutar script SQL

```bash
psql -U postgres -d iesb_acesso_academico -f sql/database.sql
```

### Erro: "connection refused"

**Solução:** Verificar se PostgreSQL está rodando

```bash
# Verificar status
sudo systemctl status postgresql

# Iniciar se necessário
sudo systemctl start postgresql
```

---

## Configuração de Produção

### 1. Segurança

```bash
# Alterar senha do postgres
sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD 'nova_senha_segura';"

# Configurar pg_hba.conf
sudo nano /etc/postgresql/15/main/pg_hba.conf

# Alterar para:
# local   all   all   md5
# host    all   all   127.0.0.1/32   md5
```

### 2. Performance

```sql
-- Analisar tabelas
ANALYZE alunos;
ANALYZE solicitacoes;
ANALYZE log_solicitacoes;

-- Vacuum
VACUUM ANALYZE;
```

### 3. Backup

```bash
# Backup diário (adicionar ao crontab)
0 2 * * * pg_dump -U postgres iesb_acesso_academico > /backup/iesb_$(date +\%Y\%m\%d).sql
```

---

## Suporte

Em caso de problemas:

1. Verifique os logs de erro do PHP
2. Verifique os logs do PostgreSQL
3. Consulte o README.md
4. Abra uma issue no repositório
