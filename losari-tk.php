<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$school = site_data()['schools']['losari-tk'];
$title = $school['name'] . ' - Yayasan Cendekia';
$description = $school['description'];

require __DIR__ . '/includes/header.php';
render_school_page('losari-tk');
require __DIR__ . '/includes/footer.php';
