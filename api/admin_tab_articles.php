<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-950"><?= $editArticle ? 'Edit artikel' : 'Tambah artikel baru'; ?></h2>
        <p class="mt-1 text-sm text-slate-600">Artikel berstatus Published akan tampil di halaman utama dan halaman artikel.</p>
    </div>
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
                <input type="text" name="title" value="<?= e($editArticle['title'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Kategori
                    <input type="text" name="category" value="<?= e($editArticle['category'] ?? 'Kegiatan Sekolah'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                </label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Status
                    <select name="status" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                        <?php $status = $editArticle['status'] ?? 'published'; ?>
                        <option value="published" <?= $status === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </label>
            </div>
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Penulis
                <input type="text" name="author" value="<?= e($editArticle['author'] ?? 'Admin Yayasan'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
            </label>
            <div class="grid gap-4 rounded-lg border border-secondary-200 bg-secondary-50 p-4">
                <div>
                    <p class="text-sm font-bold text-slate-950">Gambar utama</p>
                    <p class="mt-1 text-xs leading-6 text-slate-600">Upload Gambar Melalui Lokal dari komputer, atau pakai URL gambar jika file sudah ada di internet.</p>
                </div>
                <?php if (!empty($editArticle['image'])): ?>
                    <div class="overflow-hidden rounded-lg border border-secondary-200 bg-white">
                        <img src="<?= e($editArticle['image']); ?>" alt="Preview" class="h-44 w-full object-cover">
                    </div>
                <?php endif; ?>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Upload Gambar Melalui Lokal
                    <input type="file" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-primary-700 focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                </label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Upload Gambar Melalui Internet
                    <input type="url" name="image" value="<?= str_starts_with((string) ($editArticle['image'] ?? ''), 'http') ? e($editArticle['image']) : ''; ?>" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="https://...">
                </label>
                <?php if (!empty($editArticle['image'])): ?>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        Hapus gambar dari artikel ini
                    </label>
                <?php endif; ?>
            </div>
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Ringkasan
                <textarea name="excerpt" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?= e($editArticle['excerpt'] ?? ''); ?></textarea>
            </label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Isi artikel
                <textarea name="content" rows="12" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required><?= e($editArticle['content'] ?? ''); ?></textarea>
            </label>
        </div>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Simpan Artikel</button>
            <?php if ($editArticle): ?>
                <a href="/admin.php?tab=articles" class="rounded-md border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-950">Daftar artikel</h2>
        <form method="get" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
            <input type="hidden" name="tab" value="articles">
            <input type="search" name="article_q" maxlength="100" value="<?= e($articleSearch); ?>" placeholder="Cari artikel" class="rounded-md border border-slate-200 px-3 py-2 text-sm outline-none focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
            <select name="article_status" class="rounded-md border border-slate-200 px-3 py-2 text-sm outline-none focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                <option value="">Semua status</option>
                <option value="published" <?= $articleStatus === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?= $articleStatus === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
            <button type="submit" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
        <div class="mt-5 grid gap-4">
            <?php foreach ($articles as $article): ?>
                <article class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-primary-700"><?= e($article['status'] ?? 'draft'); ?> / <?= e($article['category'] ?? 'Artikel'); ?></p>
                            <h3 class="mt-2 text-base font-bold text-slate-950"><?= e($article['title'] ?? 'Tanpa judul'); ?></h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?= e(article_plain_excerpt($article, 120)); ?></p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <a href="/admin.php?tab=articles&edit=<?= e($article['id'] ?? ''); ?>" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Edit</a>
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
