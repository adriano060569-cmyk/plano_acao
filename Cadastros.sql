-- 1. Tabela de Usuários (os oito setores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, -- Use hash para senhas reais!
    setor VARCHAR(100) NOT NULL
);

-- 2. Tabela de Objetivos Estratégicos (Exemplo de estrutura)
CREATE TABLE objetivos_estrategicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT
);

-- 3. Tabela Principal de Planos de Ação
CREATE TABLE planos_acao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    objetivo_id INT NOT NULL,
    meta_descricao TEXT NOT NULL,
    acao_descricao TEXT NOT NULL,
    responsavel_id INT NOT NULL, -- Vincula ao setor responsável (usuario)
    status ENUM('concluida', 'em_andamento', 'reprogramada') NOT NULL DEFAULT 'em_andamento',
    percentual_execucao INT DEFAULT 0, -- 0 a 100
    justificativa_reprogramacao TEXT,
    data_reprogramacao DATE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios (id),
    FOREIGN KEY (objetivo_id) REFERENCES objetivos_estrategicos(id)
);

-- Inserções de Exemplo (para teste)
INSERT INTO usuarios (login, senha, setor) VALUES
('setor_rh', '$2y$10$...hash_da_senha...', 'Recursos Humanos'), -- Substitua pelo hash bcrypt da senha '12345'
('setor_ti', '$2y$10$...hash_da_senha...', 'Tecnologia da Informação');

INSERT INTO objetivos_estrategicos (titulo) VALUES
('Otimizar Processos Internos'),
('Melhorar a Comunicação com o Cliente');
