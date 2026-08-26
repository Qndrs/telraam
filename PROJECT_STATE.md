# Qndrs Traffic Display for Telraam - Projectstate

Laatste update: 2026-08-26

## Korte status

`qndrs-traffic-display-for-telraam` is functioneel MVP-klaar en versie `0.3.5` is succesvol gepubliceerd op WordPress.org. De releasebron staat in Git-commit `c5fc0d283f402ac06d1ebfe3f5e033a36e683288` en tag `v0.3.5`; SVN `trunk` is bijgewerkt in revisie `3666106` en de schone release-tag `0.3.5` met 20 bestanden is gepubliceerd in revisie `3666108`. De publieke WordPress.org-API toont versie `0.3.5`, `Tested up to: 7.1` en de nieuwe download. De Nederlandse Stable Readme-set staat nog op `74/74`, maar de nieuwe changelogregel voor `0.3.5` verschijnt voorlopig in het Engels. Van de `63` Stable-runtimevertalingen zijn `58` goedgekeurd en staan `5` op `Waiting`; het officiële Nederlandse runtime-languagepack is nog alleen voor versie `0.3.4` beschikbaar.

De plugin toont Telraam verkeersstatistieken via shortcode, gebruikt de WordPress HTTP API voor Telraam API-calls, cached responses met transients, heeft een compacte adminpagina en is internationaliseerbaar opgezet voor WordPress.org language packs.

De GitHub-repository `Qndrs/telraam` is publiek; de release op WordPress.org en de Nederlandse vertaling zijn eveneens publiek beschikbaar.

## Repo

- Lokale repo: `D:\_qndrs\Telraam-plugin\telraam`
- Remote: `https://github.com/Qndrs/telraam.git`
- Branch: `main`
- Releasebron: `c5fc0d283f402ac06d1ebfe3f5e033a36e683288 Release version 0.3.5`; Git-tag `v0.3.5`
- Huidige werkversie: `0.3.5` (gepubliceerd)
- GitHub `main` en de dereferenced tag `v0.3.5` wijzen naar dezelfde releasebroncommit.
- De werkboom bevat twee ongetrackte mappen: `includes/Api/smb/` en `includes/Api/streams/`. Deze zijn lokaal aangemaakt op 27 juli 2026, na de release-ZIP van 25 juli 2026, en zitten niet in die ZIP. Herkomst en beoogd gebruik zijn niet vastgesteld; niet committen, verwijderen of in een nieuwe distributie opnemen voordat dit is beoordeeld.
- Tijdens de preflight voor `0.3.5` bleken oude PSR-log-testbestanden en Bootstrap-assets per ongeluk onder `.git/refs/Test` en `.git/objects/80` te staan. De vijf vervuilde bronpaden zijn zonder verwijdering verplaatst naar `D:\_qndrs\Telraam-plugin\git-recovery-quarantine-20260826`; daarna is `git fsck --full --no-reflogs` zonder fouten afgerond en werkt Git-refverwerking weer normaal.

## Projectadministratie

- De centrale projectvermelding in `%ROBERT_AI_HOME%\PROJECTS.md` is op 26 augustus 2026 bijgewerkt met release `0.3.5`, WordPress 7.1-compatibiliteit en de resterende languagepack-/vertaalactie.
- Deze `PROJECT_STATE.md` blijft de lokale bron voor gedetailleerde status; de centrale projectindex bevat alleen de compacte status, focus en eerstvolgende stap.

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
- De menselijk beoordeelde Stable Readme-importbron staat buiten de pluginpackage als `D:\_qndrs\Telraam-plugin\pub\languages\wp-plugins-qndrs-traffic-display-for-telraam-stable-readme-nl.po`.

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
- PHP lint op alle 15 tracked PHP-bestanden is voor versie `0.3.4` groen met PHP `8.4.22`.
- Alle External services-URL's zijn op 17 augustus 2026 met redirects gecontroleerd en geven HTTP `200`; de nieuwe datalicentie-overzichtspagina staat op het officiële Telraam-hoofddomein.
- De versie-`0.3.4`-ZIP is inhoudelijk gecontroleerd: 20 bestanden, slash-genormaliseerde paden, consistente versieheader/stable tag/POT-versie, geen oude gemelde URL en geen uitgesloten of ongetrackte bestanden.
- Versie `0.3.4` is op `https://qndrs.training/telraam/` geactiveerd en gecontroleerd met WordPress `7.0.4`, PHP `8.4.24` en `WP_DEBUG=true`; na verwijdering van de oude pluginmap is een volledige deactiveer-/activeercyclus opnieuw groen uitgevoerd, Plugin Check is groen, de shortcode is geregistreerd en de frontend geeft HTTP `200`.
- Op 26 augustus 2026 is bevestigd dat de plugin op `https://qndrs.training/telraam/` werkt met WordPress `7.1`. De eerste Plugin Check gaf uitsluitend `outdated_tested_upto_header`, omdat `Tested up to: 7.0` lager was dan de actuele WordPress-versie `7.1`. Na installatie van releasecandidate `0.3.5` met `Tested up to: 7.1` is Plugin Check opnieuw uitgevoerd en afgerond zonder fouten.
- De Qndrs-iconen van 128 en 256 pixels zijn als afzonderlijke WordPress.org-assetcommit gepubliceerd in SVN-revisie `3658826`; beide iconen worden via het WordPress.org-CDN met HTTP `200` geserveerd en zijn met SHA-256 gecontroleerd tegen de centrale bron.

Belangrijk diagnosepunt uit test:

- Verschillende statistieken tussen twee sites bleken veroorzaakt door verschillende segment-ID's (`9000010390` versus `9000010300`), niet door cache of API-afwijking.
- De eerste activatie van de hernoemde plugin gaf een fatale `Cannot redeclare function qndrs_telraam_inzicht_activate()`, omdat de oude testplugin `qndrs-telraam-inzicht` versie `0.3.2` nog actief was en dezelfde globale functies/classes laadde. De oude plugin is gedeactiveerd en daarna volledig verwijderd; versie `0.3.4` activeert vervolgens zonder fout.

## Distributie

- Lokale distributie-ZIP's staan buiten de repo in `D:\_qndrs\Telraam-plugin\pub`
- De gecorrigeerde review-ZIP staat voor upload zonder versienummer als `D:\_qndrs\Telraam-plugin\pub\qndrs-traffic-display-for-telraam.zip`; een identieke lokale reserve staat als `qndrs-traffic-display-for-telraam-0.3.4.zip` (SHA-256 `7a7f1051b62fa0339d04237f8f41a825595900c64097118807684d2e4527936d`).
- De lokale testkandidaat voor metadata-patchrelease `0.3.5` staat als `D:\_qndrs\Telraam-plugin\pub\qndrs-traffic-display-for-telraam-0.3.5-rc.zip` (20 bestanden, één correcte pluginroot, slash-genormaliseerde ZIP-paden, SHA-256 `951ad9afa196ac1027acf6c9d2475258ff3da2516d463e6ba490d6a07b67b5f4`). Deze kandidaat is op de WordPress 7.1-testsite door Plugin Check gecontroleerd zonder fouten en is nog niet gepubliceerd.
- De publieke WordPress.org-ZIP voor `0.3.5` bevat exact dezelfde 20 bestanden als SVN-tag `0.3.5`, zonder uitgesloten projectdocs, gitdata of onbekende API-mappen en zonder hashverschillen per bestand. Publieke ZIP-SHA-256: `e2da975386d37651487d1e6f3e06d18b9a6f72e16becb03dce51b63938128111`.
- ZIP-mapstructuur: `qndrs-traffic-display-for-telraam/`
- ZIP bevat het hoofdpluginbestand.
- ZIP sluit projectdocumentatie en gitdata uit.
- De review-ZIP bevat 20 bestanden met rootmap `qndrs-traffic-display-for-telraam/`, hoofdpluginbestand en `readme.txt`, zonder `.po/.mo`, projectdocs/gitdata of de ongetrackte API-mappen; versieheader, stable tag en POT-versie zijn `0.3.4`.
- `Plugin URI` is bewust weggelaten; `Author URI` blijft `https://qndrs.nl`, zodat plugin- en author-URI niet gelijk zijn.
- `.gitignore` houdt lokale ZIP-bestanden en een eventuele `pub/` map buiten git.
- `.gitattributes` sluit `PROJECT_PLAN.md`, `PROJECT_STATE.md`, `.gitattributes` en lokale release-zips uit bij `git archive`.

De nieuwe gewenste permalink voor reply aan WordPress.org is `qndrs-traffic-display-for-telraam`.

## WordPress.org-publicatie

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

Actuele publicatiestatus:

1. WordPress.org bevestigde dat de commitrechten aan de hoofdlettergevoelige gebruikersnaam `Qndrs` zijn gekoppeld; hiermee is de eerdere `Access denied`-blokkade opgelost.
2. De goedgekeurde versie `0.3.4` is in SVN `trunk` gepubliceerd onder revisie `3658249`.
3. Vanuit de schone checkout `D:\_qndrs\Telraam-plugin\wordpress-org-svn-clean` is `trunk` gekopieerd naar `tags/0.3.4`; de tag bevat exact 20 bestanden en is gepubliceerd onder revisie `3658268`.
4. De publieke pluginpagina is zichtbaar op `https://wordpress.org/plugins/qndrs-traffic-display-for-telraam/`. De Qndrs-iconen zijn in SVN-revisie `3658826` gepubliceerd; het CDN-bestand is technisch geverifieerd. Controleer na de CDN-cacheverwerking ook de visuele weergave op de pluginpagina.
5. Na publicatie van `0.3.5` staat Stable runtime op `Translated 58`, `Waiting 5`, `Untranslated 0`, `Changes requested 0`, `Fuzzy 0` en `Warnings 0`. Development runtime staat op `Translated 58`, `Untranslated 5` en verder overal `0`. De translation-API levert nog alleen het Nederlandse languagepack voor `0.3.4`; het pakket voor `0.3.5` is nog niet gegenereerd.
6. Stable Readme en Development Readme staan beide op `Translated 74`, met alle overige statussen op `0`. De nieuwe changelogregel `Confirmed compatibility with WordPress 7.1.` verschijnt op de Nederlandse pluginpagina nog in het Engels en is nog niet als nieuwe Readme-string in de telling zichtbaar.
7. Metadata-patchrelease `0.3.5` is gepubliceerd vanuit Git-commit `c5fc0d2` en Git-tag `v0.3.5`. WordPress.org SVN `trunk` staat in revisie `3666106`, tag `0.3.5` in revisie `3666108`; de publieke API en pluginpagina tonen versie `0.3.5`, Tested up to `7.1` en de nieuwe changelog. De publieke ZIP is beschikbaar en exact tegen de SVN-tag gevalideerd.
8. Laat de vijf wachtende Stable-runtimevertalingen goedkeuren, vertaal de nieuwe `0.3.5`-changelogregel zodra WordPress.org die in Stable Readme exposeert en controleer daarna generatie van het Nederlandse `0.3.5`-languagepack.
9. Plaats na de resterende publieke controles een introductiebericht over de plugin in de Telraam-community.
10. Gebruik voor toekomstig SVN-onderhoud de schone checkout. De oudere map `D:\_qndrs\Telraam-plugin\wordpress-org-svn` bevat lokale, onversioned CakePHP-bestanden onder `tags/0.3.4` en mag niet met brede toevoegcommando's worden gebruikt.

## Telraam-documentatie die relevant blijft

- Telraam: https://telraam.net/
- Telraam API documentatie: https://faq.telraam.net/en/category/2/data-interpretation-and-the-telraam-api
- Telraam API tokeninformatie: https://faq.telraam.net/article/397/api-token-information-update
- Telraam data licensing overview: https://telraam.net/en/network
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
