# VoltCore CRM — Nova Plataforma

Sistema CRM moderno para gerenciamento de ordens de serviço, execuções, vendas e financeiro.
Migração do sistema legado (CodeIgniter 2 + Bootstrap 2) para **Laravel 11 + Tailwind CSS + Alpine.js**.

---

## Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.3 + Laravel 11 |
| Frontend | Tailwind CSS 3 + Alpine.js 3 |
| Banco | MySQL 8 |
| Cache/Filas | Redis |
| Web Server | Nginx |
| Build | Vite |
| Gráficos | Chart.js 4 |

---

## Módulos

- **Dashboard** — KPIs, gráficos de receita/despesa, OS recentes, vencimentos
- **Clientes** — Cadastro PF/PJ, histórico, portal do cliente
- **Ordens de Serviço** — CRUD completo, PDF, status, garantia
- **Execuções** — Checklist de instalação, engenheiro, datas, fotos
- **Vendas** — Produtos + serviços, descontos, condições de pagamento, PDF
- **Financeiro** — Lançamentos, cobranças (Asaas), relatório DRE
- **Visitas Técnicas** — Agenda de visitas, relatório
- **Produtos / Serviços** — Catálogo com estoque e margens
- **Relatórios** — OS, financeiro, clientes, execuções
- **Agenda** — Calendário com OS, visitas, execuções e vencimentos (FullCalendar)
- **Usuários / Permissões** — Roles e permissões via spatie/laravel-permission

---

## Instalação — Passo 1: Preparar o servidor

```bash
# Baixar e executar o script de setup (Debian 13)
curl -O https://raw.githubusercontent.com/carlospjjj-oss/Voltcore-new/main/setup.sh
sudo bash setup.sh
```

O script instala e configura:
- PHP 8.3-FPM + extensões necessárias
- Composer
- MySQL 8 (banco + usuário criados)
- Redis
- Nginx (vhost configurado)
- Node.js 20 + npm
- Supervisor (worker de filas)
- Laravel 11 (projeto criado em `/var/www/voltcore`)

As credenciais do banco são salvas em `/root/voltcore_credentials.txt`.

---

## Instalação — Passo 2: Deploy da aplicação

```bash
# No servidor, ir ao diretório do app
cd /var/www/voltcore

# Clonar este repositório
git clone https://github.com/carlospjjj-oss/Voltcore-new.git .

# Copiar e editar o .env
cp .env.example .env
nano .env
# → Preencher DB_PASSWORD com a senha do /root/voltcore_credentials.txt

# Instalar dependências
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Configurar Laravel
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Permissões
chown -R www-data:www-data /var/www/voltcore
chmod -R 775 storage bootstrap/cache

# Cache de config (produção)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Desenvolvimento local

```bash
# Clonar
git clone https://github.com/carlospjjj-oss/Voltcore-new.git voltcore-new
cd voltcore-new

# Instalar dependências
composer install
npm install

# Configurar .env
cp .env.example .env
php artisan key:generate
# → editar .env com suas credenciais locais

# Migrations
php artisan migrate --seed

# Iniciar servidores (em terminais separados)
php artisan serve     # Backend :8000
npm run dev           # Vite HMR
```

---

## Estrutura de Diretórios

```
app/
  Http/
    Controllers/     ← Controllers por módulo
    Requests/        ← Form Requests (validação)
    Middleware/
  Models/            ← Eloquent models
  Services/          ← Lógica de negócio (CepService, AsaasService...)
  Policies/          ← Autorização por recurso

database/
  migrations/        ← Estrutura do banco
  seeders/           ← Dados iniciais

resources/
  views/
    layouts/         ← app.blade.php (sidebar + topbar)
    dashboard/
    clientes/
    os/
    execucao/
    vendas/
    financeiro/
    relatorios/
  css/app.css        ← Tailwind + componentes
  js/app.js          ← Alpine.js + helpers

routes/
  web.php            ← Todas as rotas web
```

---

## Usuário padrão após seed

| Campo | Valor |
|-------|-------|
| Email | admin@voltcore.com.br |
| Senha | voltcore@2025 |

> ⚠️ Trocar a senha após o primeiro login.

---

## Migrando dados do sistema antigo

```bash
# Exportar do sistema antigo (MySQL)
mysqldump -u mapos_user -p mapos \
  clientes os execucoes vendas \
  lancamentos categorias produtos servicos \
  usuarios visita_tecnica > legado_export.sql

# Rodar o comando de migração (a ser implementado)
php artisan voltcore:importar-legado --arquivo=legado_export.sql
```

---

## Licença

Proprietário — Proton Mobilidade
