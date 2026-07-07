<?php
session_start();

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/articles.php';
require_once __DIR__ . '/includes/cms.php';

$adminPassword = getenv('ADMIN_PASSWORD') ?: 'cendekia-admin';
$sessionSecret = getenv('SESSION_SECRET') ?: 'cendekia-session-secret-key-123456';

// 1. Stateless Cookie Session Check (to keep login state across serverless functions)
$isLoggedIn = ($_SESSION['admin_logged_in'] ?? false) === true;
if (!$isLoggedIn && isset($_COOKIE['admin_auth'])) {
    $expectedHash = hash_hmac('sha256', $adminPassword, $sessionSecret);
    if (hash_equals($expectedHash, $_COOKIE['admin_auth'])) {
        $isLoggedIn = true;
        $_SESSION['admin_logged_in'] = true;
    }
}

$message = '';
$error = '';
$currentTab = $_GET['tab'] ?? 'articles';

// 2. CSRF Token handling (Stateless backup via cookie)
if (empty($_SESSION['csrf_token'])) {
    if (isset($_COOKIE['csrf_token'])) {
        $_SESSION['csrf_token'] = $_COOKIE['csrf_token'];
    } else {
        $token = bin2hex(random_bytes(16));
        $_SESSION['csrf_token'] = $token;
        setcookie('csrf_token', $token, [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

function admin_redirect(string $suffix = ''): void
{
    header('Location: /admin.php' . $suffix);
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
    $mimeType = $finfo ? finfo_file($finfo, $tmpName) : '';

    if ($finfo) {
        finfo_close($finfo);
    }

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        $error = 'File yang diupload bukan gambar yang valid.';
        return null;
    }

    $prefix = $folder === 'articles' ? 'artikel' : 'hero';
    $filename = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

    // Vercel Serverless File Upload to GitHub
    if (getenv('VERCEL') === '1') {
        $githubToken = getenv('GITHUB_TOKEN');
        if ($githubToken) {
            $uploadedPath = 'assets/uploads/' . $folder . '/' . $filename;
            $success = commit_to_github($uploadedPath, file_get_contents($tmpName), 'upload ' . $folder . ' image: ' . $filename, $githubToken);
            if ($success) {
                return $uploadedPath;
            }
        }
        $error = 'Gagal menyimpan gambar di cloud (Token GitHub belum diatur).';
        return null;
    }

    // Local file system upload
    $uploadDir = __DIR__ . '/../assets/uploads/' . $folder;

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        $error = 'Folder upload belum bisa dibuat.';
        return null;
    }

    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        $error = 'Gambar belum bisa disimpan ke server.';
        return null;
    }

    return 'assets/uploads/' . $folder . '/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'login') {
        require_csrf();

        if (hash_equals($adminPassword, (string) ($_POST['password'] ?? ''))) {
            $_SESSION['admin_logged_in'] = true;
            
            // Set signed authentication cookie
            $hash = hash_hmac('sha256', $adminPassword, $sessionSecret);
            setcookie('admin_auth', $hash, [
                'expires' => time() + 86400 * 7,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            admin_redirect('?success=login');
        }

        $error = 'Password admin belum sesuai.';
    } elseif ($action === 'logout') {
        require_csrf();
        $_SESSION = [];
        session_destroy();
        
        // Clear authentication cookie
        setcookie('admin_auth', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        admin_redirect();
    } elseif ($isLoggedIn) {
        require_csrf();
        
        if ($action === 'save' || $action === 'delete') {
            $articles = load_articles(false);

            if ($action === 'delete') {
                $id = (string) ($_POST['id'] ?? '');
                $articles = array_values(array_filter($articles, fn (array $article): bool => ($article['id'] ?? '') !== $id));
                
                if (save_articles($articles)) {
                    admin_redirect('?tab=articles&success=delete');
                }
                $error = 'Gagal menghapus artikel.';
            } else {
                $id = trim((string) ($_POST['id'] ?? ''));
                $titleInput = trim((string) ($_POST['title'] ?? ''));
                $contentInput = trim((string) ($_POST['content'] ?? ''));

                $uploadError = null;
                $uploadedImage = uploaded_image($uploadError, 'articles');

                if ($uploadError !== null) {
                    $error = $uploadError;
                } elseif ($titleInput === '' || $contentInput === '') {
                    $error = 'Judul dan isi artikel wajib diisi.';
                } else {
                    $now = date('Y-m-d H:i');
                    $existing = $id !== '' ? find_article_by_id($id) : null;
                    $imageUrl = trim((string) ($_POST['image'] ?? ''));
                    $currentImage = trim((string) ($_POST['current_image'] ?? ''));

                    if (($_POST['remove_image'] ?? '') === '1') {
                        $currentImage = '';
                    }

                    $image = $uploadedImage ?: ($imageUrl !== '' ? $imageUrl : $currentImage);
                    $article = [
                        'id' => $existing['id'] ?? ('art_' . date('Ymd_His')),
                        'title' => $titleInput,
                        'slug' => unique_article_slug($titleInput, $existing['id'] ?? null),
                        'category' => trim((string) ($_POST['category'] ?? 'Artikel')),
                        'author' => trim((string) ($_POST['author'] ?? 'Admin Yayasan')),
                        'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
                        'content' => $contentInput,
                        'image' => $image,
                        'views' => (int) ($existing['views'] ?? 0),
                        'status' => in_array(($_POST['status'] ?? 'draft'), ['draft', 'published'], true) ? $_POST['status'] : 'draft',
                        'created_at' => $existing['created_at'] ?? $now,
                        'updated_at' => $now,
                    ];

                    $updated = false;

                    foreach ($articles as $index => $existingArticle) {
                        if (($existingArticle['id'] ?? '') !== $article['id']) {
                            continue;
                        }

                        $articles[$index] = $article;
                        $updated = true;
                        break;
                    }

                    if (!$updated) {
                        $articles[] = $article;
                    }

                    if (save_articles($articles)) {
                        admin_redirect('?tab=articles&success=save');
                    }

                    $error = 'Artikel belum bisa disimpan. Pastikan environment token GitHub sudah benar jika dijalankan di production.';
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
                $imageUrl = trim((string) ($_POST['hero_image_url'] ?? ''));
                if ($uploadedImage) {
                    $siteData['foundation']['hero_image'] = $uploadedImage;
                } elseif ($imageUrl !== '') {
                    $siteData['foundation']['hero_image'] = $imageUrl;
                } elseif (($_POST['remove_image'] ?? '') === '1') {
                    $siteData['foundation']['hero_image'] = '';
                }
                
                if (save_site_data($siteData)) {
                    admin_redirect('?tab=foundation&success=save');
                } else {
                    $error = 'Gagal menyimpan data yayasan.';
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
                }
            }

            if (($_POST['remove_logo'] ?? '') === '1') {
                $siteData['branding']['logo_image'] = '';
            }

            if ($error === '') {
                if (save_site_data($siteData)) {
                    admin_redirect('?tab=branding&success=save');
                } else {
                    $error = 'Gagal menyimpan data branding.';
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
                $siteData['schools'][$id]['form_url'] = trim((string)($_POST['form_url'] ?? ''));
                $siteData['schools'][$id]['maps_embed'] = trim((string)($_POST['maps_embed'] ?? ''));
                
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
                    $imageUrl = trim((string) ($_POST['hero_image_url'] ?? ''));
                    if ($uploadedImage) {
                        $siteData['schools'][$id]['hero_image'] = $uploadedImage;
                    } elseif ($imageUrl !== '') {
                        $siteData['schools'][$id]['hero_image'] = $imageUrl;
                    } elseif (($_POST['remove_image'] ?? '') === '1') {
                        $siteData['schools'][$id]['hero_image'] = '';
                    }
                    
                    if (save_site_data($siteData)) {
                        admin_redirect('?tab=schools&edit_school=' . $id . '&success=save');
                    } else {
                        $error = 'Gagal menyimpan data sekolah.';
                    }
                }
            }
        } elseif ($action === 'save_faq') {
            $faqs = load_faq_data();
            $id = trim((string)($_POST['id'] ?? ''));
            $question = trim((string)($_POST['question'] ?? ''));
            $answer = trim((string)($_POST['answer'] ?? ''));
            
            if ($question !== '' && $answer !== '') {
                if ($id !== '' && isset($faqs[(int)$id])) {
                    $faqs[(int)$id] = ['question' => $question, 'answer' => $answer];
                } else {
                    $faqs[] = ['question' => $question, 'answer' => $answer];
                }
                if (save_faq_data($faqs)) {
                    admin_redirect('?tab=faq&success=save');
                } else {
                    $error = 'Gagal menyimpan FAQ.';
                }
            } else {
                $error = 'Pertanyaan dan jawaban wajib diisi.';
            }
        } elseif ($action === 'delete_faq') {
            $faqs = load_faq_data();
            $id = trim((string)($_POST['id'] ?? ''));
            if ($id !== '' && isset($faqs[(int)$id])) {
                unset($faqs[(int)$id]);
                if (save_faq_data(array_values($faqs))) {
                    admin_redirect('?tab=faq&success=delete');
                } else {
                    $error = 'Gagal menghapus FAQ.';
                }
            }
        }
    }
}

$editId = (string) ($_GET['edit'] ?? '');
$editArticle = $editId !== '' ? find_article_by_id($editId) : null;
$articles = load_articles(false);

$siteData = load_site_data();
$faqs = load_faq_data();
$editSchoolId = (string) ($_GET['edit_school'] ?? '');
$editSchool = $editSchoolId !== '' && isset($siteData['schools'][$editSchoolId]) ? $siteData['schools'][$editSchoolId] : null;
$editFaqId = (string) ($_GET['edit_faq'] ?? '');
$editFaq = $editFaqId !== '' && isset($faqs[(int)$editFaqId]) ? $faqs[(int)$editFaqId] : null;

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