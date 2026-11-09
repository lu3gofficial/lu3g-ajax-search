# Registro delle modifiche

## 1.7.2
- `includes/class-updater.php` ora è generato dal modello comune di [lu3g-updater](https://github.com/lu3gofficial/lu3g-updater) (1.0.0), a partire dal nuovo file `.lu3g-updater`. Il codice dell'updater è lo stesso di prima: cambia solo il commento in cima.
- Release avviabile anche da GitHub, senza computer: *Actions → Release → Run workflow* sul branch `main`. Il tag lo crea l'automazione dalla `Version` del plugin, e si ferma se quella versione è già pubblicata.

## 1.7.1
- Nuova opzione "Cerca nello SKU dei prodotti", attiva di serie: con WooCommerce trova il prodotto anche con una parte del codice ("114/26050" trova "chiavisegrete-114/26050"), comprese le varianti.
- Corretto: nel campo "Meta field in cui cercare" si poteva inserire un tag dinamico, che diventava un nome di campo inesistente e la ricerca non trovava nulla. Ora il campo accetta solo nomi scritti a mano; i valori con spazi vengono ignorati.

## 1.7.0
- Stile → Risultati, "Parte del titolo da mostrare": titolo intero, solo dopo il separatore o solo prima. "Abito Da Sposa Demetrios – Style 1285" può diventare "Style 1285". La ricerca continua a cercare nel titolo completo, che resta visibile passando il mouse. I titoli senza separatore restano interi.

## 1.6.0
- Stile → Risultati, "Troncare il titolo dopo": 1, 2, 3 o 4 righe, oppure mai, per desktop, tablet e mobile. Il predefinito resta 1 riga con "…".
- "Numero massimo di caratteri del titolo": taglia all'ultima parola intera e aggiunge "…"; il titolo completo resta visibile passando il mouse.

## 1.5.0
- Nuova sezione Stile → Barra di input: larghezza, altezza, spaziature, distanza tra lente e testo, arrotondamento (anche angolo per angolo), caratteri completi e bordo. Con le schede Normale, Hover e Attiva: sfondo, colori di testo e lente, bordo e ombra.
- Lente a sinistra o a destra del testo.
- X per svuotare: solo quando c'è del testo (nuovo comportamento della barra), sempre o mai.
- Distanza della tendina dalla barra regolabile.
- Corretto: con il testo iniziale vuoto, alla selezione del campo compariva una tendina vuota.

## 1.4.0
- Nuovo controllo "Mostra come: Icona / Barra di input", per desktop, tablet e mobile: la barra prende il posto della lente e il campo resta dove si trova il widget. I widget esistenti restano con l'icona.
- Allineamento della barra (sinistra, centro, destra), per dispositivo.
- La tendina dei risultati sta nel <body> mentre è aperta: nell'header non viene più tagliata né coperta da altri elementi.

## 1.3.0
- Corretto: con "Allineato all'icona" il box usciva dal bordo sinistro dello schermo quando l'icona era a sinistra. Ora resta sempre dentro lo schermo.
- Posizione del box scelta per desktop, tablet e mobile: allineato all'icona; sotto l'header al centro, a sinistra, a destra o a tutta larghezza; in cima allo schermo; al centro dello schermo; in basso; a tutto schermo; nella pagina, sempre visibile con i risultati a tendina. Distanze e altezza massima regolabili per dispositivo. I widget già salvati mantengono la loro posizione.
- Grafica: font, larghezza, arrotondamento, bordo, ombra, effetto vetro, spaziature; nuova sezione "Campo di ricerca" (sfondo, colori, dimensione del testo, linea, lente, X); nei risultati dimensione del sottotitolo, immagine, spaziature e arrotondamento.
- Testi: tutti modificabili, anche quelli letti dagli screen reader. I testi predefiniti ora sono generici e non parlano più di abiti: chi non li aveva cambiati vede "Cerca…" e può riscrivere il proprio. "%d" nel testo iniziale diventa il numero di caratteri minimi.
- Animazioni: undici aperture a scelta (scende, sale, da destra, da sinistra, dissolvenza, zoom, zoom all'indietro, rimbalzo, rotazione 3D, da sfocato, nessuna), con durata e andamento; cinque comparse dei risultati, con ritardo regolabile. Ferme con "riduci animazioni".
- Velo sullo sfondo disattivabile, clic fuori dal box per chiudere, "Tieni aperto nell'editor" per lavorare sulla grafica.
- I punti di passaggio tra desktop, tablet e mobile seguono quelli impostati in Elementor.

## 1.2.2
- Aggiornamenti automatici dalle release GitHub (lu3gofficial/lu3g-ajax-search) e link "Controlla aggiornamenti" nella pagina Plugin.

## 1.2.1
- Versione di partenza, importata dallo zip.
