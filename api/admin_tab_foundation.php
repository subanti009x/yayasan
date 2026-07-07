<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-950">Identitas & Kontak Yayasan</h2>
        <p class="mt-1 text-sm text-slate-600">Informasi utama ini akan ditampilkan di header, footer, dan halaman depan website.</p>
    </div>
</div>

<form method="post" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
    <input type="hidden" name="action" value="save_foundation">
    
    <div class="grid gap-6 sm:grid-cols-2">
        <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
            Nama Yayasan
            <input type="text" name="name" value="<?= e($siteData['foundation']['name'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
        </label>
        
        <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
            Tagline Singkat
            <input type="text" name="tagline" value="<?= e($siteData['foundation']['tagline'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
        </label>

        <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
            Deskripsi (Bisa juga jadi Meta Deskripsi SEO)
            <textarea name="description" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?= e($siteData['foundation']['description'] ?? ''); ?></textarea>
        </label>
        
        <label class="grid gap-2 text-sm font-bold text-slate-700">
            Nomor Telepon / WhatsApp
            <input type="text" name="phone" value="<?= e($siteData['foundation']['phone'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="628123456789">
            <span class="text-xs font-normal text-slate-500">Awali dengan 62 tanpa spasi atau tanda +.</span>
        </label>

        <label class="grid gap-2 text-sm font-bold text-slate-700">
            Alamat Email Utama
            <input type="email" name="email" value="<?= e($siteData['foundation']['email'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
        </label>

        <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
            Alamat Lengkap Yayasan
            <textarea name="address" rows="2" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?= e($siteData['foundation']['address'] ?? ''); ?></textarea>
        </label>

        <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
            Link Google Maps Embed (iframe src)
            <input type="text" name="maps_embed" value="<?= e($siteData['foundation']['maps_embed'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="https://www.google.com/maps/embed?...">
        </label>

        <div class="grid gap-4 rounded-lg border border-secondary-200 bg-secondary-50 p-4 sm:col-span-2">
            <div>
                <p class="text-sm font-bold text-slate-950">Foto Utama (Hero Image) Halaman Yayasan</p>
                <p class="mt-1 text-xs leading-6 text-slate-600">Gunakan gambar resolusi tinggi untuk banner.</p>
            </div>
            <?php if (!empty($siteData['foundation']['hero_image'])): ?>
                <div class="overflow-hidden rounded-lg border border-secondary-200 bg-white">
                    <img src="<?= e($siteData['foundation']['hero_image']); ?>" alt="Preview Hero" class="h-44 w-full object-cover">
                </div>
            <?php endif; ?>
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Upload Gambar Melalui Lokal
                <input type="file" name="hero_image_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-primary-700 focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
            </label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Gunakan URL Gambar dari Internet
                <input type="url" name="hero_image_url" value="<?= str_starts_with((string) ($siteData['foundation']['hero_image'] ?? ''), 'http') ? e($siteData['foundation']['hero_image']) : ''; ?>" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="https://...">
            </label>
            <?php if (!empty($siteData['foundation']['hero_image'])): ?>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    Hapus gambar ini
                </label>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-8">
        <button type="submit" class="rounded-md bg-primary-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Simpan Identitas Yayasan</button>
    </div>
</form>
