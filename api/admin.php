<?php
session_start();

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/articles.php';

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
    header('Location: /admin' . $suffix);
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

function uploaded_article_image(?string &$error): ?string
{
    if (empty($_FILES['image_upload']) || !is_array($_FILES['image_upload'])) {
        return null;
    }

    $file = $_FILES['image_upload'];

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

    $filename = 'artikel-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

    // Vercel Serverless File Upload to GitHub
    if (getenv('VERCEL') === '1') {
        $githubToken = getenv('GITHUB_TOKEN');
        if ($githubToken) {
            $uploadedPath = 'assets/uploads/articles/' . $filename;
            $success = commit_to_github($uploadedPath, file_get_contents($tmpName), 'upload article image: ' . $filename, $githubToken);
            if ($success) {
                return $uploadedPath;
            }
        }
        $error = 'Gagal menyimpan gambar di cloud (Token GitHub belum diatur).';
        return null;
    }

    // Local file system upload
    $uploadDir = __DIR__ . '/../assets/uploads/articles';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        $error = 'Folder upload belum bisa dibuat.';
        return null;
    }

    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        $error = 'Gambar belum bisa disimpan ke server.';
        return null;
    }

    return 'assets/uploads/articles/' . $filename;
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
    } elseif ($isLoggedIn && ($action === 'save' || $action === 'delete')) {
        require_csrf();
        $articles = load_articles(false);

        if ($action === 'delete') {
            $id = (string) ($_POST['id'] ?? '');
            $articles = array_values(array_filter($articles, fn (array $article): bool => ($article['id'] ?? '') !== $id));
            
            if (save_articles($articles)) {
                admin_redirect('?success=delete');
            }
            $error = 'Gagal menghapus artikel.';
        } else {
            $id = trim((string) ($_POST['id'] ?? ''));
            $titleInput = trim((string) ($_POST['title'] ?? ''));
            $contentInput = trim((string) ($_POST['content'] ?? ''));

            $uploadError = null;
            $uploadedImage = uploaded_article_image($uploadError);

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
                    admin_redirect('?success=save');
                }

                $error = 'Artikel belum bisa disimpan. Pastikan environment token GitHub sudah benar jika dijalankan di production.';
            }
        }
    }
}

$isLoggedIn = ($_SESSION['admin_logged_in'] ?? false) === true;
if (!$isLoggedIn && isset($_COOKIE['admin_auth'])) {
    $expectedHash = hash_hmac('sha256', $adminPassword, $sessionSecret);
    if (hash_equals($expectedHash, $_COOKIE['admin_auth'])) {
        $isLoggedIn = true;
    }
}

$editId = (string) ($_GET['edit'] ?? '');
$editArticle = $editId !== '' ? find_article_by_id($editId) : null;
$articles = load_articles(false);

if (($_GET['success'] ?? '') === 'save') {
    $message = 'Artikel berhasil disimpan. Perubahan sedang di-commit ke GitHub dan akan otomatis ter-deploy dalam 1 menit.';
} elseif (($_GET['success'] ?? '') === 'delete') {
    $message = 'Artikel berhasil dihapus dan perubahan sedang di-commit ke GitHub.';
} elseif (($_GET['success'] ?? '') === 'login') {
    $message = 'Login berhasil. Silakan kelola artikel sekolah.';
}

$title = 'Dashboard Admin - Yayasan Cendekia';
$description = 'Dashboard admin artikel Yayasan Cendekia.';

require __DIR__ . '/../includes/header.php';
?>
<main>
    <section class="bg-slate-950 py-14 text-white sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-wide text-amber-200">Dashboard Admin</p>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Kelola artikel website.</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-200">Tambah, edit, publikasikan, atau simpan draft artikel sekolah dari satu tempat.</p>
        </div>
    </section>

    <section class="bg-slate-50 py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <?php if ($message !== ''): ?>
                <p class="mb-6 rounded-md bg-amber-50 px-4 py-3 text-sm font-bold text-orange-800 ring-1 ring-amber-200"><?= e($message); ?></p>
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
                        <input type="password" name="password" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100" required>
                    </label>
                    <button type="submit" class="mt-5 w-full rounded-md bg-orange-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-700">Masuk Dashboard</button>
                    <p class="mt-4 text-xs leading-6 text-slate-500">Default lokal: cendekia-admin. Untuk production, set environment variable ADMIN_PASSWORD.</p>
                </form>
            <?php else: ?>
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-950"><?= $editArticle ? 'Edit artikel' : 'Tambah artikel baru'; ?></h2>
                        <p class="mt-1 text-sm text-slate-600">Artikel berstatus Published akan tampil di halaman utama dan halaman artikel.</p>
                    </div>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-amber-300 hover:text-orange-700">Keluar</button>
                    </form>
                </div>

                <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
                    <form method="post" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= e($editArticle['id'] ?? ''); ?>">
                        <input type="hidden" name="current_image" value="<?= e($editArticle['image'] ?? ''); ?>">
                        <div class="grid gap-4">
                            <label class="grid gap-2 text-sm font-bold text-slate-700">
                                Judul
                                <input type="text" name="title" value="<?= e($editArticle['title'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100" required>
                            </label>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="grid gap-2 text-sm font-bold text-slate-700">
                                    Kategori
                                    <input type="text" name="category" value="<?= e($editArticle['category'] ?? 'Kegiatan Sekolah'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100">
                                </label>
                                <label class="grid gap-2 text-sm font-bold text-slate-700">
                                    Status
                                    <select name="status" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100">
                                        <?php $status = $editArticle['status'] ?? 'published'; ?>
                                        <option value="published" <?= $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </label>
                            </div>
                            <label class="grid gap-2 text-sm font-bold text-slate-700">
                                Penulis
                                <input type="text" name="author" value="<?= e($editArticle['author'] ?? 'Admin Yayasan'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100">
                            </label>
                            <div class="grid gap-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                <div>
                                    <p class="text-sm font-bold text-slate-950">Gambar utama</p>
                                    <p class="mt-1 text-xs leading-6 text-slate-600">Upload Gambar Melalui Lokal dari komputer, atau pakai URL gambar jika file sudah ada di internet.</p>
                                    <p class="mt-2 rounded-md bg-white px-3 py-2 text-xs font-semibold leading-6 text-orange-800 ring-1 ring-amber-200">Catatan: jika upload gambar lokal dan URL gambar diisi bersamaan, gambar lokal akan digunakan terlebih dahulu.</p>
                                </div>
                                <?php if (!empty($editArticle['image'])): ?>
                                    <div class="overflow-hidden rounded-lg border border-amber-200 bg-white">
                                        <img src="<?= e($editArticle['image']); ?>" alt="<?= e($editArticle['title'] ?? 'Preview gambar artikel'); ?>" class="h-44 w-full object-cover">
                                    </div>
                                <?php endif; ?>
                                <label class="grid gap-2 text-sm font-bold text-slate-700">
                                    Upload Gambar Melalui Lokal
                                    <input type="file" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-orange-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-orange-700 focus:border-orange-500 focus:ring-4 focus:ring-amber-100">
                                </label>
                                <label class="grid gap-2 text-sm font-bold text-slate-700">
                                    Upload Gambar Melalui Internet
                                    <input type="url" name="image" value="<?= str_starts_with((string) ($editArticle['image'] ?? ''), 'http') ? e($editArticle['image']) : ''; ?>" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100" placeholder="https://...">
                                </label>
                                <?php if (!empty($editArticle['image'])): ?>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                        Hapus gambar dari artikel ini
                                    </label>
                                <?php endif; ?>
                            </div>
                            <label class="grid gap-2 text-sm font-bold text-slate-700">
                                Ringkasan
                                <textarea name="excerpt" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100"><?= e($editArticle['excerpt'] ?? ''); ?></textarea>
                            </label>
                            <label class="grid gap-2 text-sm font-bold text-slate-700">
                                Isi artikel
                                <textarea name="content" rows="12" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-orange-500 focus:ring-4 focus:ring-amber-100" required><?= e($editArticle['content'] ?? ''); ?></textarea>
                            </label>
                        </div>
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <button type="submit" class="rounded-md bg-orange-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-700">Simpan Artikel</button>
                            <?php if ($editArticle): ?>
                                <a href="/admin" class="rounded-md border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-amber-300 hover:text-orange-700">Batal Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-950">Daftar artikel</h2>
                        <div class="mt-5 grid gap-4">
                            <?php foreach ($articles as $article): ?>
                                <article class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-orange-700"><?= e($article['status'] ?? 'draft'); ?> / <?= e($article['category'] ?? 'Artikel'); ?></p>
                                            <h3 class="mt-2 text-base font-bold text-slate-950"><?= e($article['title'] ?? 'Tanpa judul'); ?></h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-600"><?= e(article_plain_excerpt($article, 120)); ?></p>
                                        </div>
                                        <div class="flex shrink-0 gap-2">
                                            <a href="/admin?edit=<?= e($article['id'] ?? ''); ?>" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-amber-300 hover:text-orange-700">Edit</a>
                                            <form method="post" onsubmit="return confirm('Hapus artikel ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e($article['id'] ?? ''); ?>">
                                                <button type="submit" class="rounded-md border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                            <?php if ($articles === []): ?>
                                <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Belum ada artikel.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
