<?php
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/components.php';

$school = site_data()['schools']['sd'];
$title = $school['name'] . ' - Yayasan Cendekia';
$description = $school['description'];

require __DIR__ . '/includes/header.php';
render_school_page('sd');
require __DIR__ . '/includes/footer.php';
