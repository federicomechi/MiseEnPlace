# Lavorare su due Mac

Questa guida rende ripetibile il lavoro sul progetto MiseEnPlace da due Mac con utenti macOS diversi.

## Regole essenziali

La cartella del progetto deve essere sempre:

```text
/Users/<utente>/Herd/MiseEnPlace
```

Il repository centrale è:

```text
https://github.com/federicomechi/MiseEnPlace.git
```

GitHub sincronizza **il codice**. Non sincronizza automaticamente il database locale, le password, le chiavi o i file caricati.

| Va su GitHub | Resta solo sul Mac |
| --- | --- |
| PHP, Vue, CSS, migration, test, documentazione | `.env`, password, database MariaDB, `vendor/`, `node_modules/`, log, file temporanei |

## Preparazione del secondo Mac

### 1. Installa gli strumenti

Servono Git, PHP/Composer, Node.js, MariaDB, Laravel Herd e GitHub CLI. Con Homebrew puoi installare quelli disponibili da terminale:

```bash
brew install git composer node mariadb gh
brew services start mariadb
```

Installa inoltre Laravel Herd e verifica che stia servendo la cartella `~/Herd`.

### 2. Configura Git e GitHub

```bash
git config --global user.name "federicomechi"
git config --global user.email "studio@mechi.it"
gh auth login
```

Nel login GitHub scegli: `GitHub.com` → `HTTPS` → `Yes` → `Login with a web browser`.

### 3. Clona il progetto

```bash
cd /Users/$USER/Herd
git clone https://github.com/federicomechi/MiseEnPlace.git
cd MiseEnPlace
```

Verifica che il repository sia pulito:

```bash
git status
```

### 4. Installa le dipendenze del progetto

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

`npm ci` usa esattamente le versioni registrate in `package-lock.json`. Preferiscilo a `npm install` per preparare un nuovo Mac.

### 5. Crea e configura il database locale

Ogni Mac ha un database **locale** chiamato `mise_en_place`. Accedi a MariaDB come amministratore:

```bash
sudo mariadb
```

Poi esegui:

```sql
CREATE DATABASE IF NOT EXISTS mise_en_place
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'mariadb'@'localhost'
  IDENTIFIED BY 'SCEGLI_UNA_PASSWORD_LOCALE';
CREATE USER IF NOT EXISTS 'mariadb'@'127.0.0.1'
  IDENTIFIED BY 'SCEGLI_UNA_PASSWORD_LOCALE';

GRANT ALL PRIVILEGES ON mise_en_place.* TO 'mariadb'@'localhost';
GRANT ALL PRIVILEGES ON mise_en_place.* TO 'mariadb'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

Apri `.env` e imposta i valori locali, senza mai versionare questo file:

```dotenv
APP_URL=http://mise-en-place.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mise_en_place
DB_USERNAME=mariadb
DB_PASSWORD=LA_TUA_PASSWORD_LOCALE
```

Infine crea le tabelle:

```bash
php artisan migrate
```

### 6. Avvia e verifica l'applicazione

Per sviluppo frontend:

```bash
npm run dev
```

Herd serve l'applicazione tramite il dominio che ha assegnato alla cartella; se necessario controllalo dall'interfaccia di Herd. Per una verifica completa prima di consegnare una modifica:

```bash
npm run build
php artisan test
```

## Flusso quotidiano

### Quando inizi su un Mac

Non iniziare a modificare prima di aggiornare il codice:

```bash
cd /Users/$USER/Herd/MiseEnPlace
git pull --rebase
php artisan migrate
```

Il secondo comando applica eventuali nuove migration ricevute da GitHub.

### Quando termini una modifica

```bash
npm run build
php artisan test
git status
git add <file-modificati>
git commit -m "feat: descrizione breve della modifica"
git push
```

Usa messaggi espliciti, per esempio:

```text
feat: aggiunge elenco ricette
fix: corregge calcolo costo ingrediente
docs: aggiorna guida importazione FileMaker
```

Prima di spegnere o cambiare Mac, assicurati che `git status` dica che il ramo è aggiornato rispetto a `origin/main`.

## Lavorare su una modifica più ampia

Per una funzionalità che richiede più sessioni crea un ramo:

```bash
git switch -c feat/nome-funzionalita
git push -u origin feat/nome-funzionalita
```

Sul secondo Mac riprendi quel lavoro così:

```bash
git pull --rebase
git switch feat/nome-funzionalita
git pull --rebase
```

Quando la funzionalità è completa, verifica build e test; poi unisci il ramo in `main` tramite GitHub oppure con Git locale.

## Database e migration

Una modifica allo schema non si fa manualmente su un solo database. Si crea sempre una migration Laravel:

```bash
php artisan make:migration add_colonna_to_recipes_table
```

Poi:

1. modifica la migration;
2. esegui `php artisan migrate`;
3. verifica l'applicazione;
4. committa **la migration**;
5. sull'altro Mac: `git pull --rebase && php artisan migrate`.

Non usare `migrate:fresh` su un database con dati importanti: cancella tutte le tabelle prima di ricrearle.

## Dati FileMaker e backup

- Gli export DDR e i dati originali FileMaker non vanno modificati.
- Gli import CSV/JSON e gli script di importazione saranno documentati e versionati quando verranno creati.
- Per avere gli stessi dati sui due Mac, usa l'importatore del progetto o un dump MariaDB concordato; non copiare il database "a mano" durante lo sviluppo.
- Prima di un import importante, crea un dump locale:

```bash
mysqldump -h 127.0.0.1 -u mariadb -p mise_en_place > ~/Desktop/mise_en_place_backup.sql
```

## Se Git segnala un conflitto

1. Fermati e non usare `git reset --hard`.
2. Esegui `git status` per vedere i file coinvolti.
3. Apri i file con i marcatori `<<<<<<<`, `=======`, `>>>>>>>`.
4. Mantieni la versione corretta, elimina i marcatori, poi esegui:

```bash
git add <file-risolti>
git rebase --continue
```

Se il conflitto non è chiaro, conserva i file e chiedi supporto prima di completare il rebase.

## Checklist rapida

Prima di iniziare:

```bash
git pull --rebase && php artisan migrate
```

Prima di cambiare Mac:

```bash
npm run build && php artisan test && git add <file> && git commit -m "..." && git push
```

## Problemi locali conosciuti

Su questo Mac PHP può mostrare un avviso relativo all'estensione MongoDB e a `libsnappy`. MiseEnPlace non usa MongoDB: l'avviso non blocca MariaDB, migration, build o test. Va corretto nella configurazione PHP/Herd solo se si desidera eliminare il messaggio.
