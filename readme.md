
# Auto Set Featured Image

**Version:** 1.2  
**Autor:** Dein Name  
**Beschreibung:** Dieses Plugin setzt automatisch das erste Bild eines Beitrags oder einer Seite als Beitragsbild, falls kein Beitragsbild gesetzt wurde. Zusätzlich ermöglicht es das manuelle Durchsuchen aller bestehenden Beiträge und Seiten, um für diese das erste Bild im Inhalt als Beitragsbild festzulegen.

## Funktionen

- Automatisches Setzen des ersten Bildes eines Beitrags oder einer Seite als Beitragsbild beim Speichern, falls noch kein Beitragsbild gesetzt wurde.
- Manuelles Durchsuchen aller vorhandenen Beiträge und Seiten, um das erste Bild als Beitragsbild festzulegen.
- Fortschrittsanzeige während des Prozesses.
- Ergebniszusammenfassung nach dem Prozess: Anzahl der gesetzten Beitragsbilder, bereits vorhandene Beitragsbilder, Beiträge ohne Bilder und eventuelle Fehler.

## Installation

1. Lade den Ordner `auto-featured-image` in dein WordPress-Verzeichnis unter `/wp-content/plugins/` hoch.
2. Gehe zu **Plugins** in deinem WordPress-Adminbereich.
3. Aktiviere das Plugin „Auto Set Featured Image“.

## Verwendung

### Automatisches Setzen eines Beitragsbildes beim Speichern

Wenn du eine Seite oder einen Beitrag erstellst oder aktualisierst, wird das Plugin automatisch nach dem ersten Bild im Inhalt suchen und dieses als Beitragsbild festlegen, wenn noch kein Beitragsbild vorhanden ist.

### Manuelles Setzen des Beitragsbildes für bestehende Beiträge und Seiten

1. Gehe im WordPress-Adminbereich zu **Auto Set Featured Image** (im Hauptmenü).
2. Klicke auf die Schaltfläche **Prozess starten**, um alle Beiträge und Seiten zu durchsuchen.
3. Der Fortschritt des Prozesses wird live angezeigt, während das Plugin die Beiträge bearbeitet.
4. Nach Abschluss erhältst du eine Zusammenfassung über die gesetzten und bereits vorhandenen Beitragsbilder sowie eventuelle Probleme.

### Zusammenfassung der möglichen Statusnachrichten:

- **Beitragsbild erfolgreich gesetzt**: Das erste Bild im Inhalt wurde erfolgreich als Beitragsbild gesetzt.
- **Bereits vorhandenes Beitragsbild**: Der Beitrag oder die Seite hatte bereits ein Beitragsbild.
- **Kein Bild gefunden**: Im Inhalt des Beitrags oder der Seite wurde kein Bild gefunden.
- **Fehler**: Es gab ein Problem beim Setzen des Beitragsbildes.

## Anpassungen

Falls du die Kriterien für die Auswahl des Bildes oder das Verhalten des Plugins ändern möchtest, kannst du den Code in der Datei `auto-featured-image.php` anpassen.

### Beispiel:

Wenn du die Bildgröße ändern möchtest, die als Beitragsbild gesetzt wird, kannst du den entsprechenden Teil in der Funktion `asfi_auto_set_featured_image()` anpassen:

```php
get_the_post_thumbnail(get_the_ID(), 'large');
```

Du kannst den Bildgrößen-Parameter anpassen, um eine andere Größe zu verwenden.

## Weitere Informationen

Dieses Plugin verwendet WordPress' `WP_Query`, um Beiträge und Seiten zu durchlaufen, und `preg_match_all()`, um das erste Bild im Inhalt zu finden und als Beitragsbild zu setzen.
