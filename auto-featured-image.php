<?php
/*
Plugin Name: Auto Set Featured Image
Plugin URI: https://torbenb.info/download/
Description: Dieses Plugin setzt automatisch das erste Bild einer Seite oder eines Beitrags als Beitragsbild, falls kein Beitragsbild gesetzt wurde.
Version: 1.2
Author: TorbenB
Author URI: https://torbenb.info/
*/

// Sicherheit: Blockiere den direkten Aufruf dieser Datei
if (!defined('ABSPATH')) {
    exit;
}

// Funktion, die das erste Bild im Inhalt als Beitragsbild setzt
function asfi_auto_set_featured_image($post_id) {
    // Prüfe, ob es sich um einen automatischen Speichervorgang handelt
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return false;
    }

    // Stelle sicher, dass der Beitragstyp entweder "post" oder "page" ist
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, ['post', 'page'])) {
        return false;
    }

    // Überprüfe, ob der Beitrag bereits ein Beitragsbild hat
    if (has_post_thumbnail($post_id)) {
        return 'already_set';
    }

    // Hole den Inhalt des Beitrags
    $post = get_post($post_id);
    $content = $post->post_content;

    // Suche nach dem ersten Bild im Inhalt des Beitrags
    $first_img = '';
    preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);

    if (isset($matches[1][0])) {
        $first_img = $matches[1][0];
    }

    // Wenn ein Bild gefunden wurde, lade es herunter und setze es als Beitragsbild
    if (!empty($first_img)) {
        // Bild in die Medienbibliothek hochladen und als Beitragsbild festlegen
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($first_img);
        $filename = basename($first_img);

        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);

        set_post_thumbnail($post_id, $attach_id);

        return 'set';
    }

    // Wenn kein Bild gefunden wurde
    return 'no_image';
}

// Admin-Menüseite hinzufügen, um den Prozess manuell zu starten
function asfi_register_admin_menu() {
    add_menu_page(
        'Auto Set Featured Image',      // Seiten-Titel
        'Auto Set Featured Image',      // Menü-Titel
        'manage_options',               // Berechtigung
        'asfi-settings',                // Menü-Slug
        'asfi_admin_page',              // Callback-Funktion zur Anzeige der Seite
        'dashicons-format-image',       // Icon
        20                              // Position im Admin-Menü
    );
}
add_action('admin_menu', 'asfi_register_admin_menu');

// Callback-Funktion für die Admin-Seite
function asfi_admin_page() {
    ?>
    <div class="wrap">
        <h1>Auto Set Featured Image</h1>
        <p>Durchsuchen Sie alle bestehenden Beiträge und Seiten und setzen Sie automatisch das erste Bild als Beitragsbild, falls noch kein Beitragsbild vorhanden ist.</p>
        <form method="post">
            <input type="hidden" name="asfi_run" value="1">
            <?php submit_button('Prozess starten'); ?>
        </form>
    </div>
    <?php

    // Prozess starten, wenn das Formular abgesendet wurde
    if (isset($_POST['asfi_run']) && $_POST['asfi_run'] == '1') {
        asfi_process_all_posts();
    }
}

// Funktion zum Durchlaufen aller Beiträge und Seiten
function asfi_process_all_posts() {
    // WP_Query, um alle Beiträge und Seiten zu erhalten
    $args = array(
        'post_type' => array('post', 'page'),
        'posts_per_page' => -1, // Alle Beiträge holen
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    // Statusvariablen
    $total_posts = $query->found_posts;
    $set_count = 0;
    $already_set_count = 0;
    $no_image_count = 0;
    $error_count = 0;

    // Durch alle Beiträge iterieren
    if ($query->have_posts()) {
        echo '<div class="wrap"><h2>Prozess läuft...</h2><ul>';
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $title = get_the_title();

            // Funktion aufrufen, um das Beitragsbild zu setzen
            $result = asfi_auto_set_featured_image($post_id);

            // Statusausgabe je nach Ergebnis
            if ($result == 'set') {
                $set_count++;
                echo '<li>Beitragsbild für <strong>' . esc_html($title) . '</strong> erfolgreich gesetzt.</li>';
            } elseif ($result == 'already_set') {
                $already_set_count++;
                echo '<li><strong>' . esc_html($title) . '</strong> hat bereits ein Beitragsbild.</li>';
            } elseif ($result == 'no_image') {
                $no_image_count++;
                echo '<li><strong>' . esc_html($title) . '</strong> enthält kein Bild.</li>';
            } else {
                $error_count++;
                echo '<li>Fehler beim Setzen des Beitragsbildes für <strong>' . esc_html($title) . '</strong>.</li>';
            }
        }
        echo '</ul></div>';
        wp_reset_postdata(); // Reset der globalen Post-Daten

        // Zusammenfassung der Ergebnisse
        echo '<div class="wrap"><h2>Prozess abgeschlossen!</h2>';
        echo '<p>Gesamtanzahl der Beiträge/Seiten: ' . esc_html($total_posts) . '</p>';
        echo '<p>Beitragsbilder erfolgreich gesetzt: ' . esc_html($set_count) . '</p>';
        echo '<p>Bereits vorhandene Beitragsbilder: ' . esc_html($already_set_count) . '</p>';
        echo '<p>Keine Bilder im Inhalt gefunden: ' . esc_html($no_image_count) . '</p>';
        echo '<p>Fehler: ' . esc_html($error_count) . '</p>';
        echo '</div>';
    } else {
        echo '<div class="notice notice-warning is-dismissible"><p>Keine Beiträge oder Seiten gefunden.</p></div>';
    }
}
