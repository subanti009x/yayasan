<?php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_set_cookie_params(['httponly' => true, 'secure' => $isHttps, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/articles.php';
require_once __DIR__ . '/includes/cms.php';
require_once __DIR__ . '/includes/registration.php';
require_once __DIR__ . '/includes/google_forms.php';

$adminPassword = (string) cms_env('ADMIN_PASSWORD');
$sessionSecret = (string) cms_env('SESSION_SECRET');
$configurationError = $adminPassword === '' || $sessionSecret === ''
    ? 'ADMIN_PASSWORD dan SESSION_SECRET wajib diatur di environment server.'
    : '';

$isLoggedIn = ($_SESSION['admin_role'] ?? '') === 'admin';

$message = '';
$error = '';
$currentTab = $_GET['tab'] ?? 'articles';

// CSRF token is bound to the server-side session.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function admin_redirect(string $suffix = ''): void
{
    header('Location: ' . url('admin.php') . $suffix);
    exit;
}

function require_csrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        exit('Token keamanan tidak valid.');
    }
}

function admin_external_url(string $value): ?string
{
    $value = trim(str_replace("\0", '', $value));

    if ($value === '') {
        return '';
    }

    $parts = filter_var($value, FILTER_VALIDATE_URL) ? parse_url($value) : false;
    if ($parts === false || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['https'], true) || strlen($value) > 2048) {
        return null;
    }

    return $value;
}

function admin_text(string $field, int $maximum): string
{
    $value = trim((string) ($_POST[$field] ?? ''));
    return mb_substr(str_replace("\0", '', $value), 0, $maximum);
}

function admin_json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function admin_registration_config(): array
{
    $types = ['text', 'tel', 'email', 'number', 'date', 'textarea', 'select'];
    $fields = [];
    foreach ((array) ($_POST['registration_field_label'] ?? []) as $index => $label) {
        $label = mb_substr(trim((string) $label), 0, 120);
        $name = trim((string) (($_POST['registration_field_name'] ?? [])[$index] ?? ''));
        if ($label === '' || !preg_match('/^(?:entry\.\d+|[a-z][a-z0-9_-]{0,63})$/i', $name)) continue;
        $type = (string) (($_POST['registration_field_type'] ?? [])[$index] ?? 'text');
        $fields[] = [
            'label' => $label, 'name' => $name, 'type' => in_array($type, $types, true) ? $type : 'text',
            'placeholder' => mb_substr(trim((string) (($_POST['registration_field_placeholder'] ?? [])[$index] ?? '')), 0, 180),
            'required' => ((string) (($_POST['registration_field_required'] ?? [])[$index] ?? '')) === '1',
            'options' => array_values(array_filter(array_map('trim', explode("\n", (string) (($_POST['registration_field_options'] ?? [])[$index] ?? ''))))),
        ];
    }
    return [
        'title' => admin_text('registration_title', 160), 'intro' => admin_text('registration_intro', 2000),
        'notice' => admin_text('registration_notice', 2000), 'is_open' => isset($_POST['registration_is_open']),
        'submit_label' => admin_text('registration_submit_label', 80) ?: 'Kirim Pendaftaran',
        'success_message' => admin_text('registration_success_message', 300) ?: 'Terima kasih. Data pendaftaran Anda sudah terkirim.',
        'consent_text' => admin_text('registration_consent_text', 500), 'fields' => $fields,
    ];
}

function uploaded_content_images(string $inputName, ?string &$error): array
{
    $input = $_FILES[$inputName] ?? null;
    if (!is_array($input) || !is_array($input['name'] ?? null)) return [];
    $original = $_FILES['hero_image_upload'] ?? null;
    $uploads = [];
    foreach ($input['name'] as $index => $name) {
        if (($input['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $_FILES['hero_image_upload'] = ['name' => $name, 'type' => $input['type'][$index] ?? '', 'tmp_name' => $input['tmp_name'][$index] ?? '', 'error' => $input['error'][$index] ?? UPLOAD_ERR_NO_FILE, 'size' => $input['size'][$index] ?? 0];
        $image = uploaded_image($error, 'schools');
        if ($error !== null) break;
        if ($image !== null) $uploads[$index] = $image;
    }
    if ($original === null) unset($_FILES['hero_image_upload']); else $_FILES['hero_image_upload'] = $original;
    return $uploads;
}

function admin_content_cards(string $prefix, array $uploadedImages = []): array
{
    $cards = [];
    foreach ((array) ($_POST[$prefix . '_title'] ?? []) as $index => $title) {
        $title = mb_substr(trim((string) $title), 0, 160);
        if ($title === '') continue;
        $image = $uploadedImages[$index] ?? admin_external_url((string) (($_POST[$prefix . '_image'] ?? [])[$index] ?? ''));
        if ($image === null) continue;
        $cards[] = [
            'title' => $title,
            'description' => mb_substr(trim((string) (($_POST[$prefix . '_description'] ?? [])[$index] ?? '')), 0, 800),
            'image' => $image,
        ];
    }
    return $cards;
}

function admin_maps_location(string $embedUrl, string $savedLocation = ''): string
{
    if (trim($savedLocation) !== '') {
        return trim($savedLocation);
    }

    $query = parse_url($embedUrl, PHP_URL_QUERY);
    parse_str(is_string($query) ? $query : '', $parameters);
    return trim((string) ($parameters['q'] ?? ''));
}

function admin_google_maps_embed(string $location): string
{
    return 'https://www.google.com/maps?q=' . rawurlencode($location) . '&output=embed';
}

function admin_managed_upload_path(string $url): ?string
{
    $parts = parse_url($url);
    if (!is_array($parts)) {
        return null;
    }

    $path = ltrim((string) ($parts['path'] ?? ''), '/');
    if ($path === 'uploads.php') {
        parse_str((string) ($parts['query'] ?? ''), $query);
        $path = (string) ($query['path'] ?? '');
    }

    return preg_match('#^uploads/(?:articles|schools)/[a-z0-9._-]+$#i', $path) ? $path : null;
}

function uploaded_image(?string &$error, string $folder = 'articles'): ?string
{
    $inputName = $folder === 'articles' ? 'image_upload' : 'hero_image_upload';
    if (empty($_FILES[$inputName]) || !is_array($_FILES[$inputName])) {
        return null;
    }

    $file = $_FILES[$inputName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Gambar belum bisa diupload. Silakan coba lagi.';
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'Ukuran gambar maksimal 5 MB.';
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($extension, $allowedExtensions, true)) {
        $error = 'Format gambar harus JPG, PNG, WEBP, atau GIF.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
    unset($finfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        $error = 'File yang diupload bukan gambar yang valid.';
        return null;
    }

    $imageInfo = @getimagesize($tmpName);
    if (!is_array($imageInfo) || ($imageInfo['mime'] ?? '') !== $mimeType) {
        $error = 'File yang diupload bukan gambar yang valid.';
        return null;
    }

    $prefix = $folder === 'articles' ? 'artikel' : 'hero';
    $filename = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

    $content = file_get_contents($tmpName);
    if ($content === false) {
        $error = 'Gambar belum bisa dibaca. Silakan coba lagi.';
        return null;
    }

    $uploadedPath = 'uploads/' . $folder . '/' . $filename;
    if (cms_save_upload($uploadedPath, $content, $mimeType)) {
        return getenv('VERCEL') === '1'
            ? '/' . $uploadedPath
            : 'uploads.php?path=' . rawurlencode($uploadedPath);
    }

    $storageError = cms_last_error();
    $error = $storageError !== '' ? $storageError : 'Gambar belum bisa disimpan ke database.';
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'login') {
        require_csrf();

        $rateKey = security_client_key($sessionSecret);
        if ($configurationError === '' && login_is_rate_limited($rateKey)) {
            $error = 'Terlalu banyak percobaan login. Silakan coba lagi 15 menit lagi.';
        } else {

        $submittedPassword = (string) ($_POST['password'] ?? '');
        $isPasswordValid = $configurationError === '' && (
            str_starts_with($adminPassword, '$2y$') || str_starts_with($adminPassword, '$argon2')
                ? password_verify($submittedPassword, $adminPassword)
                : hash_equals($adminPassword, $submittedPassword)
        );

        if ($isPasswordValid) {
            session_regenerate_id(true);
            $_SESSION['admin_role'] = 'admin';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            clear_login_failures($rateKey);
            admin_redirect('?success=login');
        }
        record_login_failure($rateKey);
        $error = 'Password admin belum sesuai.';
        }
    } elseif ($action === 'logout') {
        require_csrf();
        $_SESSION = [];
        session_destroy();
        
        admin_redirect();
    } elseif ($isLoggedIn) {
        require_csrf();
        
        if ($action === 'preview_google_form') {
            $formUrl = admin_external_url((string) ($_POST['form_url'] ?? ''));
            if ($formUrl === null || $formUrl === '') {
                admin_json(['ok' => false, 'message' => 'Masukkan URL HTTPS Google Form yang valid.'], 422);
            }
            $syncError = null;
            $schema = google_form_schema($formUrl, $syncError);
            if ($schema === null) {
                admin_json(['ok' => false, 'message' => $syncError ?: 'Google Form belum dapat dibaca.'], 422);
            }
            admin_json(['ok' => true, 'schema' => $schema]);
        } elseif ($action === 'save' || $action === 'delete') {
            if ($action === 'delete') {
                $id = (string) ($_POST['id'] ?? '');
                $article = $id !== '' ? find_article_by_id($id) : null;
                if ($article !== null && delete_article($id)) {
                    $uploadPath = admin_managed_upload_path((string) ($article['image'] ?? ''));
                    if ($uploadPath !== null) {
                        cms_delete_upload($uploadPath);
                    }
                    admin_redirect('?tab=articles&success=delete');
                }
                $storageError = cms_last_error();
                $error = $storageError !== '' ? $storageError : 'Gagal menghapus artikel.';
            } else {
                $id = trim((string) ($_POST['id'] ?? ''));
                $titleInput = admin_text('title', 180);
                $contentInput = admin_text('content', 20000);

                $uploadError = null;
                $uploadedImage = uploaded_image($uploadError, 'articles');

                if ($uploadError !== null) {
                    $error = $uploadError;
                } elseif ($titleInput === '' || $contentInput === '') {
                    $error = 'Judul dan isi artikel wajib diisi.';
                } else {
                    $now = date('Y-m-d H:i');
                    $existing = $id !== '' ? find_article_by_id($id) : null;
                    $imageUrl = admin_external_url((string) ($_POST['image'] ?? ''));
                    $currentImage = trim((string) ($_POST['current_image'] ?? ''));

                    if (($_POST['remove_image'] ?? '') === '1') {
                        $currentImage = '';
                    }

                    if ($imageUrl === null) {
                        $error = 'URL gambar artikel harus menggunakan HTTPS yang valid.';
                    } else {
                        $image = $uploadedImage ?: ($imageUrl !== '' ? $imageUrl : $currentImage);
                        $article = [
                        'id' => $existing['id'] ?? ('art_' . date('Ymd_His')),
                        'title' => $titleInput,
                        'slug' => unique_article_slug($titleInput, $existing['id'] ?? null),
                        'category' => admin_text('category', 100) ?: 'Artikel',
                        'author' => admin_text('author', 120) ?: 'Admin Yayasan',
                        'excerpt' => admin_text('excerpt', 600),
                        'content' => $contentInput,
                        'image' => $image,
                        'views' => (int) ($existing['views'] ?? 0),
                        'status' => in_array(($_POST['status'] ?? 'draft'), ['draft', 'published'], true) ? $_POST['status'] : 'draft',
                        'created_at' => $existing['created_at'] ?? $now,
                        'updated_at' => $now,
                    ];

                        if (save_article($article)) {
                            admin_redirect('?tab=articles&success=save');
                        }

                        $storageError = cms_last_error();
                        $error = $storageError !== '' ? $storageError : 'Artikel belum bisa disimpan ke database.';
                    }
                }
            }
        } elseif ($action === 'save_foundation') {
            $siteData = load_site_data();
            $siteData['foundation']['name'] = trim((string)($_POST['name'] ?? ''));
            $siteData['foundation']['tagline'] = trim((string)($_POST['tagline'] ?? ''));
            $siteData['foundation']['description'] = trim((string)($_POST['description'] ?? ''));
            $siteData['foundation']['phone'] = trim((string)($_POST['phone'] ?? ''));
            $siteData['foundation']['email'] = trim((string)($_POST['email'] ?? ''));
            $siteData['foundation']['address'] = trim((string)($_POST['address'] ?? ''));
            $mapsLocation = admin_text('maps_location', 500);
            if ($mapsLocation === '') {
                $error = 'Masukkan alamat atau nama lokasi yayasan untuk peta Google Maps.';
            } else {
                $siteData['foundation']['maps_location'] = $mapsLocation;
                $siteData['foundation']['maps_embed'] = admin_google_maps_embed($mapsLocation);
                $siteData['foundation']['maps_url'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapsLocation);
            }

            $uploadError = null;
            $uploadedImage = uploaded_image($uploadError, 'schools');
            
            if ($uploadError !== null) {
                $error = $uploadError;
            } else {
                $imageUrl = admin_external_url((string) ($_POST['hero_image_url'] ?? ''));
                if ($imageUrl === null) {
                    $error = 'URL gambar yayasan harus menggunakan HTTPS yang valid.';
                }
                if ($error === '' && $uploadedImage) {
                    $siteData['foundation']['hero_image'] = $uploadedImage;
                } elseif ($error === '' && $imageUrl !== '') {
                    $siteData['foundation']['hero_image'] = $imageUrl;
                } elseif ($error === '' && ($_POST['remove_image'] ?? '') === '1') {
                    $siteData['foundation']['hero_image'] = '';
                }
                
                if ($error === '' && save_site_data($siteData)) {
                    admin_redirect('?tab=foundation&success=save');
                } else {
                    $storageError = cms_last_error();
                    $error = $storageError !== '' ? $storageError : 'Gagal menyimpan data yayasan.';
                }
            }
        } elseif ($action === 'save_branding') {
            $siteData = load_site_data();
            $siteData['branding']['theme_color'] = trim((string)($_POST['theme_color'] ?? 'orange'));
            $siteData['branding']['logo_type'] = trim((string)($_POST['logo_type'] ?? 'text'));
            $siteData['branding']['logo_text'] = trim((string)($_POST['logo_text'] ?? 'YC'));
            $siteData['branding']['text_header_sub'] = trim((string)($_POST['text_header_sub'] ?? 'Sekolah Indonesia'));
            $siteData['branding']['text_contact_button'] = trim((string)($_POST['text_contact_button'] ?? 'Hubungi Kami'));
            $siteData['branding']['text_footer_tagline'] = trim((string)($_POST['text_footer_tagline'] ?? 'Pendidikan terpadu untuk keluarga Indonesia.'));
            $siteData['branding']['text_footer_copyright'] = trim((string)($_POST['text_footer_copyright'] ?? '&copy; {year} Yayasan Cendekia. Semua hak dilindungi.'));

            $uploadError = null;
            // Hack uploaded_image to work with logo
            $tmpFile = $_FILES['logo_upload'] ?? null;
            if ($tmpFile && $tmpFile['error'] !== UPLOAD_ERR_NO_FILE) {
                // temporarily spoof the name so `uploaded_image` processes it
                $_FILES['hero_image_upload'] = $tmpFile;
                $uploadedImage = uploaded_image($uploadError, 'schools'); // Reuse 'schools' folder for now
                if ($uploadError !== null) {
                    $error = $uploadError;
                } elseif ($uploadedImage) {
                    $siteData['branding']['logo_image'] = $uploadedImage;
                    $siteData['branding']['logo_type'] = 'image';
                }
            }

            if (($_POST['remove_logo'] ?? '') === '1') {
                $siteData['branding']['logo_image'] = '';
            }

            if ($error === '') {
                if (save_site_data($siteData)) {
                    admin_redirect('?tab=branding&success=save');
                } else {
                    $storageError = cms_last_error();
                    $error = $storageError !== '' ? $storageError : 'Gagal menyimpan data branding.';
                }
            }
        } elseif ($action === 'save_school') {
            $id = trim((string)($_POST['school_id'] ?? ''));
            $siteData = load_site_data();
            if (isset($siteData['schools'][$id])) {
                $siteData['schools'][$id]['name'] = trim((string)($_POST['name'] ?? ''));
                $siteData['schools'][$id]['description'] = trim((string)($_POST['description'] ?? ''));
                $siteData['schools'][$id]['accent'] = trim((string)($_POST['accent'] ?? ''));
                $siteData['schools'][$id]['phone'] = trim((string)($_POST['phone'] ?? ''));
                $formUrl = admin_external_url((string) ($_POST['form_url'] ?? ''));
                $mapsLocation = admin_text('maps_location', 500);
                $existingFormUrl = (string) ($siteData['schools'][$id]['form_url'] ?? '');
                if ($formUrl === null) {
                    $error = 'Link pendaftaran harus menggunakan HTTPS yang valid.';
                } elseif ($mapsLocation === '') {
                    $error = 'Masukkan alamat atau nama lokasi sekolah untuk peta Google Maps.';
                } else {
                    $siteData['schools'][$id]['form_url'] = $formUrl;
                    $siteData['schools'][$id]['maps_location'] = $mapsLocation;
                    $siteData['schools'][$id]['maps_embed'] = admin_google_maps_embed($mapsLocation);
                    $siteData['schools'][$id]['maps_url'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapsLocation);
                    $siteData['schools'][$id]['registration'] = admin_registration_config();
                    if ($formUrl !== '' && $formUrl !== $existingFormUrl) {
                        $syncError = null;
                        $schema = google_form_schema($formUrl, $syncError);
                        if ($schema === null) {
                            $error = $syncError;
                        } else {
                            $siteData['schools'][$id]['registration']['fields'] = $schema['fields'];
                            $siteData['schools'][$id]['registration']['is_open'] = true;
                            $siteData['schools'][$id]['google_form_title'] = $schema['title'];
                            $siteData['schools'][$id]['google_form_synced_at'] = date('c');
                        }
                    }
                }
                
                $programs = [];
                $pTitles = $_POST['program_title'] ?? [];
                $pDescs = $_POST['program_desc'] ?? [];
                foreach ($pTitles as $idx => $title) {
                    if (trim($title) !== '') {
                        $programs[] = ['title' => trim($title), 'description' => trim($pDescs[$idx] ?? '')];
                    }
                }
                $siteData['schools'][$id]['programs'] = $programs;
                
                $contentUploadError = null;
                $facilityImages = uploaded_content_images('facility_image_upload', $contentUploadError);
                $activityImages = uploaded_content_images('activity_image_upload', $contentUploadError);
                if ($contentUploadError !== null) {
                    $error = $contentUploadError;
                }
                $siteData['schools'][$id]['facilities'] = admin_content_cards('facility', $facilityImages);
                $siteData['schools'][$id]['activities'] = admin_content_cards('activity', $activityImages);
                
                $uploadError = null;
                $uploadedImage = uploaded_image($uploadError, 'schools');
                if ($uploadError !== null) {
                    $error = $uploadError;
                } else {
                    $imageUrl = admin_external_url((string) ($_POST['hero_image_url'] ?? ''));
                    if ($imageUrl === null) {
                        $error = 'URL gambar sekolah harus menggunakan HTTPS yang valid.';
                    } elseif ($uploadedImage) {
                        $siteData['schools'][$id]['hero_image'] = $uploadedImage;
                    } elseif ($imageUrl !== '') {
                        $siteData['schools'][$id]['hero_image'] = $imageUrl;
                    } elseif (($_POST['remove_image'] ?? '') === '1') {
                        $siteData['schools'][$id]['hero_image'] = '';
                    }
                    
                    if ($error === '' && save_site_data($siteData)) {
                        admin_redirect('?tab=schools&edit_school=' . $id . '&success=save');
                    } else {
                        $storageError = cms_last_error();
                        $error = $storageError !== '' ? $storageError : 'Gagal menyimpan data sekolah.';
                    }
                }
            }
        } elseif ($action === 'sync_google_form') {
            $id = preg_replace('/[^a-z0-9-]/i', '', (string) ($_POST['school_id'] ?? ''));
            $siteData = load_site_data();
            $url = (string) ($siteData['schools'][$id]['form_url'] ?? '');
            $syncError = null;
            $schema = $url !== '' ? google_form_schema($url, $syncError) : null;
            if ($schema === null) {
                $error = $syncError ?: 'Simpan URL Google Form terlebih dahulu.';
            } else {
                $siteData['schools'][$id]['registration']['fields'] = $schema['fields'];
                $siteData['schools'][$id]['registration']['is_open'] = true;
                $siteData['schools'][$id]['google_form_title'] = $schema['title'];
                $siteData['schools'][$id]['google_form_synced_at'] = date('c');
                if (save_site_data($siteData)) admin_redirect('?tab=schools&edit_school=' . rawurlencode($id) . '&success=save');
                $error = cms_last_error() ?: 'Sinkronisasi tidak dapat disimpan.';
            }
        } elseif ($action === 'create_school') {
            $siteData = load_site_data();
            $name = admin_text('new_school_name', 180);
            $shortName = admin_text('new_school_short_name', 80);
            $id = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? '');
            $id = trim($id, '-');
            if ($name === '' || $shortName === '' || $id === '' || isset($siteData['schools'][$id])) {
                $error = 'Nama unit dan singkatan wajib diisi. Gunakan nama yang belum pernah dipakai.';
            } else {
                $siteData['schools'][$id] = [
                    'name' => $name, 'short_name' => $shortName, 'page' => 'sekolah-' . $id,
                    'campus' => in_array($_POST['new_school_campus'] ?? '', ['cirebon', 'losari'], true) ? $_POST['new_school_campus'] : 'cirebon',
                    'level' => admin_text('new_school_level', 120) ?: 'Unit sekolah',
                    'description' => '', 'programs' => [], 'facilities' => [], 'activities' => [], 'phone' => '', 'form_url' => '',
                    'maps_embed' => '', 'hero_image' => '', 'accent' => 'amber', 'registration' => registration_default_config(['name' => $name]),
                ];
                if (save_site_data($siteData)) admin_redirect('?tab=schools&edit_school=' . rawurlencode($id) . '&success=save');
                $error = cms_last_error() ?: 'Unit sekolah belum dapat dibuat.';
            }
        } elseif ($action === 'delete_school') {
            $id = preg_replace('/[^a-z0-9-]/i', '', (string) ($_POST['school_id'] ?? ''));
            $siteData = load_site_data();
            if ($id === '' || !isset($siteData['schools'][$id])) {
                $error = 'Unit sekolah tidak ditemukan.';
            } else {
                unset($siteData['schools'][$id]);
                // Deletion is the sole operation allowed to remove a unit.
                if (save_site_data($siteData, false)) admin_redirect('?tab=schools&success=delete');
                $error = cms_last_error() ?: 'Unit sekolah belum dapat dihapus.';
            }
        } elseif ($action === 'save_faq') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $question = admin_text('question', 300);
            $answer = admin_text('answer', 3000);
            
            if ($question !== '' && $answer !== '') {
                if (save_faq($id === false ? null : $id, $question, $answer)) {
                    admin_redirect('?tab=faq&success=save');
                } else {
                    $storageError = cms_last_error();
                    $error = $storageError !== '' ? $storageError : 'Gagal menyimpan FAQ.';
                }
            } else {
                $error = 'Pertanyaan dan jawaban wajib diisi.';
            }
        } elseif ($action === 'delete_faq') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id !== false && $id !== null) {
                if (delete_faq($id)) {
                    admin_redirect('?tab=faq&success=delete');
                } else {
                    $storageError = cms_last_error();
                    $error = $storageError !== '' ? $storageError : 'Gagal menghapus FAQ.';
                }
            }
        } elseif ($action === 'delete_media') {
            $path = admin_managed_upload_path((string) ($_POST['path'] ?? ''));
            if ($path === null || !cms_delete_upload($path)) {
                $error = cms_last_error() ?: 'Media tidak dapat dihapus.';
            } else {
                admin_redirect('?tab=media&success=delete');
            }
        }
    }
}

$editId = (string) ($_GET['edit'] ?? '');
$editArticle = $editId !== '' ? find_article_by_id($editId) : null;
$articleSearch = mb_substr(trim((string) ($_GET['article_q'] ?? '')), 0, 100);
$articleStatus = (string) ($_GET['article_status'] ?? '');
$articles = load_articles(false, $articleSearch, $articleStatus);

$siteData = load_site_data();
$faqSearch = mb_substr(trim((string) ($_GET['faq_q'] ?? '')), 0, 100);
$faqs = load_faq_data($faqSearch);
$editSchoolId = (string) ($_GET['edit_school'] ?? '');
$editSchool = $editSchoolId !== '' && isset($siteData['schools'][$editSchoolId]) ? $siteData['schools'][$editSchoolId] : null;
$editFaqId = filter_input(INPUT_GET, 'edit_faq', FILTER_VALIDATE_INT);
$editFaq = $editFaqId !== false && $editFaqId !== null ? find_faq_by_id($editFaqId) : null;
$mediaFiles = $currentTab === 'media' ? cms_list_uploads() : [];

if (($_GET['success'] ?? '') === 'save') {
    $message = 'Data berhasil disimpan';
} elseif (($_GET['success'] ?? '') === 'delete') {
    $message = 'Data berhasil dihapus';
} elseif (($_GET['success'] ?? '') === 'login') {
    $message = 'Login berhasil. Silakan kelola website sekolah.';
}

$title = 'Dashboard Admin - Yayasan Cendekia';
$description = 'Dashboard admin sistem CMS Yayasan Cendekia.';

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="bg-slate-950 py-14 text-white sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-wide text-secondary-200">Dashboard Admin</p>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Kelola Website Yayasan.</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-200">Kelola artikel, identitas yayasan, unit sekolah, dan FAQ dari satu tempat secara dinamis.</p>
        </div>
    </section>

    <section class="bg-slate-50 py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <?php if ($message !== ''): ?>
                <p class="mb-6 rounded-md bg-secondary-50 px-4 py-3 text-sm font-bold text-primary-800 ring-1 ring-secondary-200"><?= e($message); ?></p>
            <?php endif; ?>
            <?php if ($configurationError !== ''): ?>
                <p class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm font-bold text-red-700 ring-1 ring-red-200"><?= e($configurationError); ?></p>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <p class="mb-6 rounded-md bg-red-50 px-4 py-3 text-sm font-bold text-red-700 ring-1 ring-red-200"><?= e($error); ?></p>
            <?php endif; ?>

            <?php if (!$isLoggedIn): ?>
                <form method="post" class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="login">
                    <label class="grid gap-2 text-sm font-bold text-slate-700">
                        Password admin
                        <input type="password" name="password" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
                    </label>
                    <button type="submit" class="mt-5 w-full rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Masuk Dashboard</button>
                </form>
            <?php else: ?>
                
                <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
                    <nav class="-mb-px flex gap-6">
                        <a href="?tab=articles" class="<?= $currentTab === 'articles' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' ?> whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium">Artikel</a>
                        <a href="?tab=branding" class="<?= $currentTab === 'branding' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' ?> whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium">Branding & Tampilan</a>
                        <a href="?tab=foundation" class="<?= $currentTab === 'foundation' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' ?> whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium">Identitas & Kontak</a>
                        <a href="?tab=schools" class="<?= $currentTab === 'schools' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' ?> whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium">Unit Sekolah</a>
                        <a href="?tab=faq" class="<?= $currentTab === 'faq' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' ?> whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium">FAQ</a>
                        <a href="?tab=media" class="<?= $currentTab === 'media' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' ?> whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium">Media</a>
                    </nav>
                    <form method="post" class="mt-4 sm:mt-0">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Keluar</button>
                    </form>
                </div>

                <?php if ($currentTab === 'articles'): ?>
                    <?php include __DIR__ . '/admin_tab_articles.php'; ?>
                <?php elseif ($currentTab === 'branding'): ?>
                    <?php include __DIR__ . '/admin_tab_branding.php'; ?>
                <?php elseif ($currentTab === 'foundation'): ?>
                    <?php include __DIR__ . '/admin_tab_foundation.php'; ?>
                <?php elseif ($currentTab === 'schools'): ?>
                    <?php include __DIR__ . '/admin_tab_schools.php'; ?>
                <?php elseif ($currentTab === 'faq'): ?>
                    <?php include __DIR__ . '/admin_tab_faq.php'; ?>
                <?php elseif ($currentTab === 'media'): ?>
                    <?php include __DIR__ . '/admin_tab_media.php'; ?>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
