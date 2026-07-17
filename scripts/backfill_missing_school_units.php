<?php
/**
 * Restores school units that exist in the JSON seed but are absent from the
 * MySQL site document. Existing MySQL units and all of their edits are kept.
 *
 * Run with --apply to write changes; without it the script is a dry run.
 */
require_once __DIR__ . '/../api/includes/cms.php';

$seedPath = __DIR__ . '/../api/data/site.json';

try {
    $seedJson = file_get_contents($seedPath);
    if ($seedJson === false) {
        throw new RuntimeException('Seed unit sekolah tidak dapat dibaca.');
    }

    $seed = json_decode($seedJson, true, 512, JSON_THROW_ON_ERROR);
    $seedSchools = $seed['schools'] ?? [];
    if (!is_array($seedSchools)) {
        throw new RuntimeException('Seed unit sekolah tidak valid.');
    }

    $siteData = load_site_data();
    $existingSchools = $siteData['schools'] ?? [];
    if (!is_array($existingSchools)) {
        throw new RuntimeException('Data unit sekolah di MySQL tidak valid.');
    }

    $missing = array_diff_key($seedSchools, $existingSchools);
    if ($missing === []) {
        echo "Tidak ada unit sekolah yang perlu dipulihkan.\n";
        exit(0);
    }

    echo 'Unit yang belum ada di MySQL: ' . implode(', ', array_keys($missing)) . PHP_EOL;
    if (!in_array('--apply', $argv, true)) {
        echo "Dry run selesai. Jalankan ulang dengan --apply untuk memulihkan unit tersebut.\n";
        exit(0);
    }

    $siteData['schools'] = $existingSchools + $missing;
    if (!cms_save_document('site', $siteData)) {
        throw new RuntimeException(cms_last_error() ?: 'Unit sekolah tidak dapat disimpan.');
    }

    echo 'Berhasil memulihkan ' . count($missing) . " unit sekolah tanpa menimpa data unit yang sudah ada.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Pemulihan gagal: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
