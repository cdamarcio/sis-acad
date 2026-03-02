-- =====================================================
-- IESB - Sistema de Controle de Acesso Acadêmico
-- Script de Criação do Banco de Dados PostgreSQL
-- =====================================================
-- Autor: Analista de Sistemas IESB
-- Data: 2026-03-02
-- Versão: 1.0
-- =====================================================

-- =====================================================
-- 1. LIMPEZA E PREPARAÇÃO (IDEMPOTENTE)
-- =====================================================

-- Remove objetos existentes para garantir idempotência
DROP VIEW IF EXISTS vw_status_academico CASCADE;
DROP FUNCTION IF EXISTS fn_dias_em_aberto(INTEGER) CASCADE;
DROP PROCEDURE IF EXISTS sp_consulta_acesso_aluno(INTEGER) CASCADE;
DROP PROCEDURE IF EXISTS sp_alterar_status_solicitacao(INTEGER, VARCHAR) CASCADE;
DROP PROCEDURE IF EXISTS sp_criar_solicitacao(INTEGER, VARCHAR, TEXT) CASCADE;
DROP PROCEDURE IF EXISTS sp_criar_aluno(VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR) CASCADE;
DROP TABLE IF EXISTS log_solicitacoes CASCADE;
DROP TABLE IF EXISTS solicitacoes CASCADE;
DROP TABLE IF EXISTS alunos CASCADE;

-- Remove tipo ENUM se existir
DROP TYPE IF EXISTS status_aluno CASCADE;
DROP TYPE IF EXISTS status_solicitacao CASCADE;
DROP TYPE IF EXISTS tipo_solicitacao CASCADE;

-- =====================================================
-- 2. CRIAÇÃO DE TIPOS ENUM
-- =====================================================

CREATE TYPE status_aluno AS ENUM ('ATIVO', 'INATIVO');
CREATE TYPE status_solicitacao AS ENUM ('ABERTA', 'ANALISE', 'FINALIZADA');
CREATE TYPE tipo_solicitacao AS ENUM ('MATRICULA', 'TRANCAMENTO', 'CANCELAMENTO', 'REMATRICULA', 'DECLARACAO');

-- =====================================================
-- 3. CRIAÇÃO DAS TABELAS
-- =====================================================

-- Tabela: alunos
-- Armazena os dados dos alunos do IESB
CREATE TABLE alunos (
    id SERIAL PRIMARY KEY,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    curso VARCHAR(100) NOT NULL,
    campus VARCHAR(50) NOT NULL,
    status status_aluno DEFAULT 'ATIVO' NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    -- Constraints
    CONSTRAINT chk_matricula_not_empty CHECK (matricula <> ''),
    CONSTRAINT chk_nome_not_empty CHECK (nome <> ''),
    CONSTRAINT chk_cpf_not_empty CHECK (cpf <> ''),
    CONSTRAINT chk_curso_not_empty CHECK (curso <> ''),
    CONSTRAINT chk_campus_not_empty CHECK (campus <> '')
);

-- Comentários para documentação
COMMENT ON TABLE alunos IS 'Tabela principal de alunos do IESB';
COMMENT ON COLUMN alunos.id IS 'Identificador único do aluno (PK)';
COMMENT ON COLUMN alunos.matricula IS 'Número de matrícula único do aluno';
COMMENT ON COLUMN alunos.nome IS 'Nome completo do aluno';
COMMENT ON COLUMN alunos.cpf IS 'CPF do aluno (formato: 000.000.000-00)';
COMMENT ON COLUMN alunos.curso IS 'Curso matriculado';
COMMENT ON COLUMN alunos.campus IS 'Campus de matrícula';
COMMENT ON COLUMN alunos.status IS 'Status acadêmico: ATIVO ou INATIVO';
COMMENT ON COLUMN alunos.data_cadastro IS 'Data/hora do cadastro no sistema';

-- Tabela: solicitacoes
-- Registra solicitações acadêmicas dos alunos
CREATE TABLE solicitacoes (
    id SERIAL PRIMARY KEY,
    id_aluno INTEGER NOT NULL,
    tipo tipo_solicitacao NOT NULL,
    descricao TEXT,
    status status_solicitacao DEFAULT 'ABERTA' NOT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    data_fechamento TIMESTAMP,
    
    -- Constraints
    CONSTRAINT fk_solicitacao_aluno FOREIGN KEY (id_aluno) 
        REFERENCES alunos(id) ON DELETE CASCADE,
    CONSTRAINT chk_data_fechamento CHECK (
        (status = 'FINALIZADA' AND data_fechamento IS NOT NULL) OR
        (status <> 'FINALIZADA' AND data_fechamento IS NULL)
    )
);

COMMENT ON TABLE solicitacoes IS 'Registro de solicitações acadêmicas';
COMMENT ON COLUMN solicitacoes.id IS 'Identificador único da solicitação (PK)';
COMMENT ON COLUMN solicitacoes.id_aluno IS 'Referência ao aluno (FK)';
COMMENT ON COLUMN solicitacoes.tipo IS 'Tipo: MATRICULA, TRANCAMENTO, CANCELAMENTO, REMATRICULA, DECLARACAO';
COMMENT ON COLUMN solicitacoes.descricao IS 'Descrição detalhada da solicitação';
COMMENT ON COLUMN solicitacoes.status IS 'Status: ABERTA, ANALISE, FINALIZADA';
COMMENT ON COLUMN solicitacoes.data_abertura IS 'Data/hora de abertura';
COMMENT ON COLUMN solicitacoes.data_fechamento IS 'Data/hora de finalização (apenas para FINALIZADA)';

-- Tabela: log_solicitacoes
-- Auditoria de todas as alterações em solicitações
CREATE TABLE log_solicitacoes (
    id SERIAL PRIMARY KEY,
    id_solicitacao INTEGER NOT NULL,
    status_anterior status_solicitacao NOT NULL,
    novo_status status_solicitacao NOT NULL,
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usuario_alteracao VARCHAR(50) DEFAULT CURRENT_USER,
    
    -- Constraints
    CONSTRAINT fk_log_solicitacao FOREIGN KEY (id_solicitacao) 
        REFERENCES solicitacoes(id) ON DELETE CASCADE
);

COMMENT ON TABLE log_solicitacoes IS 'Log de auditoria de alterações em solicitações';
COMMENT ON COLUMN log_solicitacoes.id IS 'Identificador único do log (PK)';
COMMENT ON COLUMN log_solicitacoes.id_solicitacao IS 'Referência à solicitação (FK)';
COMMENT ON COLUMN log_solicitacoes.status_anterior IS 'Status antes da alteração';
COMMENT ON COLUMN log_solicitacoes.novo_status IS 'Status após a alteração';
COMMENT ON COLUMN log_solicitacoes.data_alteracao IS 'Data/hora da alteração';
COMMENT ON COLUMN log_solicitacoes.usuario_alteracao IS 'Usuário que fez a alteração';

-- =====================================================
-- 4. ÍNDICES PARA OTIMIZAÇÃO
-- =====================================================

-- Índice para busca por matrícula (frequente em consultas de acesso)
CREATE INDEX idx_alunos_matricula ON alunos(matricula);

-- Índice para busca por CPF
CREATE INDEX idx_alunos_cpf ON alunos(cpf);

-- Índice para busca de solicitações por aluno
CREATE INDEX idx_solicitacoes_aluno ON solicitacoes(id_aluno);

-- Índice para busca de solicitações por status (importante para regras de acesso)
CREATE INDEX idx_solicitacoes_status ON solicitacoes(status);

-- Índice composto para verificação de pendências
CREATE INDEX idx_solicitacoes_aluno_status ON solicitacoes(id_aluno, status);

-- Índice para logs (busca por solicitação)
CREATE INDEX idx_log_solicitacao ON log_solicitacoes(id_solicitacao);

-- =====================================================
-- 5. STORED PROCEDURES
-- =====================================================

-- Procedure: sp_criar_aluno
-- Cria um novo aluno no sistema
CREATE OR REPLACE PROCEDURE sp_criar_aluno(
    p_matricula VARCHAR,
    p_nome VARCHAR,
    p_cpf VARCHAR,
    p_curso VARCHAR,
    p_campus VARCHAR
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Validações de negócio
    IF p_matricula IS NULL OR TRIM(p_matricula) = '' THEN
        RAISE EXCEPTION 'Matrícula é obrigatória';
    END IF;
    
    IF p_nome IS NULL OR TRIM(p_nome) = '' THEN
        RAISE EXCEPTION 'Nome é obrigatório';
    END IF;
    
    IF p_cpf IS NULL OR TRIM(p_cpf) = '' THEN
        RAISE EXCEPTION 'CPF é obrigatório';
    END IF;
    
    -- Verifica se matrícula já existe
    IF EXISTS (SELECT 1 FROM alunos WHERE matricula = p_matricula) THEN
        RAISE EXCEPTION 'Matrícula % já cadastrada', p_matricula;
    END IF;
    
    -- Verifica se CPF já existe
    IF EXISTS (SELECT 1 FROM alunos WHERE cpf = p_cpf) THEN
        RAISE EXCEPTION 'CPF % já cadastrado', p_cpf;
    END IF;
    
    -- Insere o aluno
    INSERT INTO alunos (matricula, nome, cpf, curso, campus, status)
    VALUES (TRIM(p_matricula), TRIM(p_nome), TRIM(p_cpf), TRIM(p_curso), TRIM(p_campus), 'ATIVO');
    
    RAISE NOTICE 'Aluno cadastrado com sucesso. Matrícula: %', p_matricula;
    
EXCEPTION
    WHEN unique_violation THEN
        RAISE EXCEPTION 'Violação de unicidade: matrícula ou CPF já existe';
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Erro ao cadastrar aluno: %', SQLERRM;
END;
$$;

COMMENT ON PROCEDURE sp_criar_aluno IS 'Cria um novo aluno no sistema com validações';

-- Procedure: sp_criar_solicitacao
-- Cria uma nova solicitação acadêmica
CREATE OR REPLACE PROCEDURE sp_criar_solicitacao(
    p_id_aluno INTEGER,
    p_tipo VARCHAR,
    p_descricao TEXT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status_aluno status_aluno;
    v_tipo_solicitacao tipo_solicitacao;
BEGIN
    -- Validações
    IF p_id_aluno IS NULL THEN
        RAISE EXCEPTION 'ID do aluno é obrigatório';
    END IF;
    
    IF p_tipo IS NULL OR TRIM(p_tipo) = '' THEN
        RAISE EXCEPTION 'Tipo de solicitação é obrigatório';
    END IF;
    
    -- Converte tipo para ENUM
    BEGIN
        v_tipo_solicitacao := p_tipo::tipo_solicitacao;
    EXCEPTION
        WHEN invalid_text_representation THEN
            RAISE EXCEPTION 'Tipo de solicitação inválido: %. Use: MATRICULA, TRANCAMENTO, CANCELAMENTO, REMATRICULA, DECLARACAO', p_tipo;
    END;
    
    -- Verifica se aluno existe
    SELECT status INTO v_status_aluno
    FROM alunos
    WHERE id = p_id_aluno;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Aluno com ID % não encontrado', p_id_aluno;
    END IF;
    
    -- REGRA DE NEGÓCIO: Aluno INATIVO não pode abrir solicitação
    IF v_status_aluno = 'INATIVO' THEN
        RAISE EXCEPTION 'Aluno INATIVO não pode abrir solicitações';
    END IF;
    
    -- Insere a solicitação
    INSERT INTO solicitacoes (id_aluno, tipo, descricao, status, data_abertura)
    VALUES (p_id_aluno, v_tipo_solicitacao, p_descricao, 'ABERTA', CURRENT_TIMESTAMP);
    
    RAISE NOTICE 'Solicitação criada com sucesso para o aluno ID: %', p_id_aluno;
    
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Erro ao criar solicitação: %', SQLERRM;
END;
$$;

COMMENT ON PROCEDURE sp_criar_solicitacao IS 'Cria uma nova solicitação acadêmica com validações de negócio';

-- Procedure: sp_alterar_status_solicitacao
-- Altera o status de uma solicitação com auditoria
CREATE OR REPLACE PROCEDURE sp_alterar_status_solicitacao(
    p_id_solicitacao INTEGER,
    p_novo_status VARCHAR
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status_atual status_solicitacao;
    v_novo_status_enum status_solicitacao;
    v_id_aluno INTEGER;
BEGIN
    -- Validações
    IF p_id_solicitacao IS NULL THEN
        RAISE EXCEPTION 'ID da solicitação é obrigatório';
    END IF;
    
    IF p_novo_status IS NULL OR TRIM(p_novo_status) = '' THEN
        RAISE EXCEPTION 'Novo status é obrigatório';
    END IF;
    
    -- Converte novo status para ENUM
    BEGIN
        v_novo_status_enum := p_novo_status::status_solicitacao;
    EXCEPTION
        WHEN invalid_text_representation THEN
            RAISE EXCEPTION 'Status inválido: %. Use: ABERTA, ANALISE, FINALIZADA', p_novo_status;
    END;
    
    -- Busca status atual e ID do aluno
    SELECT status, id_aluno INTO v_status_atual, v_id_aluno
    FROM solicitacoes
    WHERE id = p_id_solicitacao;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Solicitação com ID % não encontrada', p_id_solicitacao;
    END IF;
    
    -- REGRA DE NEGÓCIO: Solicitações FINALIZADAS não podem ser alteradas
    IF v_status_atual = 'FINALIZADA' THEN
        RAISE EXCEPTION 'Solicitações FINALIZADAS não podem ser alteradas';
    END IF;
    
    -- Não permite alterar para o mesmo status
    IF v_status_atual = v_novo_status_enum THEN
        RAISE NOTICE 'Solicitação já está com status %', v_novo_status_enum;
        RETURN;
    END IF;
    
    -- Inicia transação para garantir atomicidade
    BEGIN
        -- Registra no log antes de alterar
        INSERT INTO log_solicitacoes (id_solicitacao, status_anterior, novo_status)
        VALUES (p_id_solicitacao, v_status_atual, v_novo_status_enum);
        
        -- Atualiza o status da solicitação
        IF v_novo_status_enum = 'FINALIZADA' THEN
            UPDATE solicitacoes 
            SET status = v_novo_status_enum, 
                data_fechamento = CURRENT_TIMESTAMP
            WHERE id = p_id_solicitacao;
        ELSE
            UPDATE solicitacoes 
            SET status = v_novo_status_enum,
                data_fechamento = NULL
            WHERE id = p_id_solicitacao;
        END IF;
        
        RAISE NOTICE 'Status da solicitação % alterado de % para %', 
            p_id_solicitacao, v_status_atual, v_novo_status_enum;
            
    EXCEPTION
        WHEN OTHERS THEN
            RAISE EXCEPTION 'Erro ao alterar status: %', SQLERRM;
    END;
    
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Erro ao alterar status da solicitação: %', SQLERRM;
END;
$$;

COMMENT ON PROCEDURE sp_alterar_status_solicitacao IS 'Altera status da solicitação com auditoria em log';

-- Procedure: sp_consulta_acesso_aluno
-- Consulta se aluno tem acesso liberado
CREATE OR REPLACE PROCEDURE sp_consulta_acesso_aluno(
    p_id_aluno INTEGER
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_aluno RECORD;
    v_pendencias INTEGER;
    v_acesso_liberado BOOLEAN;
    v_mensagem VARCHAR(200);
BEGIN
    -- Validação
    IF p_id_aluno IS NULL THEN
        RAISE EXCEPTION 'ID do aluno é obrigatório';
    END IF;
    
    -- Busca dados do aluno
    SELECT id, matricula, nome, curso, campus, status
    INTO v_aluno
    FROM alunos
    WHERE id = p_id_aluno;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Aluno com ID % não encontrado', p_id_aluno;
    END IF;
    
    -- Conta solicitações pendentes (ABERTA ou ANALISE)
    SELECT COUNT(*) INTO v_pendencias
    FROM solicitacoes
    WHERE id_aluno = p_id_aluno
      AND status IN ('ABERTA', 'ANALISE');
    
    -- REGRA DE NEGÓCIO: Acesso permitido apenas para alunos ATIVOS sem solicitações pendentes
    v_acesso_liberado := (v_aluno.status = 'ATIVO' AND v_pendencias = 0);
    
    -- Define mensagem
    IF v_acesso_liberado THEN
        v_mensagem := 'Acesso LIBERADO';
    ELSE
        IF v_aluno.status != 'ATIVO' THEN
            v_mensagem := 'Acesso BLOQUEADO: Aluno ' || v_aluno.status;
        ELSE
            v_mensagem := 'Acesso BLOQUEADO: Possui ' || v_pendencias || ' solicitação(ões) pendente(s)';
        END IF;
    END IF;
    
    -- Retorna resultado (usando RAISE NOTICE para simular retorno em procedure)
    RAISE NOTICE '========================================';
    RAISE NOTICE 'CONSULTA DE ACESSO - IESB';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Matrícula: %', v_aluno.matricula;
    RAISE NOTICE 'Nome: %', v_aluno.nome;
    RAISE NOTICE 'Curso: %', v_aluno.curso;
    RAISE NOTICE 'Campus: %', v_aluno.campus;
    RAISE NOTICE 'Status: %', v_aluno.status;
    RAISE NOTICE 'Pendências: %', v_pendencias;
    RAISE NOTICE '----------------------------------------';
    RAISE NOTICE 'RESULTADO: %', v_mensagem;
    RAISE NOTICE '========================================';
    
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Erro na consulta de acesso: %', SQLERRM;
END;
$$;

COMMENT ON PROCEDURE sp_consulta_acesso_aluno IS 'Consulta se aluno tem acesso liberado baseado em regras de negócio';

-- =====================================================
-- 6. FUNCTIONS
-- =====================================================

-- Function: fn_dias_em_aberto
-- Retorna quantos dias uma solicitação está em aberto
CREATE OR REPLACE FUNCTION fn_dias_em_aberto(p_id_solicitacao INTEGER)
RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_data_abertura TIMESTAMP;
    v_status status_solicitacao;
    v_dias INTEGER;
BEGIN
    -- Validação
    IF p_id_solicitacao IS NULL THEN
        RAISE EXCEPTION 'ID da solicitação é obrigatório';
    END IF;
    
    -- Busca dados da solicitação
    SELECT data_abertura, status 
    INTO v_data_abertura, v_status
    FROM solicitacoes
    WHERE id = p_id_solicitacao;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Solicitação com ID % não encontrada', p_id_solicitacao;
    END IF;
    
    -- Se estiver finalizada, retorna 0 (não está mais em aberto)
    IF v_status = 'FINALIZADA' THEN
        RETURN 0;
    END IF;
    
    -- Calcula dias entre data de abertura e agora
    v_dias := EXTRACT(DAY FROM (CURRENT_TIMESTAMP - v_data_aberture));
    
    -- Se for menos de 1 dia, retorna 0
    IF v_dias IS NULL OR v_dias < 0 THEN
        v_dias := 0;
    END IF;
    
    RETURN v_dias;
    
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Erro ao calcular dias em aberto: %', SQLERRM;
END;
$$;

COMMENT ON FUNCTION fn_dias_em_aberto IS 'Retorna quantos dias uma solicitação está em aberto (0 se finalizada)';

-- =====================================================
-- 7. VIEWS
-- =====================================================

-- View: vw_status_academico
-- Visão consolidada do status acadêmico de todos os alunos
CREATE OR REPLACE VIEW vw_status_academico AS
SELECT 
    a.matricula AS "Matrícula",
    a.nome AS "Nome",
    a.curso AS "Curso",
    a.campus AS "Campus",
    a.status AS "Status Aluno",
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM solicitacoes s 
            WHERE s.id_aluno = a.id 
              AND s.status IN ('ABERTA', 'ANALISE')
        ) THEN 'SIM'
        ELSE 'NÃO'
    END AS "Possui Pendência",
    CASE 
        WHEN a.status = 'ATIVO' 
             AND NOT EXISTS (
                 SELECT 1 FROM solicitacoes s 
                 WHERE s.id_aluno = a.id 
                   AND s.status IN ('ABERTA', 'ANALISE')
             ) THEN 'SIM'
        ELSE 'NÃO'
    END AS "Acesso Liberado"
FROM alunos a
ORDER BY a.nome;

COMMENT ON VIEW vw_status_academico IS 'Visão consolidada do status acadêmico e permissão de acesso de todos os alunos';

-- =====================================================
-- 8. DADOS DE TESTE (OPCIONAL - PARA DESENVOLVIMENTO)
-- =====================================================

-- Insere alunos de teste
INSERT INTO alunos (matricula, nome, cpf, curso, campus, status) VALUES
('20250001', 'Ana Silva Santos', '123.456.789-00', 'Engenharia de Software', 'Brasília', 'ATIVO'),
('20250002', 'Bruno Oliveira Costa', '234.567.890-11', 'Administração', 'Brasília', 'ATIVO'),
('20250003', 'Carolina Mendes Lima', '345.678.901-22', 'Direito', 'Taguatinga', 'ATIVO'),
('20250004', 'Daniel Pereira Souza', '456.789.012-33', 'Psicologia', 'Brasília', 'INATIVO'),
('20250005', 'Elisa Ferreira Alves', '567.890.123-44', 'Engenharia Civil', 'Taguatinga', 'ATIVO');

-- Insere solicitações de teste
INSERT INTO solicitacoes (id_aluno, tipo, descricao, status, data_abertura) VALUES
(1, 'DECLARACAO', 'Solicito declaração de matrícula para estágio', 'ABERTA', CURRENT_TIMESTAMP - INTERVAL '5 days'),
(2, 'TRANCAMENTO', 'Trancamento por motivos de saúde', 'ANALISE', CURRENT_TIMESTAMP - INTERVAL '10 days'),
(3, 'MATRICULA', 'Rematrícula em disciplina', 'FINALIZADA', CURRENT_TIMESTAMP - INTERVAL '15 days');

-- Atualiza data de fechamento da solicitação finalizada
UPDATE solicitacoes SET data_fechamento = CURRENT_TIMESTAMP - INTERVAL '2 days' WHERE status = 'FINALIZADA';

-- Insere logs de teste
INSERT INTO log_solicitacoes (id_solicitacao, status_anterior, novo_status, data_alteracao) VALUES
(3, 'ABERTA', 'ANALISE', CURRENT_TIMESTAMP - INTERVAL '5 days'),
(3, 'ANALISE', 'FINALIZADA', CURRENT_TIMESTAMP - INTERVAL '2 days');

-- =====================================================
-- 9. PERMISSÕES (AJUSTAR CONFORME AMBIENTE)
-- =====================================================

-- Concede permissões (descomentar e ajustar conforme necessário)
-- GRANT SELECT, INSERT, UPDATE ON alunos TO usuario_aplicacao;
-- GRANT SELECT, INSERT, UPDATE ON solicitacoes TO usuario_aplicacao;
-- GRANT SELECT, INSERT ON log_solicitacoes TO usuario_aplicacao;
-- GRANT USAGE, SELECT ON SEQUENCE alunos_id_seq TO usuario_aplicacao;
-- GRANT USAGE, SELECT ON SEQUENCE solicitacoes_id_seq TO usuario_aplicacao;
-- GRANT USAGE, SELECT ON SEQUENCE log_solicitacoes_id_seq TO usuario_aplicacao;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
