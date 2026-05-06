-- ============================================================
-- MIGRAÇÕES ASSINADOR BAMRJ — Executar na ordem apresentada
-- Dados existentes NÃO são apagados (ALTER TABLE / UPDATE)
-- ============================================================

-- 1. Adiciona coluna substituto_ativo na tabela users (persistência do modo substituto)
ALTER TABLE users ADD COLUMN IF NOT EXISTS substituto_ativo BOOLEAN DEFAULT FALSE;

-- 2. Garante que a coluna must_change_password existe
ALTER TABLE users ADD COLUMN IF NOT EXISTS must_change_password BOOLEAN DEFAULT TRUE;

-- ============================================================
-- FIM DAS MIGRAÇÕES ASSINADOR
-- Para reverter: ALTER TABLE users DROP COLUMN substituto_ativo;
-- ============================================================