# Qndrs Traffic Display for Telraam - Projectstate

Laatste update: 2026-07-25

## Korte status

`qndrs-traffic-display-for-telraam` is functioneel MVP-klaar. De eerste WordPress.org submission onder de oude naam is gepend; reviewfixes worden nu verwerkt met de nieuwe naam `Qndrs Traffic Display for Telraam`.

De plugin toont Telraam verkeersstatistieken via shortcode, gebruikt de WordPress HTTP API voor Telraam API-calls, cached responses met transients, heeft een compacte adminpagina en is internationaliseerbaar opgezet voor WordPress.org language packs.

De repository blijft voorlopig private totdat de WordPress.org-review is afgerond.

## Repo

- Lokale repo: `D:\_qndrs\Telraam-plugin\telraam`
- Remote: `https://github.com/Qndrs/telraam.git`
- Branch: `main`
- Laatste opgeslagen state-commit vóór deze wijzigingsronde: `6355802 Record WordPress.org submission state`
- Huidige werkversie: `0.3.3`
- Werkboom bevat reviewfixes voor WordPress.org: nieuwe naam/slug/textdomain, verwijderde private Plugin URI en verwijderde `.po/.mo` bestanden.

## Plugin-identiteit

- Pluginnaam: `Qndrs Traffic Display for Telraam`
- Slug/mapnaam voor distributie: `qndrs-traffic-display-for-telraam`
- Hoofdbestand: `qndrs-traffic-display-for-telraam.php`
- Textdomain: `qndrs-traffic-display-for-telraam`
- Licentie: GPL-2.0-or-later
- Minimum PHP: 8.3
- Requires at least: WordPress 6.5
- Tested up to: WordPress 7.0

## MVP-functionaliteit

Aanwezig en getest:

- Admin settings page onder Settings.
- API-token opslaan zonder tokenwaarde opnieuw te tonen.
- API-tokenstatus zichtbaar.
- API-token wissen zonder dummywaarde.
- API-token wissen leegt ook relevante segment-cache.
- Standaard segment-ID, standaard periode en cacheduur instelbaar.
- API-verbinding testen vanuit admin.
- Telraam API-client via `wp_remote_post()`.
- Telraam API rate-limit bescherming: live API-calls binnen één PHP request worden gespreid; HTTP 429 krijgt één retry.
- Transient caching per segment/periode.
- Shortcode `[qndrs_telraam_segment]`.
- Shortcode-attributen:
  - `id`
  - `days`
  - `view`
  - `rows`
  - `title`
- `title=""` verbergt de zichtbare plugin-heading, maar behoudt een screenreader-heading voor toegankelijke structuur.
- Summary-output met voetgangers, tweewielers, auto's, zwaar verkeer, nachtverkeer en uptime.
- Table-output met uurregels.
- Table-output wordt van recent naar oud gesorteerd, zodat de standaard `rows="24"` de meest recente regels toont.
- Telraam S2 nachtverkeer wordt als aparte categorie getoond en niet bij auto's of zwaar verkeer opgeteld.
- Frontend HTML gebruikt gelabelde secties, headings, `<time>`, tabelcaption en veilige escaping.
- Frontend CSS is gescoped onder `.qndrs-traffic-display-for-telraam` en container-responsief.
- Frontend shadow-variabele staat standaard op `none`; latere stijlkeuzes kunnen dezelfde variabele gebruiken voor presets zoals skeleton/light/night.
- Admin CSS is gescoped onder `.qndrs-telraam-admin`.
- Alle zichtbare strings zijn vertaalbaar gemaakt.
- Runtime-vertalingen lopen via WordPress.org language packs; `.po/.mo` bestanden worden niet meegeleverd.
- Voor lokale/testsite-NL is buiten de pluginpackage een language pack geplaatst:
  - `D:\_qndrs\Telraam-plugin\pub\languages\qndrs-traffic-display-for-telraam-nl_NL.po`
  - `D:\_qndrs\Telraam-plugin\pub\languages\qndrs-traffic-display-for-telraam-nl_NL.mo`
  - testsitepad: `wp-content/languages/plugins/qndrs-traffic-display-for-telraam-nl_NL.mo`

## Geteste situaties

Bevestigd op testomgeving en externe installatie:

- Plugin activeren werkt.
- Plugin deactiveren en opnieuw activeren werkt.
- Token opslaan, wissen en opnieuw opslaan werkt.
- Foutmeldingen bij ontbrekend token werken op frontend en admin.
- Cache wissen werkt.
- Shortcode met standaardtitel werkt.
- Standaard zichtbare shortcode-heading is voor reviewveiligheid gewijzigd naar `Traffic data`.
- Shortcode met `title="..."` toont de aangepaste titel.
- Shortcode met `title=""` verbergt de zichtbare plugin-heading.
- Tabelsortering recent naar oud is gedeployed naar de testsite en via WP-CLI gecontroleerd.
- Frontend shadow-default `--qndrs-telraam-shadow: none` is gedeployed naar de testsite.
- ZIP-installatie van `0.3.2` werkte vóór de reviewfixes.
- Plugin Check was groen op de testsite.
- Plugin Check opnieuw gedraaid na publicatie-readme en versie-sync: groen.
- Plugin Check opnieuw gedraaid op de hernoemde plugin `qndrs-traffic-display-for-telraam`: groen.
- Handmatige NL language pack voor de nieuwe textdomain getest op de testsite: `Traffic totals` wordt `Verkeerstotalen`, `Traffic data` wordt `Verkeersdata`.
- PHP lint op gewijzigde pluginbestanden is groen.

Belangrijk diagnosepunt uit test:

- Verschillende statistieken tussen twee sites bleken veroorzaakt door verschillende segment-ID's (`9000010390` versus `9000010300`), niet door cache of API-afwijking.

## Distributie

- Lokale distributie-ZIP's staan buiten de repo in `D:\_qndrs\Telraam-plugin\pub`
- Nieuwe submission-ZIP is gemaakt als `D:\_qndrs\Telraam-plugin\pub\qndrs-traffic-display-for-telraam.zip`
- ZIP-mapstructuur: `qndrs-traffic-display-for-telraam/`
- ZIP bevat het hoofdpluginbestand.
- ZIP sluit projectdocumentatie en gitdata uit.
- Nieuwe review-ZIP is gemaakt: 20 entries, rootmap `qndrs-traffic-display-for-telraam/`, hoofdpluginbestand aanwezig, `readme.txt` aanwezig, geen `.po/.mo`, geen projectdocs/gitdata, versieheader `0.3.3`, stable tag `0.3.3`, textdomain `qndrs-traffic-display-for-telraam`, geen `Plugin URI`, External services-sectie aanwezig.
- `Plugin URI` is bewust weggelaten; `Author URI` blijft `https://qndrs.nl`, zodat plugin- en author-URI niet gelijk zijn.
- `.gitignore` houdt lokale ZIP-bestanden en een eventuele `pub/` map buiten git.
- `.gitattributes` sluit `PROJECT_PLAN.md`, `PROJECT_STATE.md`, `.gitattributes` en lokale release-zips uit bij `git archive`.

De nieuwe gewenste permalink voor reply aan WordPress.org is `qndrs-traffic-display-for-telraam`.

## WordPress.org-publicatievoorbereiding

Gebruikte referenties:

- WordPress Plugin Readmes: https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/
- Detailed Plugin Guidelines: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- Common Plugin Review Issues: https://developer.wordpress.org/plugins/wordpress-org/common-issues/
- Bestaande Qndrs-plugin als referentie: https://wordpress.org/plugins/qndrs-availability-heartbeat-monitor/

Relevante publicatiepunten:

- `readme.txt` moet de WordPress.org readme-structuur volgen.
- `Stable tag` moet overeenkomen met de pluginversie.
- De pluginversie in het hoofd-PHP-bestand bepaalt de downloadversie op WordPress.org.
- Externe services moeten duidelijk worden gedocumenteerd.
- De plugin moet uitleggen wanneer Telraam wordt aangeroepen, welke data wordt verstuurd en waar de voorwaarden/privacyinformatie staan.
- Geen API-tokens, persoonlijke testdata of deployment-toegang opnemen.
- Geen onnodige ontwikkelbestanden in de distributie-ZIP.
- WordPress.org SVN is releasekanaal; alleen releaseklare commits daarheen pushen.
- Runtime-vertalingen voor publicatie bij voorkeur via WordPress.org language packs laten lopen.

Publicatie-readiness:

- GPL-2.0-or-later staat consistent in header, readme en `LICENSE`.
- Plugin slug is onderscheidend met Qndrs-prefix.
- Plugin gebruikt WordPress HTTP API.
- Plugin gebruikt WordPress enqueue voor CSS.
- Plugin heeft directe bestandsaccess guards.
- Plugin gebruikt gescopete prefixes/classes.
- `readme.txt` bevat nu een expliciete `External services`-sectie voor Telraam.
- Telraam is als derde partij gedocumenteerd met service-, API-, terms-, privacy- en datalicentielinks.

Nog te doen voor de volgende reviewronde:

1. Nieuwe ZIP uploaden via de WordPress.org Add Your Plugin-pagina.
2. Reply sturen in dezelfde reviewmail met gewenste permalink `qndrs-traffic-display-for-telraam`.
3. Bij goedkeuring SVN `trunk` en `tags/{version}` vullen volgens WordPress.org releaseflow.

## Telraam-documentatie die relevant blijft

- Telraam: https://telraam.net/
- Telraam API documentatie: https://faq.telraam.net/en/category/2/data-interpretation-and-the-telraam-api
- Telraam API tokeninformatie: https://faq.telraam.net/article/397/api-token-information-update
- Telraam data license: https://faq.telraam.net/en/article/9/telraam-data-license-what-can-i-do-with-the-telraam-data
- Telraam terms of use: https://telraam.net/en/terms-of-use
- Telraam privacy policy: https://telraam.net/en/privacy-policy

## Bewuste keuzes

- Geen Composer-dependency in de MVP.
- Geen PSR-7 dependency; WordPress HTTP API is voldoende.
- Geen `load_plugin_textdomain()` call; WordPress.org language packs krijgen voorrang.
- `.po/.mo` bestanden blijven buiten de WordPress.org review-ZIP; lokale taaltests gebruiken `wp-content/languages/plugins/`.
- Shortcode-first; Gutenberg block pas later.
- Uptime blijft een kleine datakwaliteitsindicator, niet een hoofdstatistiek.
- Nachtverkeer is aparte Telraam S2-categorie.
- "Laatste dag" betekent nu rolling 24 uur vanaf het API-requestmoment, niet kalenderdag.

## Backlog na MVP/publicatie

- Shortcode builder in admin.
- Publieke Telraam locatie-URL als input ondersteunen.
- Dagelijkse aggregates/snapshots opslaan voor trends en regressiegrafieken.
- Zonsopkomst, zonsondergang en seizoen koppelen aan dag-/nachtverkeer.
- Fun styling of style presets optioneel toevoegen.
- Grafieken/infographics.
- Meerdere segmenten en segmentlabels.
- Mogelijke Gutenberg block-variant.
