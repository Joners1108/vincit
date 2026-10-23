# Vincit — Loja Online de Roupa

Loja em PHP com MySQL: catálogo de produtos, carrinho, checkout (Stripe ou modo simulado), registo/login, área de utilizador, painel de administração, favoritos e notificações por email.

## O que faz

- **Loja:** catálogo, páginas de produto, carrinho de compras, checkout
- **Conta:** registo, login, perfil, histórico de encomendas, moradas
- **Pagamentos:** Stripe (chaves no `.env`) ou checkout simulado para testes locais
- **Admin:** gestão de produtos, categorias, encomendas e utilizadores (apenas role admin)
- **Emails:** Resend → SMTP → PHP `mail()` (configurável no `.env`)
- **Imagens:** guardadas em `site/assets/img/products/` e `categories/`

## Tecnologias

- PHP 8.x (PDO, sem frameworks)
- MySQL / MariaDB
- Apache com mod_rewrite
- Stripe (opcional, para pagamentos reais)

## Pré-requisitos

- PHP 8.x com extensões: `pdo_mysql`, `mbstring`, `curl`, `openssl`
- MySQL / MariaDB a correr
- Apache (recomendado Laragon no Windows)

## Como instalar

1. Cola a pasta `vincit` em `C:\laragon\www\vincit`
2. Copia `site/.env.example` para `site/.env` e edita os dados (BD, emails, Stripe)
3. Cria a base de dados vazia (ex.: `vincit_db`) no MySQL
4. Inicia Apache e MySQL (Laragon "Start All")
5. Abre no browser: `http://localhost/vincit/site/install.php` e segue o assistente
6. Após a instalação, remove ou bloqueia o acesso a `install.php`

### Arranque rápido (PAP / apresentação)

Duplo-clique em `iniciar_pap.bat` — arranca MySQL + Apache, importa a BD se necessário, abre o site e ativa ngrok (túnel público).

## Segurança (repositório público)

- O ficheiro `site/.env` **não é versionado** (usa o `.env.example` como modelo)
- Nunca comites passwords ou chaves Stripe reais
- Em produção: `APP_DEBUG=false`, `ALLOW_SIMULATED_CHECKOUT=false`, HTTPS
- A pasta `backups/` não é versionada (contém dumps da base de dados)

## Estrutura resumida

```
vincit/
  site/
    .env.example          → modelo de variáveis de ambiente
    admin/                → painel de administração
    api/                  → endpoints (carrinho, Stripe webhook, etc.)
    assets/               → CSS, JS, imagens
    includes/             → mailer, templates, helpers
    pages/                → páginas públicas (loja, conta, checkout...)
    config.php, database.php
    install.php           → instalação inicial da BD
    update_database.php   → atualizar esquema da BD
```

## Licença

Projeto académico / pessoal.
