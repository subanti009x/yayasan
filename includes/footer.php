<?php $data = site_data(); ?>
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.2fr_0.8fr_0.8fr] lg:px-8">
            <div>
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-700 text-base font-bold text-white">YC</span>
                    <div>
                        <p class="font-bold text-slate-950">Yayasan Cendekia</p>
                        <p class="text-sm text-slate-500">Pendidikan terpadu untuk keluarga Indonesia.</p>
                    </div>
                </div>
                <p class="mt-5 max-w-xl text-sm leading-7 text-slate-600"><?= e($data['foundation']['description']); ?></p>
            </div>

            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-950">Cendekia Cirebon</h2>
                <div class="mt-4 grid gap-2 text-sm">
                    <?php foreach (schools_by_campus($data, 'cirebon') as $school): ?>
                        <a href="<?= e($school['page']); ?>" class="text-slate-600 transition hover:text-teal-700"><?= e($school['name']); ?></a>
                    <?php endforeach; ?>
                    <span class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-400">Cabang Losari</span>
                    <?php foreach (schools_by_campus($data, 'losari') as $school): ?>
                        <a href="<?= e($school['page']); ?>" class="text-slate-600 transition hover:text-teal-700"><?= e($school['name']); ?></a>
                    <?php endforeach; ?>
                    <a href="faq.php" class="mt-3 text-slate-600 transition hover:text-teal-700">FAQ Pendaftaran</a>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-950">Kontak</h2>
                <div class="mt-4 grid gap-2 text-sm text-slate-600">
                    <p><?= e($data['foundation']['address']); ?></p>
                    <a class="transition hover:text-teal-700" href="mailto:<?= e($data['foundation']['email']); ?>"><?= e($data['foundation']['email']); ?></a>
                    <a class="transition hover:text-teal-700" href="<?= e(whatsapp_url($data['foundation']['phone'], 'Halo Yayasan Cendekia, saya ingin bertanya.')); ?>" target="_blank" rel="noopener">WhatsApp Yayasan</a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-200 px-4 py-5 text-center text-xs text-slate-500">
            &copy; <?= date('Y'); ?> Yayasan Cendekia. Semua hak dilindungi.
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
