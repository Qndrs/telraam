# Qndrs Telraam Inzicht - Projectstate

Laatste update: 2026-07-25

## Huidige status

De eerste implementatiestappen zijn gestart. De plugin heeft nu een minimale WordPress bootstrap, basisstructuur, opgeschoonde admin settings page, API-tokenstatus, API-testknop, Telraam API-client, transient cachinglaag, traffic report normalizer, toegankelijke frontend shortcode-output, neutrale frontend/admin basisstijl en eerste vertaalbestanden.

Voor WordPress.org-publicatie vertrouwen we op WordPress.org language packs. De plugin roept `load_plugin_textdomain()` niet handmatig aan, zodat Plugin Check geen waarschuwing geeft over de sinds WordPress 4.6 ontmoedigde functie. De `languages/` bestanden blijven voorlopig bron-/ontwikkelmateriaal in de repository.

De repository `Qndrs/telraam` bestaat en bevat nu documentatie plus een minimale plugin-entrypoint, settings page, API client, caching repository, shortcode renderer en frontend/admin stylesheets. De WordPress-pluginnaam/slug is `qndrs-telraam-inzicht`. Er is nog geen Composer-configuratie of PHPUnit-testset.

Lokale projectroot:

```text
D:\_qndrs\Telraam-plugin
```

Lokale plugin/repository-root:

```text
D:\_qndrs\Telraam-plugin\telraam
```

GitHub remote:

```text
https://github.com/Qndrs/telraam.git
```

Huidige branch:

```text
main
```

Laatste bekende commit:

```text
3eeed59 Release 0.3.1 compact display fixes
```

Huidige werkversie:

```text
0.3.1
```

## Huidige bestanden

In `telraam/`:

```text
LICENSE
PROJECT_PLAN.md
PROJECT_STATE.md
  qndrs-telraam-inzicht.php
readme.txt
.gitattributes
includes/
  Plugin.php
  index.php
  Admin/
    SettingsPage.php
    index.php
  Api/
    Client.php
    TrafficReportRepository.php
    TrafficReportNormalizer.php
    index.php
  Frontend/
    Assets.php
    Shortcodes.php
    index.php
assets/
  index.php
  css/
    admin.css
    frontend.css
    index.php
languages/
  index.php
  qndrs-telraam-inzicht.pot
  qndrs-telraam-inzicht-nl_NL.po
  qndrs-telraam-inzicht-nl_NL.mo
```

Naast de repo staat lokaal ook een map:

```text
http-message/
```

Deze map bevat een losse kopie van `psr/http-message` interfaces. Voor de WordPress-plugin MVP is deze dependency waarschijnlijk niet nodig, omdat WordPress zelf de HTTP API levert via `wp_remote_post()`.

## Besloten richting

We bouwen een WordPress-plugin die Telraam-statistieken toont op een WordPress-website.

Uitgangspunten:

- PHP 8.3+
- WordPress plugin
- Pluginlicentie: GPL-2.0-or-later
- Plugin slug: `qndrs-telraam-inzicht`
- Telraam API via WordPress HTTP API
- Shortcode-first aanpak
- Later mogelijk Gutenberg block
- Caching via WordPress transients
- Tweetalig vanaf de MVP:
  - Engels als brontaal/fallback
  - Nederlands (`nl_NL`) meegeleverd
  - textdomain: `qndrs-telraam-inzicht`

Voor WordPress.org/Plugin Check moet de deployment-directory ook `qndrs-telraam-inzicht` heten. De lokale Git-repo staat nu nog in een map `telraam`, maar bij plaatsing in `wp-content/plugins` moet de pluginmap dus zijn:

```text
wp-content/plugins/qndrs-telraam-inzicht
```

## Eerste Telraam segment

De eerste concrete testcase is Roberts Telraam:

- Segment-ID: `9000010390`
- Publieke pagina: <https://telraam.net/nl/location/9000010390>

Dit segment wordt gebruikt als standaard demo/configuratie tijdens de eerste implementatie.

Voor later is besloten dat de plugin ook een publieke Telraam locatiepagina als input moet kunnen gebruiken. Bijvoorbeeld:

```text
https://telraam.net/nl/location/9000010390
```

De plugin moet daaruit het segment-ID `9000010390` kunnen halen en dezelfde statistieken tonen als bij directe segment-ID input.

## Relevante Telraam bronnen

- Telraam homepage: <https://telraam.net/>
- Roberts segment: <https://telraam.net/nl/location/9000010390>
- Data interpretation and the Telraam API: <https://faq.telraam.net/en/category/2/data-interpretation-and-the-telraam-api>
- Using the Telraam API: <https://faq.telraam.net/en/article/27/you-wish-more-data-and-statistics-using-the-telraam-api>
- Understanding the Telraam API: <https://faq.telraam.net/en/api-introduction>
- API token information: <https://faq.telraam.net/article/397/api-token-information-update>

## Testomgeving

Er is een WordPress-testsite beschikbaar:

```text
https://qndrs.training/telraam/
```

SSH:

```text
host: qndrs.training
user: qndrs
key: C:\Users\Gebruiker\Documents\keys\zadkine.link
```

WordPress-root op de server:

```text
/home/qndrs/public_html/telraam
```

Pluginmap op de server:

```text
/home/qndrs/public_html/telraam/wp-content/plugins
```

Bevestigde serverstatus:

- WordPress-site reageert onder `/telraam/`
- `wp-content/plugins` bevat standaard `akismet`, `hello.php` en `index.php`
- WP-CLI is beschikbaar via `/usr/local/bin/wp`
- Plugin Check is geïnstalleerd op de testsite
- De webserver gebruikt volgens `.htaccess` `ea-php84`
- De standaard SSH/CLI `php` binary is PHP 8.1.34
- PHP 8.4 is beschikbaar via `/opt/cpanel/ea-php84/root/usr/bin/php`

Voor PHP 8.3+ validatie moet expliciet de cPanel PHP 8.4 binary gebruikt worden in plaats van de standaard `php` op SSH.

## API kennis die vastligt

De eerste API-call gebruikt:

```text
POST https://telraam-api.net/v1/reports/traffic
```

Headers:

```text
X-Api-Key: <token>
Content-Type: application/json
```

Request-body voorbeeld:

```json
{
  "id": "9000010390",
  "time_start": "2026-07-17 00:00:00Z",
  "time_end": "2026-07-24 00:00:00Z",
  "level": "segments",
  "format": "per-hour"
}
```

Bekende API-beperkingen:

- 1 request per seconde
- burst 1
- 1000 requests per dag voor gewone API-gebruikers
- maximaal 3 maanden per traffic report request

## Nog niet aanwezig

Nog te bouwen:

- tests of geautomatiseerde testscenario's
- shortcode builder in de admin
- publieke Telraam locatie-URL parsing naar segment-ID
- fun styling als optionele tweede laag voorbereiden na de neutrale basisstijl
- dagelijkse aggregates/snapshots opslaan voor toekomstige regressiegrafieken, trends en periodevergelijkingen

## Open technische keuzes

Nog te bepalen:

- Exacte plugin namespace/prefix
- Minimale WordPress-versie
- Wel of geen Composer/autoloading
- Wel of geen Chart.js in versie 1.1
- Exacte datastructuur voor genormaliseerde Telraam responses
- Of meerdere segmenten direct in v1.0 komen of pas in v2
- Exacte Nederlandse terminologie voor Telraam modaliteiten en datakwaliteit
- Of URL-input direct in de shortcode MVP komt of samen met de latere shortcode builder
- Definitieve WordPress.org contributors/tags/readme metadata
- Exacte opslagvorm voor trenddata: waarschijnlijk custom table, niet options/transients

## Laatste WordPress-test

Uitgevoerd op 2026-07-24 op:

```text
https://qndrs.training/telraam/
```

Resultaat:

- Plugin actief
- Telraam API-token is opgeslagen als WordPress option
- Tokenwaarde is niet geprint of gelogd
- Standaard segment-ID: `9000010390`
- Standaard periode: 7 dagen
- Cacheduur: 60 minuten
- Shortcode `[qndrs_telraam_segment id="9000010390" days="7"]` haalt echte data op
- Shortcode table view werkt
- Shortcode table view met `rows="5"` toont exact 5 datarijen
- Shortcode table view met `rows="all"` toont meer dan 24 datarijen
- Table timestamps worden als `<time>` elementen gerenderd
- Row headers gebruiken het tijdveld
- Nederlandse tekst voor `days="1"` toont "laatste dag" en niet "last day"
- Template-inspringing is uit de shortcode HTML-output verwijderd
- Frontend stylesheet `qndrs-telraam-inzicht` wordt via WordPress enqueue geladen
- Shortcode-output bevat de basisstijlklassen voor summary cards, uptime-indicator en table wrapper
- Tabeltitel staat nu zichtbaar buiten de horizontaal scrollende table wrapper, met behoud van een native `<caption>` voor screenreaders
- Admin stylesheet `qndrs-telraam-inzicht-admin` wordt op de plugin settings page geladen
- Admin settings page gebruikt kaartlayout met gescopete `.qndrs-telraam-admin` CSS
- API-testen en API-token wissen staan visueel bij het API-token veld
- Cache wissen staat visueel bij het cacheduur veld
- Actieknoppen gebruiken aparte action-forms via het HTML `form` attribuut, zodat er geen geneste forms ontstaan
- API-token wissen zet het token nu echt leeg; de settings sanitizer heeft hiervoor een expliciete interne clear-flag
- API-token wissen verwijdert nu ook alle 1 t/m 90 dagen traffic-cache voor het ingestelde standaardsegment
- Dubbele groene WordPress settings-notice bij opslaan is opgelost door de extra `settings_errors()` call uit de pluginpagina te verwijderen
- Frontend summary cards reageren nu op de containerbreedte via `auto-fit` en container-query units, zodat sidebarplaatsing geen te smalle vierkolomskaartjes meer forceert
- Frontend labels zijn ingekort voor smalle sidebarweergave: "Telraam" en "Uptime"
- Toekomstige verbetering vastgelegd: `title` shortcode-attribuut, inclusief lege waarde om de plugin-heading te verbergen
- Tweede shortcode-call kwam snel terug (`elapsed=0.0069`), passend bij transient cache
- Plugin Check op de testsite: `Success: Controles afgerond. Geen fouten gevonden.`

Geteste summary-output:

```text
Voetgangers: 6195
Tweewielers: 44574
Auto's: 3240
Zwaar verkeer: 288
Uptime: 99,9%
```

Open punt uit test:

- Geen open punt meer voor tokenstatus; de admin settings page toont nu expliciet of er een API-token opgeslagen is, zonder de tokenwaarde te tonen.

## WordPress.org publicatie-aandachtspunten

Voor publicatie moet nog een aparte reviewronde plaatsvinden. De repository is nu private en wordt pas later publiek gemaakt.

Vastgelegde checks voor die review:

- `readme.txt` toevoegen volgens de WordPress.org plugin readme standaard
- `Stable tag` afstemmen op de pluginversie
- Geen `Stable Tag: trunk` voor nieuwe publieke release
- Plugin header en readme metadata consistent houden
- Pluginmap/distributieslug: `qndrs-telraam-inzicht`
- Plugin Check draaien op de testsite
- GPL-2.0-or-later consistent houden in header, readme en `LICENSE`
- Runtime-vertalingen voor publicatie via WordPress.org language packs laten lopen
- Telraam API-gebruik, externe requests en datalicentie duidelijk documenteren
- Geen secrets, API-tokens of persoonlijke testdata opnemen
- Naamgeving controleren op merk-/trademarkkritiek rond Telraam en WordPress

Huidige distributienotitie:

- `readme.txt` is toegevoegd voor Plugin Check en WordPress.org-voorbereiding
- `.gitattributes` sluit `PROJECT_PLAN.md`, `PROJECT_STATE.md` en lokale release-zips uit bij `git archive`

Officiële referenties:

- <https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/>
- <https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/>
- <https://developer.wordpress.org/plugins/wordpress-org/common-issues/>

## Eerstvolgende logische stap

De technische MVP-basis is aanwezig en getest:

1. ~~`qndrs-telraam-inzicht.php` aanmaken~~
2. ~~Basis namespaced PHP-structuur opzetten~~
3. ~~Admin settings page toevoegen~~
4. ~~API client bouwen met `wp_remote_post()`~~
5. ~~Caching met transients toevoegen~~
6. ~~Shortcode `[qndrs_telraam_segment]` implementeren~~
7. ~~Alle zichtbare strings vertaalbaar maken~~
8. ~~Nederlandse vertaling toevoegen~~
9. ~~Testen met segment `9000010390`~~
10. ~~Plugin Check draaien~~

Eerstvolgende pragmatische opties:

1. `title` shortcode-attribuut toevoegen, inclusief `title=""` om de plugin-heading te verbergen.
2. Nieuwe installatie-ZIP maken voor `0.3.1` als die extern getest moet worden.
3. Fun styling optioneel voorbereiden, bijvoorbeeld via style preset.
4. Daarna pas shortcode builder/admin UX uitbreiden.

## Laatste lokale validatie

Uitgevoerd op 2026-07-24:

```text
php -l qndrs-telraam-inzicht.php
php -l includes\Plugin.php
php -l includes\Admin\SettingsPage.php
php -l includes\Api\Client.php
php -l includes\Api\TrafficReportRepository.php
php -l includes\Api\TrafficReportNormalizer.php
php -l includes\Frontend\Assets.php
php -l includes\Frontend\Shortcodes.php
```

Resultaat:

```text
No syntax errors detected
```

Vertaalbestandvalidatie:

```text
languages\qndrs-telraam-inzicht-nl_NL.mo
```

Resultaat:

```text
MO written, entries: 56
```

## Laatste implementatieronde

Uitgevoerd:

- Versie opgehoogd naar `0.2.0`
- Admin tokenstatus toegevoegd zonder tokenwaarde te tonen
- Admin API-testknop toegevoegd
- API-testresultaat wordt tijdelijk per gebruiker opgeslagen in een transient en daarna verwijderd
- `TrafficReportNormalizer` toegevoegd voor stabiele rows en summary totals
- Shortcode refactored naar genormaliseerde data
- Shortcode HTML verbeterd met `section`, `header`, gelabelde headings, table caption, row headers en `role="alert"` voor fouten
- Enkelvoud/meervoud voor dag/dagen geïmplementeerd; bij 1 dag toont de output "laatste dag"
- Expliciete adminactie toegevoegd om het API-token te wissen
- Table view ondersteunt nu `rows`, met standaard `24`, maximum `500` en `rows="all"` voor alle teruggegeven regels
- Frontend table-timestamps worden nu als lokale datum/tijd in een `<time datetime="">` element gerenderd
- Template-inspringing wordt uit shortcode HTML-output verwijderd
- Frontend basisstylesheet toegevoegd via `assets/css/frontend.css`
- Frontend assets worden via `wp_enqueue_scripts` geladen
- Verkeerstellingen worden visueel als hoofdkaarten gepresenteerd
- Uptime wordt visueel kleiner als datakwaliteitsindicator gepresenteerd
- Tabelweergave heeft nu een responsive horizontale scroll-wrapper en neutrale tabelstijl
- Mobiele tabeltitel gefixt door de zichtbare titel buiten de scroll-wrapper te plaatsen en de native caption visueel te verbergen
- Admin settings page verplaatst naar compacte kaartlayout
- Admin API-acties staan bij het API-token veld en cacheactie staat bij cacheduur
- Admin basisstylesheet toegevoegd via `assets/css/admin.css`
- Bugfix: API-token wissen werd door token-preserve sanitization teruggedraaid; opgelost met expliciete clear-flag
- Bugfix: API-token wissen wist nu segment-cache voor alle ondersteunde periodes
- Versie voorbereid als `0.3.1`
- Bugfix: dubbele settings-saved notice bij opslaan verwijderd
- Bugfix: summary card getallen/labels schalen beter in smalle sidebars
- Sidebarverbetering: hoofdheading en uptime-label ingekort
- `readme.txt`, POT/PO/MO en projectstate bijgewerkt

## Actuele snapshot voor hervatten

Vastgelegd op 2026-07-25 na de 429/layout-fix bovenop commit `3eeed59`.

Repo/status:

- Branch: `main`
- Remote: `origin/main`
- Laatste commit: `3eeed59 Release 0.3.1 compact display fixes`
- Werkversie: `0.3.1`
- Werkboom heeft lokale wijzigingen na `3eeed59`: `PROJECT_STATE.md`, `PROJECT_PLAN.md`, `assets/css/frontend.css` en `includes/Api/Client.php`

Functionele stand:

- Shortcode `[qndrs_telraam_segment]` werkt met echte Telraam API-data.
- Standaard segment op testsite: `9000010390`.
- API-token is door Robert geroteerd.
- API-token wissen, opnieuw opslaan en foutmeldingen bij ontbrekende token werken op frontend en admin.
- Cache wordt bij token wissen voor het ingestelde segment voor periodes 1 t/m 90 dagen verwijderd.
- Adminpaneel gebruikt compacte kaartlayout met acties bij de relevante velden.
- Frontend gebruikt neutrale basisstijl met container-responsieve summary cards.
- Sidebarweergave is verbeterd; heading is nu `Telraam`, uptime-label is `Uptime`.
- Brede tegelweergave vult weer de beschikbare breedte met expliciete vierkolomsgrid.
- Smalle containers gebruiken container-breakpoints naar 3, 2 en 1 kolom.
- Telraam API-client spreidt live API-calls binnen één PHP request om de 1 request/sec limiet te respecteren.
- Telraam API-client probeert één retry na een HTTP 429 response.
- Tabelcaption staat mobiel buiten de horizontale scroll-wrapper; native `<caption>` blijft beschikbaar voor screenreaders.

Laatste checks:

- PHP lint op pluginbestanden: groen
- Shortcode-outputcheck op testsite: heading `Telraam`, label `Uptime`, versie `0.3.1`
- Koude-cache test op testsite met `days="1"` en `days="7"` in één request: geen 429, totals aanwezig, elapsed circa 2.49s
- Plugin Check op testsite: groen

Distributie:

- Laatste eerder gemaakte installatie-ZIP: `D:\_qndrs\Telraam-plugin\qndrs-telraam-inzicht-0.3.0.zip`
- Voor externe test van de huidige `0.3.1` stand moet nog een nieuwe ZIP worden gemaakt.

Open vervolgpunten:

- `title` shortcode-attribuut toevoegen, inclusief `title=""` om de plugin-heading te verbergen.
- Fun styling optioneel voorbereiden, bijvoorbeeld via style preset.
- Shortcode builder in admin later toevoegen.
- Publieke Telraam locatie-URL later als input ondersteunen.
- Dagelijkse aggregates/snapshots later opslaan voor trends/regressiegrafieken.
