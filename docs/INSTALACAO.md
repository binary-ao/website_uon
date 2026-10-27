# Instalação, configuração e manutenção

## 1. Requisitos

| | Detectado no ambiente de desenvolvimento | Mínimo recomendado |
|---|---|---|
| Sistema | Windows 11 Pro + XAMPP | Linux (Ubuntu/Debian) ou Windows com XAMPP |
| PHP | 8.2.12 | 8.2 ou superior |
| Extensões PHP | bcmath, ctype, curl, dom, fileinfo, gd, intl, mbstring, openssl, pdo_mysql, tokenizer, xml, zip | as mesmas (**fileinfo** e **gd** são obrigatórias para os uploads) |
| Base de dados | MariaDB 10.4.32 | MySQL 8 / MariaDB 10.4+ (InnoDB, utf8mb4) |
| Composer | não estava instalado (usado `composer.phar` 2.10) | 2.x |
| Node.js | 24 (opcional) | apenas para voltar a copiar as bibliotecas de frontend |

Não existe *build step*: o CSS/JS do template é servido tal como está. Bootstrap, Bootstrap Icons
e TinyMCE já estão em `public/vendor/`. Para os actualizar: `npm install && npm run vendor:sync`.

## 2. Instalação

```bash
composer install --no-dev --optimize-autoloader     # em desenvolvimento: composer install
cp .env.example .env                                 # Windows: copy .env.example .env
php artisan key:generate
```

Editar `.env` (nunca colocar o `.env` em repositórios):

```ini
APP_URL=https://www.exemplo-da-uon.ao        # URL público exacto, sem barra final
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Africa/Luanda                   # fuso horário de todas as datas (eventos, agendamentos)
DB_DATABASE=uon_website
DB_USERNAME=uon_user
DB_PASSWORD=********
SESSION_SECURE_COOKIE=true                   # obrigatório em HTTPS
MAIL_MAILER=smtp                             # necessário para a recuperação de palavra-passe
```

Criar a base de dados (exemplo MySQL):

```sql
CREATE DATABASE uon_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'uon_user'@'localhost' IDENTIFIED BY 'palavra-passe-forte';
GRANT ALL PRIVILEGES ON uon_website.* TO 'uon_user'@'localhost';
```

Migrações e dados estruturais (funções/permissões, taxonomias, árvore de páginas do template, menus):

```bash
php artisan migrate --seed --force
```

Os seeders podem ser executados novamente sem duplicar nem sobrescrever conteúdos editados.

## 3. Primeiro administrador

Não existem contas nem palavras-passe por omissão. Duas opções:

1. **Recomendado** — comando interactivo (a palavra-passe é pedida de forma oculta):
   ```bash
   php artisan uon:create-admin
   ```
2. Variáveis temporárias no `.env` (`UON_ADMIN_NAME`, `UON_ADMIN_EMAIL`, `UON_ADMIN_PASSWORD`)
   seguidas de `php artisan db:seed --force`. **Apague `UON_ADMIN_PASSWORD` do `.env` logo a seguir.**

Requisitos da palavra-passe: mínimo 10 caracteres, maiúsculas, minúsculas e números.
Aceder ao CMS em `/admin`.

## 4. Servidor web

### XAMPP (Windows) — recomendado: VirtualHost

`C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName uon.local
    DocumentRoot "C:/xampp/htdocs/uon_edu/public"
    <Directory "C:/xampp/htdocs/uon_edu/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Adicionar `127.0.0.1 uon.local` a `C:\Windows\System32\drivers\etc\hosts`, reiniciar o Apache e
definir `APP_URL=http://uon.local`.

### XAMPP sem VirtualHost

O `.htaccess` da raiz bloqueia `.env`, `vendor/`, `storage/`, logs e ZIPs, e redirecciona
`http://localhost/uon_edu/` para `http://localhost/uon_edu/public/`. Nesse caso use
`APP_URL=http://localhost/uon_edu/public`. (Verificado: `.env` → 403; páginas → 200.)

### Linux (Apache/Nginx)

O *document root* deve ser **sempre** `public/`. Para Nginx, usar a configuração padrão do Laravel
(`try_files $uri $uri/ /index.php?$query_string;`) e negar a execução de PHP em `/uploads/`:

```nginx
location ^~ /uploads/ { location ~ \.php$ { deny all; } }
```

### Permissões de pastas (Linux)

```bash
chown -R www-data:www-data storage bootstrap/cache public/uploads
chmod -R 775 storage bootstrap/cache public/uploads
```

### Uploads

Limites da aplicação no `.env` (`UON_UPLOAD_MAX_IMAGE_KB`, `UON_UPLOAD_MAX_DOCUMENT_KB`). O PHP também
tem de os permitir em `php.ini` (o XAMPP traz `upload_max_filesize=40M`):

```ini
upload_max_filesize = 25M
post_max_size = 30M
```

- Imagens: guardadas em `public/uploads/images/AAAA/MM/` com nome aleatório, re-codificadas com GD
  (remove metadados/conteúdo embebido), redimensionadas até 1920 px + variante média de 800 px.
- Documentos institucionais: `storage/app/private/documents/` (não acessíveis por URL); download
  apenas pela rota `/documentos/{slug}/download`, que verifica estado e visibilidade.

## 5. Tarefas agendadas

O agendamento de conteúdos funciona mesmo sem cron (um conteúdo agendado fica visível quando a data
chega). O agendador apenas actualiza o rótulo "Agendado" → "Publicado" e limpa tokens expirados.

- Linux: `* * * * * cd /var/www/uon_edu && php artisan schedule:run >> /dev/null 2>&1`
- Windows: Agendador de Tarefas → a cada minuto:
  `C:\xampp\php\php.exe C:\xampp\htdocs\uon_edu\artisan schedule:run`

## 6. Produção — lista de verificação

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

- `APP_DEBUG=false`, `APP_ENV=production`, HTTPS com `SESSION_SECURE_COOKIE=true`;
- configurar email (SMTP) e preencher em *Configurações → Contactos* o email que recebe as mensagens;
- preencher contactos, redes sociais e URLs das plataformas externas confirmados pela UON;
- remover o conteúdo de demonstração: `php artisan uon:demo-content --remove`;
- em *Configurações → SEO*, confirmar "Permitir indexação".

## 7. Actualização

```bash
php artisan down
# obter o novo código (git pull / cópia)
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## 8. Backup e restauro

Fazer cópia de três elementos:

1. Base de dados:
   ```bash
   mysqldump --single-transaction --routines -u uon_user -p uon_website > uon_AAAAMMDD.sql
   # XAMPP: C:\xampp\mysql\bin\mysqldump.exe -u root uon_website > uon_AAAAMMDD.sql
   ```
2. Ficheiros: `public/uploads/` e `storage/app/private/documents/`.
3. O ficheiro `.env` (guardado em local seguro — contém `APP_KEY`, necessário para as sessões cifradas).

Restauro: recriar a base de dados, `mysql -u uon_user -p uon_website < uon_AAAAMMDD.sql`, repor as
duas pastas de ficheiros e o `.env`, e executar `php artisan optimize:clear`.

## 9. Testes

```bash
php artisan test                         # SQLite em memória (não toca na base de dados real)
# Contra MySQL/MariaDB (base dedicada, é apagada e recriada pelos testes):
DB_CONNECTION=mysql DB_DATABASE=uon_website_test DB_USERNAME=root DB_PASSWORD= php vendor/bin/phpunit
```
