# Vincit — notas de instalação

Loja em PHP com MySQL: catálogo, carrinho, checkout (Stripe ou modo de teste), contas e backoffice.

Evita commitar o `site/.env` com passwords — mantém só o `.env.example` no repositório. Quem recebe o projeto copia `site/.env.example` para `site/.env` e edita. Primeira instalação: `site/install.php` no browser. Se o instalador disser que a BD já existe e quiseres voltar ao assistente, apaga `site/installed.lock` (vê bem o que isso faz aos dados).

## Resumo do código

É PHP com PDO, sem Laravel nem nada do género. Páginas em `site/pages/`, includes em `site/includes/`, estáticos em `site/assets/`. O `site/.htaccess` trata das URLs tipo `/loja` e `/produto/...` no Apache (mod_rewrite).

O `config.php` lê o `.env`. O `database.php` abre a MySQL e às vezes ajusta tabelas para bater com o código.

**Frente da loja:** loja e produto, carrinho (há também `site/api/cart.php`), favoritos, registo/login. O checkout só com sessão iniciada. Moradas: pode haver autocomplete via `api/address_suggest.php` se estiver ativo no `.env`. Pagamentos com Stripe (chaves no `.env`, webhook em `api/stripe_webhook.php`). Para testar sem cartão dá `ALLOW_SIMULATED_CHECKOUT=true` no `.env` — só em máquina tua, não deixes assim no site real.

**Admin:** `site/admin` — produtos, categorias, encomendas, users, definições. Só quem tem role admin na tabela `users`.

**Imagens:** `site/assets/img/products/` e `categories/`; na BD guarda-se só o nome do ficheiro.

À mão, na pasta do projeto (MySQL ligado): `php site/update_database.php` atualiza o esquema (podes correr várias vezes); `php site/seed_demo_products.php` mete produtos de exemplo; `site/tools/migrate_uploads_to_assets_img.php` se ainda tiveres coisas antigas em `uploads/`.

Emails: ordem Resend → SMTP → `mail()`, está na secção 7.

---

## 1. O que precisas no computador

| Componente | Notas |
|------------|--------|
| **PHP 8.x** | Com extensões `pdo_mysql`, `mbstring`, `curl`, `openssl` (o Laragon inclui por defeito). |
| **MySQL / MariaDB** | Servidor a correr antes de instalar ou atualizar a base de dados. |
| **Servidor web** | Apache (recomendado no **Laragon**) ou equivalente, com o documento raiz a apontar para a pasta onde está o projeto. |

**Laragon (Windows):** instala [Laragon](https://laragon.org/), coloca a pasta `vincit` em `C:\laragon\www\vincit`, inicia **Start All** e usa o PHP do Laragon na linha de comandos quando indicado abaixo.

---

## 2. Preparação geral (antes do primeiro acesso)

1. **Copiar o projeto** para `www` (ex.: `C:\laragon\www\vincit`).
2. **Criar a base de dados vazia** no MySQL (ex.: `vincit_db`) — ou deixa o instalador criar se usares o fluxo completo; o `install.php` pode criar a BD conforme configuração.
3. **Criar o ficheiro de ambiente:** copia `site/.env.example` para `site/.env` e edita os valores (secção 4).
4. **Garantir permissões de escrita** nas pastas onde são gravadas imagens:
   - `site/assets/img/products/`
   - `site/assets/img/categories/`

---

## 3. Arranque automático (recomendado — para apresentação/PAP)

Há dois ficheiros `.bat` na raiz do projeto que tratam de tudo. **Funcionam em qualquer PC** com Laragon instalado (detetam sozinhos os caminhos, a versão do MySQL/Apache e o config).

### `iniciar_pap.bat` — arranca tudo com duplo-clique

Faz, por esta ordem:

1. Arranca **MySQL** e **Apache** diretamente (sem abrir a interface do Laragon).
2. Espera o MySQL ficar pronto (até 60s).
3. **Base de dados:**
   - Se a BD `vincit_db` **já existir com dados** → **não toca** (preserva o que editaste no admin).
   - Se estiver **vazia** (PC novo) → importa `backups/vincit_db.sql` + `backups/extras_pap.sql`.
4. Abre o site no browser (`http://localhost/vincit/site`).
5. Arranca o **ngrok** (túnel público `https://...`) numa janela à parte — o token já vai embutido, ativo em qualquer PC.

Se algo falhar, a janela **não fecha**: mostra o erro e grava-o em `_arranque_log.txt`.

### `guardar_bd.bat` — congela as tuas edições

Depois de editares no admin (produtos, **localização da loja**, etc.), corre este `.bat`. Ele grava o estado atual da BD por cima de `backups/vincit_db.sql` (com cópia de segurança `.bak`). Assim as edições **viajam** para qualquer PC no próximo `iniciar_pap.bat`.

### Fluxo para levar para outro PC

1. Copia a pasta `vincit` inteira para `C:\laragon\www\` (mantém o nome `vincit`).
2. Duplo-clique em `iniciar_pap.bat`.

> **Requisitos noutro PC:** Laragon instalado e MySQL `root` **sem password** (padrão do Laragon). Se o ngrok não existir nesse Laragon, o passo é ignorado sem erro.

> **Ficheiros que têm de viajar juntos:** `iniciar_pap.bat`, `guardar_bd.bat`, `backups/vincit_db.sql` e `backups/extras_pap.sql`.

**Nota:** o `extras_pap.sql` garante sempre o admin de demonstração, os produtos com tamanhos e a loja no **Porto**, mesmo num PC do zero.

---

## 3b. Como iniciar o site (manual)

### Com Laragon

1. Abre o **Laragon** e clica em **Start All** (Apache + MySQL).
2. No browser, abre o endereço do site. Exemplo típico com projeto em `www\vincit`:
   - **`http://localhost/vincit/site/`**  
   Se usares um virtual host, ajusta `SITE_URL` no `.env` para esse URL (com `https` em produção).

### Raiz do projeto

O ficheiro `index.php` na pasta `vincit/` pode redirecionar para `site/` — útil se o servidor apontar para a raiz `vincit`.

---

## 4. Ficheiro `site/.env` — o que configurar

Copia `site/.env.example` → `site/.env` e define no mínimo:

| Variável | Descrição |
|----------|-----------|
| `SITE_NAME` | Nome da loja. |
| `SITE_URL` | URL pública (ex.: `http://localhost/vincit/site`). Podes deixar **vazio** para deteção automática em muitos casos. |
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Ligação MySQL. |
| `APP_DEBUG` ou `DEBUG` | `true` em desenvolvimento; **`false` em produção**. |
| `SITE_EMAIL` | Email de contacto (rodapé, apoio, remetente por defeito). |
| `ADMIN_NOTIFY_EMAIL` | Onde chegam alertas de **encomenda paga** (pode ser o mesmo que `SITE_EMAIL`). |

**Stripe (opcional):** `STRIPE_PUBLIC_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET` (webhook em produção).

**Só para testes locais sem pagamento real:** `ALLOW_SIMULATED_CHECKOUT=true` marca a encomenda como paga sem ir ao Stripe. **Nunca uses isto num site público.**

---

## 5. Instalação da base de dados (primeira vez)

1. Com Apache e MySQL ligados, abre no browser:  
   **`http://localhost/vincit/site/install.php`**  
   (ajusta o caminho ao teu URL.)
2. Segue o assistente. Isto cria tabelas, utilizador admin inicial e pode popular categorias e produtos de demonstração conforme o fluxo.

### Depois da instalação — atualizar o esquema

Quando mudas o código e a BD precisa de colunas/tabelas novas, corre **uma vez** na pasta do projeto (com MySQL a correr):

```bash
php site/update_database.php
```

Dá para correr outra vez sem problema se já estiver atualizado.

### Catálogo de demonstração (opcional)

Para inserir produtos fictícios e descarregar imagens para `assets/img/products/` (requer internet na primeira execução):

```bash
php site/seed_demo_products.php
```

Slugs que já existem são ignorados.

### Imagens antigas na pasta `uploads/` (legado)

Se ainda tiveres ficheiros em `site/uploads/` e quiseres movê-los para `assets/img/products/` ou `categories/` conforme a base de dados:

```bash
php site/tools/migrate_uploads_to_assets_img.php
```

---

## 6. Passwords e credenciais iniciais

Após a instalação típica:

| Função | Email | Password inicial |
|--------|--------|------------------|
| **Administrador** | `admin@vincit.pt` | `admin` |

**Obrigatório:** na primeira entrada no admin, altera a password (gestão de utilizadores / perfil, conforme o painel).

**Clientes** criam conta em **Registo**; as passwords são guardadas com hash seguro no servidor. O fluxo **Esqueci a password** envia email com link de redefinição (se o email estiver bem configurado no `.env`).

---

## 7. Emails — como fazer funcionar

O envio segue esta **ordem**:

1. **Resend** — se `RESEND_API_KEY` estiver definida, usa a API (tem prioridade sobre SMTP).
2. **SMTP (PHPMailer)** — se `MAIL_HOST` e `MAIL_USER` estiverem definidos (ex.: Gmail).
3. **`mail()` do PHP** — último recurso (no Windows costuma ser pouco fiável).

### Gmail (recomendado para testes e pequenas lojas)

1. Ativa a **verificação em dois passos** na conta Google.
2. Cria uma **palavra-passe de aplicação** (Google Account → Segurança).
3. No `.env`:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USER=a-tua-loja@gmail.com
MAIL_PASS=a-palavra-passe-de-aplicacao
MAIL_FROM=a-tua-loja@gmail.com
MAIL_FROM_NAME=Vincit
SITE_EMAIL=a-tua-loja@gmail.com
ADMIN_NOTIFY_EMAIL=a-tua-loja@gmail.com
```

**Importante:** se `RESEND_API_KEY` estiver preenchida, o sistema usa Resend e **não** o Gmail. Para forçar só Gmail, deixa `RESEND_API_KEY` vazia ou comenta a linha.

### Emails que o sistema envia

- Boas-vindas no registo  
- Redefinição de password  
- Formulário de apoio → email da loja  
- Confirmação / pagamento (consoante o fluxo de checkout)  
- Alerta ao admin quando uma encomenda fica **paga**  
- Cliente notificado quando o **estado da encomenda** muda no admin  

---

## 8. Onde ficam as imagens

- **Produtos:** `site/assets/img/products/` (até 3 fotos por produto na interface).
- **Categorias:** `site/assets/img/categories/`.
- **Placeholder:** `site/assets/img/fallback/product-placeholder.svg`.

A base de dados guarda **apenas o nome do ficheiro**, não o caminho completo.

---

## 9. Segurança antes de pôr “no ar”

- `APP_DEBUG=false` e `ALLOW_SIMULATED_CHECKOUT=false` (ou removida).
- **Não commits** o ficheiro `site/.env` com passwords reais (usa `.env.example` só como modelo).
- Após o setup definitivo: **remove ou bloqueia** o acesso a `install.php` e `install_process.php`.
- Produção: **HTTPS**, chaves Stripe **live** e webhook configurado se usares pagamentos reais.

---

## 10. Rotas úteis para testar

- Loja: `/site/`, `/site/loja`
- Conta: `/site/login`, `/site/registo`, `/site/conta`
- Admin: `/site/admin`

---

## 11. Resolução rápida de problemas

| Problema | O que verificar |
|----------|-----------------|
| Página em branco / erro PHP | `APP_DEBUG=true` temporariamente; logs em `site/logs/` se existirem. |
| Erro de ligação à BD | `DB_*` no `.env`, MySQL a correr, base criada. |
| Emails não chegam | Gmail: palavra-passe de aplicação; pasta spam; `RESEND_API_KEY` não estar a sobrepor SMTP sem querer. |
| Imagens partidas | Ficheiros em `assets/img/products/` ou `categories/`; nomes iguais aos da BD; migração a partir de `uploads/` se for legado. |
| Checkout diz que pagamento está indisponível | `STRIPE_SECRET_KEY` válida **ou** `ALLOW_SIMULATED_CHECKOUT=true` só em ambiente de teste local. |
| “Base de dados já instalada” mas queres reinstalar | Apaga `site/installed.lock` e volta a `install.php` (atenção: pode sobrescrever dados se o script o fizer). |

---

## 12. Estrutura resumida do projeto

```text
vincit/
  index.php                 → entrada opcional / redirecionamento
  README.md                 → este guia
  site/
    .env                    → criar a partir de .env.example (não versionar segredos)
    admin/                  → painel de gestão
    api/                    → endpoints (carrinho, Stripe webhook, etc.)
    assets/                 → CSS, JS, img (products, categories, fallback)
    includes/               → mailer, templates de email, helpers
    pages/                  → páginas públicas
    tools/                  → migrate_uploads_to_assets_img.php
    config.php, database.php
    database.sql            → referência / cópia do esquema (a instalação principal é pelo browser)
    install.php             → instalação inicial da BD
    update_database.php     → atualizar esquema da BD pela linha de comandos
    seed_demo_products.php  → produtos demo (CLI)
```

---

*Dúvidas: `.env`, instalação da BD (secção 5) e emails (secção 7).*
