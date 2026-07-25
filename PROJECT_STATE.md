# Qndrs Telraam Inzicht - Projectstate

Laatste update: 2026-07-25

## Korte status

`qndrs-telraam-inzicht` is functioneel MVP-klaar en de eerste WordPress.org review-submission is ingediend.

De plugin toont Telraam verkeersstatistieken via shortcode, gebruikt de WordPress HTTP API voor Telraam API-calls, cached responses met transients, heeft een compacte adminpagina, is internationaliseerbaar opgezet en heeft een geteste Nederlandse vertaling als ontwikkelbestand.

De repository blijft voorlopig private totdat de WordPress.org-review is afgerond.

## Repo

- Lokale repo: `D:\_qndrs\Telraam-plugin\telraam`
- Remote: `https://github.com/Qndrs/telraam.git`
- Branch: `main`
- Laatste gepushte commit: `e3f4997 Prepare WordPress.org MVP submission`
- Huidige werkversie: `0.3.2`
- Werkboom was schoon na commit/push van de MVP/publicatie-afronding.

## Plugin-identiteit

- Pluginnaam: `Qndrs Telraam Inzicht`
- Slug/mapnaam voor distributie: `qndrs-telraam-inzicht`
- Hoofdbestand: `qndrs-telraam-inzicht.php`
- Textdomain: `qndrs-telraam-inzicht`
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
- Telraam S2 nachtverkeer wordt als aparte categorie getoond en niet bij auto's of zwaar verkeer opgeteld.
- Frontend HTML gebruikt gelabelde secties, headings, `<time>`, tabelcaption en veilige escaping.
- Frontend CSS is gescoped onder `.qndrs-telraam-inzicht` en container-responsief.
- Admin CSS is gescoped onder `.qndrs-telraam-admin`.
- Alle zichtbare strings zijn vertaalbaar gemaakt.
- Nederlands (`nl_NL`) is aanwezig als ontwikkelvertaling.

## Geteste situaties

Bevestigd op testomgeving en externe installatie:

- Plugin activeren werkt.
- Plugin deactiveren en opnieuw activeren werkt.
- Token opslaan, wissen en opnieuw opslaan werkt.
- Foutmeldingen bij ontbrekend token werken op frontend en admin.
- Cache wissen werkt.
- Shortcode met standaardtitel werkt.
- Shortcode met `title="..."` toont de aangepaste titel.
- Shortcode met `title=""` verbergt de zichtbare plugin-heading.
- ZIP-installatie van `0.3.2` werkt.
- Plugin Check was groen op de testsite.
- Plugin Check opnieuw gedraaid na publicatie-readme en versie-sync: groen.
- PHP lint op gewijzigde pluginbestanden is groen.

Belangrijk diagnosepunt uit test:

- Verschillende statistieken tussen twee sites bleken veroorzaakt door verschillende segment-ID's (`9000010390` versus `9000010300`), niet door cache of API-afwijking.

## Distributie

- Lokale distributie-ZIP's staan buiten de repo in `D:\_qndrs\Telraam-plugin\pub`
- Laatste gemaakte submission-ZIP: `D:\_qndrs\Telraam-plugin\pub\qndrs-telraam-inzicht.zip`
- ZIP-mapstructuur: `qndrs-telraam-inzicht/`
- ZIP bevat het hoofdpluginbestand.
- ZIP sluit projectdocumentatie en gitdata uit.
- Review-ZIP controle: 22 entries, hoofdpluginbestand aanwezig, `readme.txt` aanwezig, versieheader `0.3.2`, stable tag `0.3.2`, External services-sectie aanwezig.
- `.gitignore` houdt lokale ZIP-bestanden en een eventuele `pub/` map buiten git.
- `.gitattributes` sluit `PROJECT_PLAN.md`, `PROJECT_STATE.md`, `.gitattributes` en lokale release-zips uit bij `git archive`.

De submission-ZIP `qndrs-telraam-inzicht.zip` is ingediend voor WordPress.org review.

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

Nog te doen na submission:

1. Wachten op feedback van het WordPress.org plugin review team.
2. Eventuele reviewfeedback verwerken in GitHub.
3. Bij goedkeuring SVN `trunk` en `tags/{version}` vullen volgens WordPress.org releaseflow.
4. Controleren of screenshots wenselijk zijn voor de WordPress.org pluginpagina.
5. Daarna repository publiek zetten wanneer de review/publicatie stabiel is.

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
