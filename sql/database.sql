-- Procedure para Criar Aluno com Validação
CREATE OR REPLACE PROCEDURE sp_criar_aluno(
    p_nome VARCHAR,
    p_matricula VARCHAR,
    p_cpf VARCHAR,
    p_email VARCHAR
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Inserção simples; o PostgreSQL cuidará das restrições UNIQUE de CPF e Matrícula
    INSERT INTO alunos (nome, matricula, cpf, email, status, data_cadastro)
    VALUES (p_nome, p_matricula, p_cpf, p_email, 'ATIVO', CURRENT_TIMESTAMP);

EXCEPTION
    WHEN unique_violation THEN
        RAISE EXCEPTION 'Erro: Matrícula ou CPF já cadastrado no sistema do IESB.';
    WHEN others THEN
        RAISE EXCEPTION 'Erro inesperado ao cadastrar aluno: %', SQLERRM;
END;
$$;

-- Tabela de Solicitações (Caso ainda não tenha criado)
CREATE TABLE IF NOT EXISTS solicitacoes (
    id SERIAL PRIMARY KEY,
    aluno_id INTEGER REFERENCES alunos(id),
    tipo_solicitacao VARCHAR(50) NOT NULL,
    descricao TEXT,
    status VARCHAR(20) DEFAULT 'ABERTO', -- ABERTO, FINALIZADO, CANCELADO
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);