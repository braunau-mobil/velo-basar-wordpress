# Braunaumobil.at Velobasar Verkaufsabfrage

Erhält den Verkaufsstatus für eine Kunden-ID (accessid) über einen per Token geschützten REST API Endpunkt vom Basar-System.
Siehe [Extending WordPress REST API](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/).

Speichert und updated den erhaltenen Verkaufsstatus für jede Kunden-ID (accessid) in einer Tabelle

Definiert einen Wordpress Shortcode, der das Abfrageformular samt Ergebnissen in einer
Wordpress Seite erzeugt.

Bietet entsprechende Einstellungsmöglichkeiten im WordPress Admin

### Example API Calls:

`curl -i -X GET -H "bm-velobasar-api-token: philtest" "http://127.0.0.1:8080/wp-json/bm/v1/velobasar/test1"`

### Example Shortcode

`[bmvelobasar title="Fahrradbasar Verkaufsabfrage" titletag="h4"]`
