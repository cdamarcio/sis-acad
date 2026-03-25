### 1. Descrição da Solução

O **IESB Acesso Acadêmico** é um sistema para gestão de fluxo estudantil. Ele controla o status de alunos (ATIVO/INATIVO) e gerencia solicitações acadêmicas, garantindo que o acesso físico seja liberado apenas para quem não possui pendências críticas.

### 2. Decisões Técnicas Tomadas

* **Camada de Serviço (Service Layer):** Uso das classes `AlunoService` e `SolicitacaoService` para separar a lógica de negócio da interface.
* **Regras no Banco (Stored Procedures):** Centralização de validações críticas no PostgreSQL (como a `sp_criar_aluno`) para garantir integridade independente do frontend.
* **Segurança com PDO:** Utilização de *Prepared Statements* para mitigar riscos de SQL Injection.
* **Arquitetura Idempotente:** Scripts SQL que utilizam `DROP IF EXISTS` para facilitar o deploy e reinstalação.

### 3. Como Executar o Projeto

1. **Banco de Dados:** Crie o banco `iesb_acesso_academico` e execute o script em `sql/database.sql`.
2. **Configuração:** Ajuste as credenciais de acesso no arquivo `php/includes/config.php`.
3. **Servidor:** Navegue até a pasta `/php` e inicie o servidor local:
`php -S localhost:8000`.

### 4. Versões Utilizadas

| Componente | Versão |
| --- | --- |
| **PHP** | 8.0+ |
| **PostgreSQL** | 13+ (ou 17, conforme instalado) |
| **PDO Driver** | Nativo |

### 5. Pontos de Melhoria Futuros

* **Autenticação:** Implementação de login seguro para administradores e alunos.
* **API REST:** Criação de endpoints para integração direta com hardware de catracas.
* **Dashboard de BI:** Relatórios gráficos sobre o tempo médio de resolução de solicitações.

### 6. Link do Vídeo:

* https://www.linkedin.com/posts/marcioro_ugcPost-7434630804028149762-IleV?utm_source=share&utm_medium=member_desktop&rcm=ACoAADqvwxkBSll8_p6jGpYLJLlYXWKUfQCGKKI

---

## Autor

**Márcio Rodrigues de Oliveira | Desenvolvedor Líder Fullstack | cda.marcio@gmail.com**

---

### MIT License

### Copyright (c) 2026 MARCIO RODRIGUES DE OLIVEIRA

É concedida permissão, gratuitamente, a qualquer pessoa que obtenha uma cópia
deste software e arquivos de documentação associados (o "Software"), para lidar
com o Software sem restrições, incluindo, sem limitação, os direitos
de usar, copiar, modificar, fundir, publicar, distribuir, sublicenciar e/ou vender
cópias do Software, e para permitir que as pessoas a quem o Software é
fornecido o façam, sujeitas às seguintes condições:

O aviso de direitos autorais acima e este aviso de permissão devem ser incluídos em todas as
cópias ou partes substanciais do Software.

O SOFTWARE É FORNECIDO "NO ESTADO EM QUE SE ENCONTRA", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU
IMPLÍCITA, INCLUINDO, MAS NÃO SE LIMITANDO ÀS GARANTIAS DE COMERCIALIZAÇÃO,
ADEQUAÇÃO A UM FIM ESPECÍFICO E NÃO VIOLAÇÃO. EM NENHUMA HIPÓTESE OS
AUTORES OU DETENTORES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS POR QUAISQUER REIVINDICAÇÕES, DANOS OU OUTRAS
RESPONSABILIDADES, SEJA EM AÇÃO CONTRATUAL, EXTRACONTRATUAL OU DE OUTRA NATUREZA, DECORRENTES DE,
OU RELACIONADAS COM O SOFTWARE OU O USO OU OUTRAS NEGOCIAÇÕES COM O
SOFTWARE.

