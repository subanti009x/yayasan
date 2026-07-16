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
        
        if ($action === 'save' || $action === 'delete') {
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
            $siteData['foundation']['maps_embed'] = trim((string)($_POST['maps_embed'] ?? ''));

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
                $mapsEmbed = admin_external_url((string) ($_POST['maps_embed'] ?? ''));
                if ($formUrl === null || $mapsEmbed === null) {
                    $error = 'Link pendaftaran dan Google Maps harus menggunakan HTTPS yang valid.';
                } else {
                    $siteData['schools'][$id]['form_url'] = $formUrl;
                    $siteData['schools'][$id]['maps_embed'] = $mapsEmbed;
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
                
                $siteData['schools'][$id]['facilities'] = array_filter(array_map('trim', explode("\n", $_POST['facilities'] ?? '')));
                $siteData['schools'][$id]['activities'] = array_filter(array_map('trim', explode("\n", $_POST['activities'] ?? '')));
                
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
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
