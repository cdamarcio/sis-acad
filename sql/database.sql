-- Criar extensão para UUID se quiser IDs mais seguros (opcional)
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS alunos (
    id SERIAL PRIMARY KEY,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    email VARCHAR(100),
    status VARCHAR(10) DEFAULT 'ATIVO', -- ATIVO, INATIVO, TRANCADO
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registros_acesso (
    id SERIAL PRIMARY KEY,
    aluno_id INTEGER REFERENCES alunos(id) ON DELETE CASCADE,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ponto_acesso VARCHAR(50), -- Ex: 'Portaria Principal', 'Biblioteca'
    autorizado BOOLEAN DEFAULT TRUE
);

-- Índices para busca rápida por matrícula (essencial para o IESB)
CREATE INDEX idx_alunos_matricula ON alunos(matricula);