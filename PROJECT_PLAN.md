# Qndrs Traffic Display for Telraam - Projectplan

## Doel

Dit project bouwt de WordPress-plugin `qndrs-traffic-display-for-telraam`, waarmee Telraam-statistieken op een WordPress-website getoond kunnen worden.

De plugin haalt verkeersdata op uit de Telraam API, cached die data binnen WordPress, en toont begrijpelijke statistieken via shortcodes en later eventueel Gutenberg blocks.

Eerste concrete testcase:

- Telraam segment-ID: `9000010390`
- Publieke pagina: <https://telraam.net/nl/location/9000010390>

## Technische uitgangspunten

- Platform: WordPress plugin
- PHP-versie: PHP 8.3+
- Pluginlicentie: GPL-2.0-or-later
- WordPress HTTP API gebruiken voor externe requests
- Geen PSR-7 dependency nodig voor de eerste versie
- Geen Composer-verplichting in de MVP, tenzij later nuttig
- WordPress transients gebruiken voor caching
- WordPress coding standards volgen
- Alle frontend-output escapen
- API-token nooit op de frontend tonen
- Plugin volledig internationaliseerbaar maken
- Brontaal/fallback: Engels
- Translation-ready voor WordPress.org language packs; geen `.po`/`.mo` in de review-ZIP

## Telraam API uitgangspunten

De plugin gebruikt in eerste instantie:

- Endpoint: `https://telraam-api.net/v1/reports/traffic`
- Methode: `POST`
- Header: `X-Api-Key`
- Header: `Content-Type: application/json`
- Standaard `level`: `segments`
- Standaard `format`: `per-hour`

Voorbeeld request-body:

```json
{
  "id": "9000010390",
  "time_start": "2026-07-17 00:00:00Z",
  "time_end": "2026-07-24 00:00:00Z",
  "level": "segments",
  "format": "per-hour"
}
```

Belangrijke API-beperkingen:

- Rate limit: 1 request per seconde
- Burst: 1 request
- Quota: 1000 requests per dag voor gewone API-gebruikers
- Traffic report periodes mogen maximaal 3 maanden per request beslaan

Caching is daarom een kernonderdeel van de plugin, geen optimalisatie achteraf.

## MVP scope

De eerste versie moet klein, testbaar en bruikbaar zijn.

### 1. Pluginstructuur

Voorgestelde structuur:

```text
qndrs-traffic-display-for-telraam.php
includes/
  Api/
    Client.php
  Admin/
    SettingsPage.php
  Frontend/
    Shortcodes.php
assets/
  css/
  js/
languages/
  qndrs-traffic-display-for-telraam.pot
```

### 2. Admin instellingen

Adminpagina onder WordPress beheer:

- API-token instellen
- Duidelijke tokenstatus tonen zonder het token zelf te tonen, bijvoorbeeld "API-token opgeslagen"
- Standaard segment-ID instellen
- Standaard periode instellen, bijvoorbeeld laatste 7 dagen
- Cacheduur instellen, standaard 60 minuten
- Knop: API-verbinding testen
- Knop: cache legen

Voorgestelde standaardwaarden:

- Segment-ID: `9000010390`
- Periode: 7 dagen
- Cacheduur: 60 minuten

### 3. API client

De API client moet:

- requests uitvoeren via `wp_remote_post()`
- API-token meesturen via `X-Api-Key`
- JSON-requestbody opbouwen
- JSON-response valideren
- WordPress errors correct afhandelen
- duidelijke foutmeldingen teruggeven aan admin/frontend
- geen secrets loggen of tonen

### 4. Caching

Caching via transients:

- cache-key gebaseerd op segment-ID, periode, level en format
- standaard cacheduur 1 uur
- handmatig cache legen via admin
- toekomstige uitbreiding naar cron-refresh mogelijk

### 5. Frontend shortcode

Eerste shortcode:

```text
[qndrs_telraam_segment]
[qndrs_telraam_segment id="9000010390" days="7"]
[qndrs_telraam_segment id="9000010390" view="summary"]
```

Gedrag:

- Zonder `id` gebruikt de shortcode het standaard segment uit de settings
- Zonder `days` gebruikt de shortcode de standaard periode uit de settings
- Zonder `view` toont de shortcode een compacte samenvatting
- Een shortcode builder in de admin wordt later toegevoegd, zodat beheerders de juiste shortcode kunnen samenstellen zonder attributen handmatig te typen

Toekomstige inputoptie:

```text
[qndrs_telraam_segment url="https://telraam.net/nl/location/9000010390"]
```

De plugin moet dan het segment-ID uit de publieke Telraam locatie-URL halen en dezelfde statistieken tonen alsof `id="9000010390"` was opgegeven.

Ondersteunde URL-vorm voor de eerste implementatie:

```text
https://telraam.net/{locale}/location/{segment_id}
```

Voorbeeld:

```text
https://telraam.net/nl/location/9000010390
```

### 6. Eerste frontend-output

De MVP toont:

- Segment-ID
- Periode
- Totaal voetgangers
- Totaal tweewielers
- Totaal auto's
- Totaal zwaar verkeer
- Gemiddelde uptime
- Eenvoudige tabel met uur- of dagtotalen

### 7. Internationalisatie

De plugin wordt vanaf de MVP tweetalig opgezet.

Uitgangspunten:

- Alle zichtbare teksten worden vertaalbaar gemaakt
- Plugin slug: `qndrs-traffic-display-for-telraam`
- Textdomain: `qndrs-traffic-display-for-telraam`
- Brontaal/fallback: Engels
- Geen meegeleverde `.po`/`.mo` in de WordPress.org package; vertalingen lopen via translate.wordpress.org/language packs.
- POT-template in `languages/`
- Plugin header bevat `Text Domain: qndrs-traffic-display-for-telraam` en `Domain Path: /languages`
- Geen hardcoded Nederlandse of Engelse frontend/admin-strings zonder vertaalfunctie

WordPress functies:

- `__()`
- `_e()`
- `esc_html__()`
- `esc_attr__()`
- `esc_html_e()`
- `esc_attr_e()`

Te vermijden:

- samengestelde zinnen die moeilijk vertaalbaar zijn
- vertaalbare strings met HTML waar dat niet nodig is
- onduidelijke placeholders

Voorbeeld:

```php
sprintf(
    esc_html__( 'Showing traffic data for segment %s.', 'qndrs-traffic-display-for-telraam' ),
    esc_html( $segment_id )
);
```

## Versie 1.1: presentatie

Na de MVP:

- Grafieken toevoegen
- Dagtotalen tonen
- Uurverdeling tonen
- Modal split tonen
- Richting A -> B en B -> A tonen
- Uitleg bij uptime/databetrouwbaarheid tonen
- Waarschuwing bij lage uptime, bijvoorbeeld onder `0.5`

Mogelijke grafiekbibliotheek:

- Chart.js

Besluit later nemen op basis van bundlegrootte, onderhoud en frontend-eisen.

## Versie 1.2: Telraam interpretatielaag

Telraam-data vereist domeinlogica. Deze versie voegt interpretatie toe:

- API-richtingen `lft` en `rgt` vertalen naar A -> B en B -> A
- Snelheidsdata tonen
- V85 tonen
- Nachtcounts tonen voor Telraam S2
- Klassieke Telraam-categorieën ondersteunen:
  - pedestrians
  - two-wheelers
  - cars
  - heavy vehicles
- Telraam S2 modaliteiten voorbereiden:
  - bicycle
  - bus
  - car
  - light truck
  - motorcycle
  - pedestrian
  - stroller
  - tractor
  - trailer
  - truck

## Versie 2: meerdere segmenten en dashboards

Uitbreidingen:

- Meerdere segmenten beheren
- Segmenten labels geven
- Dashboard shortcode:

```text
[qndrs_telraam_dashboard]
```

- Segmenten vergelijken
- Periodes vergelijken
- CSV-export
- Cron-refresh
- Mogelijk Gutenberg block
- Publieke Telraam locatie-URL kunnen gebruiken in plaats van handmatig segment-ID

## Beveiliging en privacy

De plugin moet:

- API-token opslaan in WordPress options
- adminformulieren beveiligen met nonces
- capability checks toepassen, bijvoorbeeld `manage_options`
- frontend-output escapen
- API-token nooit tonen aan bezoekers
- foutmeldingen zonder secrets tonen

## WordPress Plugin Check

De testsite heeft Plugin Check geïnstalleerd.

Acceptatiecriteria:

- Plugin activeert zonder fatal errors
- Plugin Check geeft geen kritieke fouten
- PHP syntax checks draaien tegen PHP 8.4
- Escaping, sanitization, nonces en capability checks zijn aantoonbaar verwerkt
- Internationalisatie wordt meegenomen in alle zichtbare strings

## WordPress.org documentatie en publicatiechecklist

Voor een latere publieke release moet de plugin voldoen aan de WordPress.org plugin directory regels en readme-standaard.

Nog toe te voegen vóór publicatie:

- `readme.txt` volgens de WordPress.org plugin readme standaard
- korte pluginbeschrijving van maximaal 150 tekens
- contributors als WordPress.org gebruikersnamen
- tags, bij voorkeur 1 tot 5 relevante algemene tags
- `Requires at least`
- `Tested up to`
- `Requires PHP`
- `Stable tag`
- `License`
- `License URI`
- installatie-instructies
- configuratie-instructies voor Telraam API-token
- shortcodevoorbeelden
- FAQ, inclusief Telraam API-token en datalicentie/gebruik
- changelog
- eventuele privacy/disclaimertekst over externe API-calls naar Telraam

Regels om expliciet te bewaken:

- Main plugin header blijft de bron voor pluginnaam, versie en runtime requirements
- `Stable tag` moet overeenkomen met de pluginversie wanneer er een officiële release wordt gemaakt
- Nieuwe WordPress.org plugins moeten geen `Stable Tag: trunk` gebruiken
- Pluginmap bij distributie: `qndrs-traffic-display-for-telraam`
- Alle code, assets en dependencies moeten GPLv2-or-later compatibel zijn
- Geen tracking of externe API-calls zonder duidelijke uitleg en functionele noodzaak
- Geen API-token, secrets of persoonlijke testdata in repository, logs, screenshots of readme
- Geen merk- of naamgebruik dat Telraam of WordPress endorsement suggereert

Te gebruiken officiële referenties:

- <https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/>
- <https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/>
- <https://developer.wordpress.org/plugins/wordpress-org/common-issues/>

## Licentie en gebruiksvoorwaarden

De plugin moet in documentatie vermelden:

- Telraam API-gebruik vereist een geldige API-token
- API-gebruikers zijn zelf verantwoordelijk voor naleving van Telraam voorwaarden
- Telraam API-data valt onder licentievoorwaarden, inclusief CC-BY-NC tenzij apart overeengekomen
- Commercieel gebruik van Telraam-data kan aparte toestemming/licentie vereisen

## Belangrijkste risico's

- Zonder geldige API-token kan echte data niet volledig getest worden
- API-limieten vereisen goede caching
- Verkeerde interpretatie van `lft` / `rgt` kan misleidende richtinginformatie geven
- Lage uptime kan data minder betrouwbaar maken
- Telraam API-responses kunnen velden bevatten die per device/type/periode verschillen

## Eerste implementatiestappen

1. Plugin bootstrap maken in `qndrs-traffic-display-for-telraam.php`
2. Admin settings page maken
3. API client maken
4. Transient caching toevoegen
5. Shortcode `[qndrs_telraam_segment]` maken
6. Output als samenvatting en eenvoudige tabel tonen
7. Testen met segment `9000010390`
8. Daarna grafieken toevoegen

## Actuele follow-up punten

- De admin settings page toont nu expliciet of er een API-token opgeslagen is, zonder de tokenwaarde te tonen.
- De admin settings page heeft nu een API-verbindingstest voor het opgeslagen token en standaardsegment.
- De frontend shortcode gebruikt nu een aparte traffic report normalizer in plaats van ruwe API-velden rechtstreeks te interpreteren.
- De frontend shortcode gebruikt nu toegankelijkere HTML met gelabelde secties, summary-heading, table caption, row headers en alert-role voor foutmeldingen.
- Shortcode-output handelt enkelvoud/meervoud af: bij `days="1"` toont de tekst "laatste dag".
- Admin settings page heeft nu een expliciete actie om het opgeslagen API-token te wissen zonder een dummywaarde te hoeven invullen.
- Admin settings page is visueel opgeschoond: API-testen en API-token wissen staan bij het API-token veld; cache wissen staat bij het cacheduur veld; layout is compacter en horizontaler.
- API-token wissen moet een expliciete clear-route gebruiken, zodat de settings sanitizer het oude token niet behoudt; tegelijk wordt segment-cache voor alle ondersteunde periodes gewist.
- Frontend-layout prioriteert nu inhoudelijk: verkeerstellingen zijn primair; uptime is een kleine datakwaliteitsindicatie, geen gelijkwaardige hoofdstatistiek.
- Grote uur-tabel voorlopig niet pagineren. De table view ondersteunt nu een `rows` attribuut (`rows="24"` standaard, `rows="all"` voor alles) en volledige uurdata blijft een detailweergave.
- Frontend-output moet netjes renderen zonder template-inspringing in de HTML-output; timestamps moeten als leesbare lokale datum/tijd met machineleesbaar `<time datetime="">` worden getoond.
- Frontend basisstijl gebruikt gescopete CSS onder `.qndrs-traffic-display-for-telraam`, met CSS-variabelen voor latere theming.
- Frontend summary cards moeten container-responsief blijven, zodat shortcodes ook in sidebars bruikbaar blijven.
- Brede tegels moeten weer over de volledige beschikbare breedte vullen; smalle containers gebruiken expliciete container-breakpoints naar 3, 2 en 1 kolom.
- Frontend labels moeten kort genoeg blijven voor sidebarplaatsing; huidige korte labels: "Telraam" en "Uptime".
- Telraam API rate limiting moet rekening houden met meerdere shortcodes op één koude-cache pagina; live API-calls worden binnen één PHP request gespreid en 429 krijgt één retry.
- Telraam S2 nachtverkeer moet als aparte categorie worden getoond; niet optellen bij auto's of zwaar verkeer.
- Later verkeer en nachtverkeer relateren aan zonsopkomst, zonsondergang en seizoen voor trends en infographics.
- Shortcode ondersteunt een `title` attribuut; met `title=""` wordt de plugin-heading visueel verborgen wanneer de site-eigenaar zelf een titel in de editor plaatst.
- Mobiele tabeltitel moet buiten de horizontaal scrollende tabel blijven; native `<caption>` blijft beschikbaar voor screenreaders.
- Admin basisstijl gebruikt gescopete CSS onder `.qndrs-telraam-admin`, met kaartlayout, veldgroepen en statusbadge.
- Fun styling komt pas na de neutrale basisroute en moet optioneel blijven, bijvoorbeeld via style presets.
- Toekomstige dataopslag: dagelijkse Telraam-aggregates/snapshots opslaan in WordPress, los van transient cache, zodat regressiegrafieken, trends en periodevergelijkingen mogelijk worden.

## Toekomstige trend- en regressielaag

De huidige transient cache is bedoeld om API-limieten te beschermen, niet als historische databron.

Voor trendanalyse is later een aparte opslaglaag nodig:

- dagelijkse aggregates per segment opslaan
- totalen per modaliteit bewaren
- gemiddelde/minimale uptime bewaren als datakwaliteitsindicator
- datum, segment-ID en eventueel richting bewaren
- periodieke WP-Cron import/sync toevoegen
- ontbrekende dagen kunnen backfillen binnen Telraam API-limieten
- regressiegrafieken en trends baseren op deze lokale dagdata
- onderscheid maken tussen ruwe API-cache en duurzame geaggregeerde statistieken
- zonsopkomst/zonsondergang en seizoen koppelen aan dag- en nachtverkeer voor contextuele trendanalyse en infographics

Mogelijke opslagopties:

- custom database table voor schaalbaarheid en queries
- custom post type is minder geschikt voor tijdreeksen
- options/transients niet gebruiken voor duurzame trenddata
