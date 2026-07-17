<?php

function google_form_schema(string $url, ?string &$error): ?array
{
    $parts = parse_url($url);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $path = (string) ($parts['path'] ?? '');
    if (($parts['scheme'] ?? '') !== 'https' || $host !== 'docs.google.com' || !preg_match('#^/forms/d/e/[A-Za-z0-9_-]+/viewform$#', $path)) {
        $error = 'URL Google Form harus berupa link HTTPS viewform dari docs.google.com.';
        return null;
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 15, CURLOPT_USERAGENT => 'YayasanCendekiaCMS/1.0']);
    $html = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if (!is_string($html) || $status !== 200 || !preg_match('~FB_PUBLIC_LOAD_DATA_\s*=\s*(\[.*?\]);\s*</script>~s', $html, $matches)) {
        $error = 'Struktur Google Form tidak dapat dibaca. Pastikan form dapat diakses oleh siapa saja yang memiliki link.';
        return null;
    }
    try { $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR); } catch (JsonException) { $error = 'Data Google Form tidak valid.'; return null; }
    $questions = $data[1][1] ?? null;
    if (!is_array($questions)) { $error = 'Tidak ada pertanyaan yang dapat dibaca dari Google Form.'; return null; }
    $fields = [];
    foreach ($questions as $question) {
        $label = trim((string) ($question[1] ?? ''));
        $kind = (int) ($question[3] ?? -1);
        $definition = $question[4][0] ?? null;
        $entryId = $definition[0] ?? null;
        if ($label === '' || !is_numeric($entryId) || !in_array($kind, [0, 1, 2, 3, 4], true)) continue;
        $options = [];
        foreach ((array) ($definition[1] ?? []) as $option) if (is_array($option) && trim((string) ($option[0] ?? '')) !== '') $options[] = trim((string) $option[0]);
        $fields[] = ['label' => $label, 'name' => 'entry.' . $entryId, 'type' => $kind === 1 ? 'textarea' : ($options !== [] ? 'select' : 'text'), 'placeholder' => $label, 'required' => !empty($definition[2]), 'options' => $options];
    }
    if ($fields === []) { $error = 'Google Form tidak memiliki field yang didukung.'; return null; }
    return ['title' => trim((string) ($data[1][8] ?? 'Formulir Pendaftaran')), 'fields' => $fields];
}
