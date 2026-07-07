<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-950">Kelola FAQ Pendaftaran</h2>
        <p class="mt-1 text-sm text-slate-600">Pertanyaan yang sering ditanyakan akan muncul di halaman FAQ.</p>
    </div>
</div>

<div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
    <form method="post" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-4"><?= $editFaq !== null ? 'Edit FAQ' : 'Tambah FAQ Baru' ?></h3>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="save_faq">
        <input type="hidden" name="id" value="<?= e($editFaqId); ?>">
        
        <div class="grid gap-4">
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Pertanyaan
                <input type="text" name="question" value="<?= e($editFaq['question'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required placeholder="Cth: Bagaimana cara mendaftar?">
            </label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Jawaban
                <textarea name="answer" rows="4" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required placeholder="Jelaskan jawabannya di sini..."><?= e($editFaq['answer'] ?? ''); ?></textarea>
            </label>
        </div>
        
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Simpan FAQ</button>
            <?php if ($editFaq !== null): ?>
                <a href="?tab=faq" class="rounded-md border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-950">Daftar FAQ Saat Ini</h2>
        <div class="mt-5 grid gap-4">
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-950"><?= e($faq['question']) ?></h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600 line-clamp-3"><?= e($faq['answer']) ?></p>
                        </div>
                        <div class="flex shrink-0 gap-2 mt-3 sm:mt-0">
                            <a href="?tab=faq&edit_faq=<?= $index ?>" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Edit</a>
                            <form method="post" onsubmit="return confirm('Hapus FAQ ini?');" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="delete_faq">
                                <input type="hidden" name="id" value="<?= $index ?>">
                                <button type="submit" class="rounded-md border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($faqs)): ?>
                <p class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Belum ada FAQ yang ditambahkan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
