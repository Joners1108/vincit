# Vincit — E-commerce de Roupa

Loja online em PHP com MySQL: catálogo de produtos, carrinho de compras, checkout (Stripe ou modo simulado), registo e login de utilizadores, área de conta, painel de administração, favoritos e envio de email. Arquitetura monolítica sem frameworks, utilizando PDO para acesso à base de dados.

## Especificações técnicas

| Item | Valor |
|------|-------|
| Linguagem | PHP 8.x |
| Base de dados | MySQL / MariaDB (PDO) |
| Servidor web | Apache com mod_rewrite |
| Framework | Nenhum (PHP puro) |
| Pagamentos | Stripe (Checkout sessões hospedadas) ou modo simulado local |
| Email | Resend → SMTP (PHPMailer) → PHP `mail()` (fallback) |
| Sessões | PHP nativas com cookie `HttpOnly`, `SameSite=Lax` |
| Proteção CSRF | Token por sessão (`bin2hex(random_bytes(32))`) |
| Imagens | Sistema de ficheiros (`site/assets/img/products/`, `categories/`) |
| Moeda | EUR (€) |

## Estrutura do repositório

```
vincit/
├── index.php                  # Redirecionamento opcional para site/
├── ARRANCAR.bat / iniciar_pap.bat # Scripts de arranque automático (Laragon)
├── guardar_bd.bat             # Exportação da base de dados para backups/
├── .env.example               # Modelo de variáveis de ambiente
├── .gitignore                 # Ignora .env, vendor, backups, uploads, etc.
├── plan.md / CHANGELOG.md
├── backups/                   # Dumps SQL da base de dados (não versionado)
├── site/
│   ├── .env                   # Criar a partir de .env.example (não versionado)
│   ├── .htaccess              # Regras de rewriting de URLs
│   ├── config.php             # Constantes globais, env loader, Stripe, sessões
│   ├── database.php           # Ligação PDO e utilitários de BD
│   ├── database.sql           # Referência do esquema
│   ├── install.php            # Assistente de instalação inicial
│   ├── install_process.php
│   ├── update_database.php    # Migração de esquema via CLI
│   ├── seed_demo_products.php # População de produtos de exemplo
│   ├── sitemap.xml / robots.txt
│   ├── admin/                 # Painel de administração (produtos, encomendas, utilizadores, definições)
│   ├── api/                   # Endpoints (carrinho, Stripe webhook, sugestão de moradas, etc.)
│   ├── assets/
│   │   ├── css/               # style.css, admin.css, retro-theme.css
│   │   ├── js/                # main.js, admin.js, checkout-address.js
│   │   └── img/
│   │       ├── products/      # Imagens de produtos (gitkeep)
│   │       ├── categories/    # Imagens de categorias (gitkeep)
│   │       └── fallback/      # Placeholder SVG
│   ├── includes/
│   │   ├── env_loader.php     # Carregamento de .env sem Composer
│   │   ├── mailer.php         # Envio de email (Resend / SMTP / mail())
│   │   ├── header.php / footer.php
│   │   ├── schema_migrate.php # Migrações automáticas de esquema
│   │   ├── product_sizes_lib.php
│   │   ├── stripe_checkout.php
│   │   └── ...
│   └── pages/                 # Páginas públicas (loja, produto, conta, checkout, login, registo, etc.)
```

## Variáveis de ambiente

Copie `site/.env.example` para `site/.env` e ajuste. O ficheiro `.env` não é versionado.

| Variável | Descrição | Obrigatório |
|----------|-----------|-------------|
| `SITE_NAME` | Nome da loja | Não |
| `SITE_URL` | URL pública do site | Não (auto-deteta) |
| `SITE_EMAIL` | Email de contacto | Não |
| `DB_HOST` | Host MySQL | Sim |
| `DB_NAME` | Nome da base de dados | Sim |
| `DB_USER` | Utilizador MySQL | Sim |
| `DB_PASS` | Password MySQL | Sim |
| `APP_DEBUG` | Modo depuração (`true`/`false`) | Não |
| `STRIPE_PUBLIC_KEY` | Chave pública Stripe | Para pagamentos reais |
| `STRIPE_SECRET_KEY` | Chave secreta Stripe | Para pagamentos reais |
| `STRIPE_WEBHOOK_SECRET` | Segredo do webhook Stripe | Para webhooks |
| `ALLOW_SIMULATED_CHECKOUT` | Checkout simulado local (apenas dev) | Não |
| `MAIL_HOST` / `MAIL_USER` / `MAIL_PASS` | Configuração SMTP | Para email via SMTP |
| `RESEND_API_KEY` | Chave API Resend (prioritária sobre SMTP) | Para email via Resend |

## Instalação

### Pré-requisitos

- PHP 8.x com extensões: `pdo_mysql`, `mbstring`, `curl`, `openssl`
- MySQL / MariaDB em execução
- Apache (recomendado Laragon no Windows)

### Passos

1. Copie a pasta `vincit` para `C:\laragon\www\vincit`.
2. Crie a base de dados vazia (ex.: `vincit_db`) no MySQL.
3. Copie `site/.env.example` para `site/.env` e preencha `DB_*` e outras variáveis.
4. Garanta permissões de escrita em:
   - `site/assets/img/products/`
   - `site/assets/img/categories/`
   - `site/logs/` (se existir)
5. Inicie Apache e MySQL.
6. Aceda a `http://localhost/vincit/site/install.php` e siga o assistente de instalação.
7. Após a instalação, remova ou bloqueie o acesso a `install.php` e `install_process.php`.

### Arranque rápido (PAP / apresentação)

Em Windows com Laragon, duplo-clique em `iniciar_pap.bat`. O script inicia MySQL e Apache, importa a base de dados se necessário, abre o site no navegador e opcionalmente ativa um túnel ngrok.

## Comandos úteis

```bash
# Atualizar esquema da base de dados (idempotente)
php site/update_database.php

# Inserir produtos de demonstração
php site/seed_demo_products.php
```

## Notas de segurança (repositório público)

- O ficheiro `site/.env` **não é versionado**; utilize `site/.env.example` como modelo.
- Nunca comite passwords, chaves Stripe ou credenciais SMTP reais.
- A pasta `backups/` contém dumps da base de dados e está no `.gitignore`; não a versione.
- Em produção defina `APP_DEBUG=false` e `ALLOW_SIMULATED_CHECKOUT=false` (ou remova a variável).
- Utilize HTTPS em produção e configure o webhook Stripe para eventos de pagamento.

## Licença

© 2026 João Pereira — https://github.com/Joners1108

Este projeto está disponível para uso pessoal e académico. Para uso comercial ou redistribuição, contacte o autor através do GitHub.

O código é fornecido "tal como está", sem garantias de qualquer tipo.
