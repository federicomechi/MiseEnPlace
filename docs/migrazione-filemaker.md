# Migrazione FileMaker → MiseEnPlace

## Decisioni iniziali

- Applicazione: Laravel 13, PHP 8.3+, Vue 3, Inertia e Vite.
- Database di sviluppo e produzione: MariaDB.
- Autenticazione: Laravel Fortify; i ruoli applicativi saranno modellati separatamente dai vecchi account FileMaker.
- Cartella locale convenzionale: `/Users/<utente>/Herd/MiseEnPlace`.
- Repository condiviso: `https://github.com/federicomechi/MiseEnPlace.git`.
- I file `.env`, dati locali, allegati e credenziali non sono versionati.

## Inventario DDR

Sorgente analizzata: `Ricette.fmp12`, export DDR FileMaker 26.0.1 del 22 luglio 2026.

| Oggetto FileMaker | Quantità |
| --- | ---: |
| Tabelle base | 29 |
| Occorrenze tabella | 51 |
| Relazioni | 45 |
| Layout | 91 |
| Script | 68 |
| Funzioni personalizzate | 22 |
| Account / set privilegi | 9 / 10 |

## Domini individuati

| Priorità | Dominio FileMaker | Destinazione proposta |
| --- | --- | --- |
| 1 | `Ricette`, `Fasi`, `FAS_Ingredienti`, `DB_ingredienti` | ricette, fasi, ingredienti e righe ingrediente |
| 1 | `RIC_Attrezzature`, `DB_attrezzature` | attrezzature e tabella ponte ricetta-attrezzatura |
| 1 | `RIC_Supporti`, `DB_supporti` | supporti e tabella ponte ricetta-supporto |
| 2 | `DB_allergeni` | catalogo allergeni e associazioni ingrediente/allergene |
| 2 | `Menu`, `Menu_ricette`, `Menu_ospiti`, `Menu_bevande` | composizione dei menu |
| 2 | `BAR`, `BAR_Ingredienti` | bevande e relativi ingredienti |
| 3 | `Produzione` | pianificazione e produzione |
| 3 | `DB_cliente`, `DB_ospiti`, `DB_fornitori`, `DB_listino` | anagrafiche e prezzi |
| Da rivalutare | `APP`, `srch`, `html`, `WebAssets*`, `Carrello` | stato UI, ricerca e funzionalità web: non migrare automaticamente come tabelle |

## Relazioni confermate nel primo nucleo

- `Fasi.RIC_ID = Ricette.ID`: una ricetta ha molte fasi.
- `FAS_Ingredienti.FAS_ID = Fasi.ID` e `FAS_Ingredienti.RIC_ID = Fasi.RIC_ID`: ogni riga ingrediente appartiene a una fase e alla relativa ricetta.
- `FAS_Ingredienti.ING_ID = DB_ingredienti.ID`: ogni riga collega un ingrediente di catalogo.
- `RIC_Attrezzature.RIC_ID = Ricette.ID`: relazione ricetta-attrezzatura.
- `RIC_Supporti.RIC_ID = Ricette.ID`: relazione ricetta-supporto.

La chiave composta `ricetta_id + fase_id` visibile in alcune relazioni FileMaker sarà semplificata: in MariaDB la riga ingrediente avrà `fase_id`; la ricetta sarà raggiungibile tramite la fase. Un eventuale `ricetta_id` duplicato sarà ammesso solo se necessario per importazione o prestazioni, con vincolo di coerenza applicativo.

## Strategia di migrazione

1. Definire lo schema MariaDB del nucleo Ricette e scrivere le migration Laravel.
2. Realizzare interfaccia Vue completa per elenco, ricerca, dettaglio, fasi e ingredienti.
3. Esportare i dati FileMaker in CSV/JSON per ciascuna tabella base; allegare la corrispondenza dei campi.
4. Scrivere un importatore Laravel idempotente e verificare conteggi, chiavi e collegamenti.
5. Migrare attrezzature, supporti e allergeni, quindi Menu/Bar e infine Produzione e anagrafiche.

## Regole per il lavoro su due Mac

Prima di iniziare: `git pull --rebase`.

Al termine di una modifica verificata:

```bash
git add .
git commit -m "descrizione della modifica"
git push
```

Ogni Mac usa un proprio `.env` con un database MariaDB locale, ad esempio `mise_en_place`. Le migration sono l'unica fonte versionata dello schema; i dump dati completi non entrano nel repository.

## Prossimo incremento

Creare migration, modelli e test per `recipes`, `recipe_steps`, `ingredients` e `recipe_step_ingredients`, dopo aver validato la mappatura dei campi del catalogo Ricette e Fasi.
