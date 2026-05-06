# Assinador BAMRJ — Sistema de Tramitação de Empenhos

> Base de Abastecimento da Marinha no Rio de Janeiro

## 🏗️ Arquitetura

- **Backend:** PHP 8.2+ (Puro, sem framework)
- **Banco de Dados:** PostgreSQL (Railway / Local)
- **Frontend:** HTML5 + CSS3 + JavaScript Vanilla
- **Deploy:** Railway (Dockerfile / Procfile)

## 📋 Funcionalidades

### Módulos Principais
- **Dashboard** — Visão por perfil com radar de inbox
- **Upload** — Novos processos e acervo legado
- **Visualizador** — Leitura de PDFs com aprovação/rejeição inline
- **Arquivo** — Consulta pública e histórico
- **Administração** — CRUD de utilizadores e migrações web
- **SSO** — Login único entre Assinador e SIGEF

### 🆕 Melhorias Implementadas (Missão HEAVY MAX)

| # | Feature | Descrição |
|---|---------|-----------|
| 1 | **Substituto Persistente** | Estado gravado no BD (coluna `substituto_ativo`). Sobrevive a reinicializações de navegador/PC. Banner visual permanente no topo quando ativo. |
| 2 | **Sem Restrições de Senha** | Removidos limites mínimos/máximos e regras de complexidade. O utilizador escolhe livremente. |
| 3 | **Trocar Senha** | Nova rota `/trocar_senha` com botão no menu do utilizador logado. |
| 4 | **Design Unificado** | CSS e header padronizados entre SIGEF e Assinador (mesma paleta, botões, badges). |

## 🗄️ Migrações de Banco de Dados

**IMPORTANTE:** Execute o ficheiro `migracoes.sql` no seu servidor PostgreSQL ANTES de fazer deploy do novo código:

```bash
psql -h SEU_HOST -U SEU_USER -d assinador_bamrj -f migracoes.sql
```

As migrações usam `ADD COLUMN IF NOT EXISTS` e são seguras para executar múltiplas vezes.

### Migrações Incluídas
- `substituto_ativo BOOLEAN` na tabela `users`

## 🚀 Deploy

1. Configure a variável `DATABASE_URL` no Railway
2. Execute as migrações SQL
3. Push para o repositório — o Railway faz o resto

## 🔐 Segurança

- Senhas com `password_hash()` (bcrypt)
- Prepared statements em todas as queries (anti-SQL injection)
- Controle de acesso por perfil (role-based)
- SSO com token HMAC-SHA256 entre sistemas