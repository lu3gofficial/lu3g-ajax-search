# LU3G Ajax Search

Widget Elementor di ricerca Ajax per CPT (JetEngine compatibile): lente che apre un pannello di ricerca con effetto blur, risultati live e link al dettaglio.

Plugin WordPress di LU3G Agenzia Web.

## Installazione su un sito
1. Scarica `lu3g-ajax-search.zip` dall'ultima [release](../../releases/latest).
2. WordPress → Plugin → Aggiungi nuovo → Carica plugin → Attiva.
3. Gli aggiornamenti arrivano da soli dalle release di questo repository (Bacheca → Aggiornamenti, entro 6 ore;
   subito con "Controlla di nuovo" o con il link "Controlla aggiornamenti" nella pagina Plugin).
   Facoltativo: `define( 'LU3G_GITHUB_TOKEN', 'github_pat_...' );` in `wp-config.php` alza il limite di richieste all'API GitHub.

I siti con una versione precedente alla 1.2.2 (senza aggiornamenti automatici) vanno aggiornati a mano una volta:
Plugin → Aggiungi nuovo → Carica plugin → "Sostituisci la versione attuale con quella caricata".

## Pubblicare una nuova versione
1. Modifica il codice.
2. Aumenta la versione nell'intestazione di `lu3g-ajax-search.php` (`Version:`), nella costante della versione.
3. Aggiungi la sezione della versione in `CHANGELOG.md` (diventa la nota della release).
4. Commit, poi tag e invio: `git tag v1.2.2 && git push && git push --tags`
5. L'automazione `.github/workflows/release.yml` controlla la versione, crea lo zip e pubblica la release.

Pacchetto in locale: `sh bin/build.sh` → `dist/lu3g-ajax-search.zip`.
