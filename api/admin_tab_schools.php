<?php if ($editSchool): ?>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Edit Unit Sekolah: <?= e($editSchool['name']); ?></h2>
            <p class="mt-1 text-sm text-slate-600">Sesuaikan deskripsi, program, pendaftaran, dan visual sekolah ini.</p>
        </div>
        <a href="?tab=schools" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Kembali ke Daftar</a>
    </div>

    <form method="post" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm max-w-4xl">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="save_school">
        <input type="hidden" name="school_id" value="<?= e($editSchoolId); ?>">
        
        <div class="grid gap-6 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
                Nama Lengkap Sekolah
                <input type="text" name="name" value="<?= e($editSchool['name'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" required>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
                Deskripsi Singkat / Pengantar
                <textarea name="description" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?= e($editSchool['description'] ?? ''); ?></textarea>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Tema Warna Visual (Aksen)
                <select name="accent" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                    <?php 
                    $accents = ['orange' => 'Orange', 'amber' => 'Amber', 'yellow' => 'Yellow', 'emerald' => 'Emerald', 'sky' => 'Sky', 'indigo' => 'Indigo', 'rose' => 'Rose'];
                    $currentAccent = $editSchool['accent'] ?? 'amber';
                    foreach ($accents as $val => $label): 
                    ?>
                        <option value="<?= $val ?>" <?= $currentAccent === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700">
                Nomor WhatsApp Admin Sekolah
                <input type="text" name="phone" value="<?= e($editSchool['phone'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="628...">
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
                Link Pendaftaran (Google Form)
                <input type="url" name="form_url" value="<?= e($editSchool['form_url'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="https://docs.google.com/forms/...">
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
                Link Google Maps Embed (iframe src)
                <input type="text" name="maps_embed" value="<?= e($editSchool['maps_embed'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="https://www.google.com/maps/embed?...">
            </label>

            <div class="grid gap-4 rounded-lg border border-secondary-200 bg-secondary-50 p-4 sm:col-span-2">
                <div>
                    <p class="text-sm font-bold text-slate-950">Foto Utama (Hero Image) Halaman Sekolah</p>
                </div>
                <?php if (!empty($editSchool['hero_image'])): ?>
                    <div class="overflow-hidden rounded-lg border border-secondary-200 bg-white">
                        <img src="<?= e($editSchool['hero_image']); ?>" alt="Preview" class="h-44 w-full object-cover">
                    </div>
                <?php endif; ?>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Upload Gambar Lokal
                    <input type="file" name="hero_image_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-primary-700 focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                </label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    URL Gambar Internet
                    <input type="url" name="hero_image_url" value="<?= str_starts_with((string) ($editSchool['hero_image'] ?? ''), 'http') ? e($editSchool['hero_image']) : ''; ?>" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100">
                </label>
                <?php if (!empty($editSchool['hero_image'])): ?>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        Hapus gambar
                    </label>
                <?php endif; ?>
            </div>

            <div class="sm:col-span-2 border-t border-slate-200 pt-6">
                <h3 class="text-lg font-bold text-slate-950 mb-4">Program Unggulan</h3>
                <div id="programs-container" class="grid gap-4">
                    <?php 
                    $programs = $editSchool['programs'] ?? [];
                    if (empty($programs)) $programs[] = ['title' => '', 'description' => ''];
                    foreach ($programs as $index => $program): 
                    ?>
                    <div class="flex flex-col sm:flex-row gap-3 items-start border border-slate-200 p-3 rounded-md bg-slate-50 program-row">
                        <div class="flex-1 grid gap-3 w-full">
                            <input type="text" name="program_title[]" value="<?= e($program['title']) ?>" placeholder="Judul Program (Mis: Literasi Pagi)" class="rounded-md border border-slate-200 px-3 py-2 font-normal outline-none focus:border-primary-500 w-full">
                            <textarea name="program_desc[]" rows="2" placeholder="Deskripsi program..." class="rounded-md border border-slate-200 px-3 py-2 font-normal leading-7 outline-none focus:border-primary-500 w-full"><?= e($program['description']) ?></textarea>
                        </div>
                        <button type="button" onclick="this.closest('.program-row').remove()" class="text-red-500 font-bold px-2 hover:underline">Hapus</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="addProgram()" class="mt-3 text-sm font-bold text-primary-600 hover:underline">+ Tambah Program</button>
            </div>

            <div class="sm:col-span-2 border-t border-slate-200 pt-6 grid sm:grid-cols-2 gap-6">
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Fasilitas (1 per baris)
                    <textarea name="facilities" rows="5" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Ruang Kelas&#10;Perpustakaan"><?= e(implode("\n", $editSchool['facilities'] ?? [])); ?></textarea>
                </label>
                <label class="grid gap-2 text-sm font-bold text-slate-700">
                    Kegiatan Siswa (1 per baris)
                    <textarea name="activities" rows="5" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7 outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Upacara Bendera&#10;Pramuka"><?= e(implode("\n", $editSchool['activities'] ?? [])); ?></textarea>
                </label>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="rounded-md bg-primary-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Simpan Perubahan Unit</button>
            <a href="?tab=schools" class="rounded-md border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Batal Edit</a>
        </div>
    </form>
    <script>
        function addProgram() {
            const container = document.getElementById('programs-container');
            const row = document.createElement('div');
            row.className = 'flex flex-col sm:flex-row gap-3 items-start border border-slate-200 p-3 rounded-md bg-slate-50 program-row';
            row.innerHTML = `
                <div class="flex-1 grid gap-3 w-full">
                    <input type="text" name="program_title[]" value="" placeholder="Judul Program" class="rounded-md border border-slate-200 px-3 py-2 font-normal outline-none focus:border-primary-500 w-full">
                    <textarea name="program_desc[]" rows="2" placeholder="Deskripsi program..." class="rounded-md border border-slate-200 px-3 py-2 font-normal leading-7 outline-none focus:border-primary-500 w-full"></textarea>
                </div>
                <button type="button" onclick="this.closest('.program-row').remove()" class="text-red-500 font-bold px-2 hover:underline">Hapus</button>
            `;
            container.appendChild(row);
        }
    </script>
<?php else: ?>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Daftar Unit Sekolah</h2>
            <p class="mt-1 text-sm text-slate-600">Pilih unit sekolah untuk mengubah konten dan tampilannya.</p>
        </div>
    </div>
    
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($siteData['schools'] ?? [] as $id => $school): ?>
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm flex flex-col overflow-hidden">
                <div class="h-32 bg-slate-100 overflow-hidden relative">
                    <?php if (!empty($school['hero_image'])): ?>
                        <img src="<?= e($school['hero_image']) ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="absolute bottom-3 left-4">
                        <span class="px-2 py-1 text-xs font-bold rounded-md bg-white/90 text-slate-800 shadow-sm"><?= e($school['campus'] === 'losari' ? 'Cabang Losari' : 'Pusat Cirebon') ?></span>
                    </div>
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-slate-900"><?= e($school['short_name']) ?></h3>
                    <p class="mt-1 text-sm text-slate-500 mb-4 line-clamp-2"><?= e($school['description']) ?></p>
                    <div class="mt-auto pt-4 border-t border-slate-100">
                        <a href="?tab=schools&edit_school=<?= e($id) ?>" class="block w-full text-center rounded-md border border-secondary-200 bg-secondary-50 px-4 py-2 text-sm font-bold text-primary-700 transition hover:bg-primary-100">Edit Konten Unit Ini</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
