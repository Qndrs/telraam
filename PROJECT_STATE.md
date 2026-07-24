# Qndrs Telraam Inzicht - Projectstate

Laatste update: 2026-07-24

## Huidige status

De eerste implementatiestappen zijn gestart. De plugin heeft nu een minimale WordPress bootstrap, basisstructuur, admin settings page, Telraam API-client, transient cachinglaag, frontend shortcode en eerste vertaalbestanden.

Voor WordPress.org-publicatie vertrouwen we op WordPress.org language packs. De plugin roept `load_plugin_textdomain()` niet handmatig aan, zodat Plugin Check geen waarschuwing geeft over de sinds WordPress 4.6 ontmoedigde functie. De `languages/` bestanden blijven voorlopig bron-/ontwikkelmateriaal in de repository.

De repository `Qndrs/telraam` bestaat en bevat nu documentatie plus een minimale plugin-entrypoint, settings page, API client, caching repository en shortcode renderer. De WordPress-pluginnaam/slug is `qndrs-telraam-inzicht`. Er is nog geen README, tests of Composer-configuratie.

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
f6e3081 Initial commit
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
    index.php
  Frontend/
    Shortcodes.php
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

- frontend CSS
- tests of testscenario's
- README
- changelog
- shortcode builder in de admin
- publieke Telraam locatie-URL parsing naar segment-ID
- betere tokenstatus op admin settings page zonder tokenwaarde te tonen

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
- Exacte admin-UI tekst voor "API-token opgeslagen"

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
- Tweede shortcode-call kwam snel terug (`elapsed=0.0069`), passend bij transient cache

Geteste summary-output:

```text
Voetgangers: 6195
Tweewielers: 44574
Auto's: 3240
Zwaar verkeer: 288
Gemiddelde uptime: 99,9%
```

Open punt uit test:

- De admin settings page moet duidelijker tonen dat er een API-token bekend is.

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

Starten met de MVP-implementatie:

1. ~~`qndrs-telraam-inzicht.php` aanmaken~~
2. ~~Basis namespaced PHP-structuur opzetten~~
3. ~~Admin settings page toevoegen~~
4. ~~API client bouwen met `wp_remote_post()`~~
5. ~~Caching met transients toevoegen~~
6. ~~Shortcode `[qndrs_telraam_segment]` implementeren~~
7. ~~Alle zichtbare strings vertaalbaar maken~~
8. ~~Nederlandse vertaling toevoegen~~
9. Testen met segment `9000010390`
10. Plugin Check draaien

## Laatste lokale validatie

Uitgevoerd op 2026-07-24:

```text
php -l qndrs-telraam-inzicht.php
php -l includes\Plugin.php
php -l includes\Admin\SettingsPage.php
php -l includes\Api\Client.php
php -l includes\Api\TrafficReportRepository.php
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
MO ok, entries: 45
```
