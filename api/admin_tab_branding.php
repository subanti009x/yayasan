<?php
$branding = $siteData['branding'] ?? [];
$themeColor = $branding['theme_color'] ?? 'orange';
$logoType = $branding['logo_type'] ?? 'text';
?>
<div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="text-lg font-bold text-slate-800">Branding & Tampilan</h2>
        <p class="text-sm text-slate-500">Kelola logo, warna tema, dan teks antarmuka (UI).</p>
    </div>
    <form method="post" enctype="multipart/form-data" class="p-6">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="save_branding">

        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-900">1. Identitas Visual</h3>
        <div class="grid gap-6 sm:grid-cols-2 mb-8">
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Warna Tema Utama
                <select name="theme_color" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                    <option value="orange" <?= $themeColor === 'orange' ? 'selected' : ''; ?>>Orange (Default)</option>
                    <option value="blue" <?= $themeColor === 'blue' ? 'selected' : ''; ?>>Blue</option>
                    <option value="emerald" <?= $themeColor === 'emerald' ? 'selected' : ''; ?>>Emerald (Hijau)</option>
                    <option value="rose" <?= $themeColor === 'rose' ? 'selected' : ''; ?>>Rose (Merah Muda/Merah)</option>
                    <option value="indigo" <?= $themeColor === 'indigo' ? 'selected' : ''; ?>>Indigo</option>
                    <option value="violet" <?= $themeColor === 'violet' ? 'selected' : ''; ?>>Violet (Ungu)</option>
                    <option value="amber" <?= $themeColor === 'amber' ? 'selected' : ''; ?>>Amber (Kuning)</option>
                </select>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Tipe Logo
                <select name="logo_type" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                    <option value="text" <?= $logoType === 'text' ? 'selected' : ''; ?>>Teks Sederhana</option>
                    <option value="image" <?= $logoType === 'image' ? 'selected' : ''; ?>>Gambar Custom</option>
                </select>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Teks Logo (Jika Tipe Teks)
                <input type="text" name="logo_text" value="<?= e($branding['logo_text'] ?? 'YC'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="YC">
            </label>

            <div class="grid gap-2 text-sm font-bold text-slate-700">
                <span>Logo Gambar (Jika Tipe Gambar)</span>
                <?php if (!empty($branding['logo_image'])): ?>
                    <div class="mb-2">
                        <img src="/<?= e($branding['logo_image']); ?>" alt="Logo Saat Ini" class="h-16 rounded-md bg-slate-100 object-contain p-2">
                    </div>
                <?php endif; ?>
                <input type="file" name="logo_upload" accept="image/jpeg, image/png, image/webp, image/gif" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                <label class="mt-2 flex items-center gap-2 font-normal">
                    <input type="checkbox" name="remove_logo" value="1" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-600">
                    Hapus logo gambar saat ini
                </label>
            </div>
        </div>

        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-900 border-t border-slate-200 pt-8">2. Teks Antarmuka (UI)</h3>
        <div class="grid gap-6 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Sub-teks Header (Bawah Nama Yayasan)
                <input type="text" name="text_header_sub" value="<?= e($branding['text_header_sub'] ?? 'Sekolah Indonesia'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
            </label>
            
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Teks Tombol Hubungi Kami
                <input type="text" name="text_contact_button" value="<?= e($branding['text_contact_button'] ?? 'Hubungi Kami'); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Slogan Footer
                <textarea name="text_footer_tagline" rows="2" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?= e($branding['text_footer_tagline'] ?? 'Pendidikan terpadu untuk keluarga Indonesia.'); ?></textarea>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Teks Copyright Footer
                <textarea name="text_footer_copyright" rows="2" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?= e($branding['text_footer_copyright'] ?? '&copy; {year} Yayasan Cendekia. Semua hak dilindungi.'); ?></textarea>
                <span class="text-xs font-normal text-slate-500">Gunakan <code>{year}</code> untuk tahun saat ini.</span>
            </label>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-5">
            <button type="submit" class="rounded-md bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
