<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/components.php';

$school = site_data()['schools']['losari-sd'];
$title = $school['name'] . ' - Yayasan Cendekia';
$description = $school['description'];

require __DIR__ . '/includes/header.php';
render_school_page('losari-sd');
require __DIR__ . '/includes/footer.php';
