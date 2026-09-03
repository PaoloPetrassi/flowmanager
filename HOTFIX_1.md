# FlowManager v1.3.0 — Hotfix 1

Corregge i 3 test falliti dopo l'aggiornamento 1.1 + 1.2 + 1.3:

1. `V101PolishHardeningTest`: il test storico non fissa più la versione a `1.0.1`, ma verifica un numero di versione SemVer valido.
2. `DemoModeTest` / `ApiDocumentationTest`: la pagina di documentazione API usa ora la chiave namespaced `ui.api_pagination`, evitando su Windows la collisione case-insensitive con `lang/<locale>/pagination.php` che faceva restituire un array a Blade.
3. Aggiunto un test di regressione della documentazione API con locale inglese.

## Applicazione

Copia il contenuto dello ZIP nella root del progetto FlowManager e sovrascrivi i file esistenti.

Poi, dal terminale Git Bash integrato in VS Code:

```bash
php artisan optimize:clear
php artisan test
```

Non sono necessarie nuove migrazioni, dipendenze Composer o dipendenze npm.
