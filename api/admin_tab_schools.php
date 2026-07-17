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
                <input type="url" name="form_url" data-google-form-url value="<?= e($editSchool['form_url'] ?? ''); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="https://docs.google.com/forms/...">
                <span class="text-xs font-normal leading-5 text-slate-500" data-google-form-status>Tempel URL Google Form. Field akan dibaca otomatis tanpa menunggu halaman disimpan.</span>
            </label>

            <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">
                Alamat atau Nama Lokasi di Google Maps
                <input type="text" name="maps_location" value="<?= e(admin_maps_location((string) ($editSchool['maps_embed'] ?? ''), (string) ($editSchool['maps_location'] ?? ''))); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100" placeholder="Contoh: TKIT Cendekia, Jl. Pelandakan, Kota Cirebon" required>
                <span class="text-xs font-normal leading-5 text-slate-500">Cukup ketik nama tempat atau alamat lengkap. Sistem akan membuat peta Google Maps otomatis.</span>
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

            <?php foreach (['facility' => ['Fasilitas', $editSchool['facilities'] ?? []], 'activity' => ['Kegiatan Siswa', $editSchool['activities'] ?? []]] as $contentType => [$contentTitle, $contentItems]): ?>
                <div class="sm:col-span-2 border-t border-slate-200 pt-6">
                    <div class="mb-3 flex items-center justify-between"><h3 class="text-lg font-bold text-slate-950"><?= e($contentTitle); ?></h3><button type="button" onclick="addContentCard('<?= e($contentType); ?>')" class="text-sm font-bold text-primary-600 hover:underline">+ Tambah <?= strtolower($contentTitle); ?></button></div>
                    <div id="<?= e($contentType); ?>-cards" class="grid gap-4">
                        <?php foreach ($contentItems as $item): ?><?php $item = is_array($item) ? $item : ['title' => $item, 'description' => '', 'image' => '']; ?>
                            <div class="content-card grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                                <label class="grid gap-1 text-xs font-bold text-slate-600">Judul<input name="<?= e($contentType); ?>_title[]" value="<?= e((string) ($item['title'] ?? '')); ?>" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600">URL Gambar<input type="url" name="<?= e($contentType); ?>_image[]" value="<?= e((string) ($item['image'] ?? '')); ?>" placeholder="https://..." class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Atau upload gambar<input type="file" name="<?= e($contentType); ?>_image_upload[]" accept="image/jpeg,image/png,image/webp,image/gif" class="rounded border border-slate-200 bg-white px-2 py-2 text-sm font-normal"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Deskripsi<textarea name="<?= e($contentType); ?>_description[]" rows="2" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"><?= e((string) ($item['description'] ?? '')); ?></textarea></label>
                                <button type="button" onclick="this.closest('.content-card').remove()" class="justify-self-start text-sm font-bold text-red-600 hover:underline">Hapus</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php $registration = registration_config($editSchool); ?>
            <div class="sm:col-span-2 border-t border-slate-200 pt-6">
                <div class="mb-5">
                    <h3 class="text-lg font-bold text-slate-950">Konfigurasi Formulir Pendaftaran</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Kelola seluruh teks, status, dan field yang tampil pada halaman pendaftaran sekolah ini. Untuk Google Form, gunakan nama field seperti <code>entry.123456</code>.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-slate-700">Judul Formulir<input type="text" name="registration_title" value="<?= e((string) $registration['title']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal"></label>
                    <label class="flex items-center gap-3 self-end pb-3 text-sm font-bold text-slate-700"><input type="checkbox" name="registration_is_open" value="1" <?= !empty($registration['is_open']) ? 'checked' : ''; ?> class="h-4 w-4 rounded border-slate-300 text-primary-600">Pendaftaran online dibuka</label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">Teks Pengantar<textarea name="registration_intro" rows="3" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7"><?= e((string) $registration['intro']); ?></textarea></label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">Pengumuman (opsional)<textarea name="registration_notice" rows="2" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7" placeholder="Contoh: Pendaftaran gelombang 1 sampai 30 Juni."><?= e((string) $registration['notice']); ?></textarea></label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700">Teks Tombol<input type="text" name="registration_submit_label" value="<?= e((string) $registration['submit_label']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal"></label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700">Pesan Berhasil<input type="text" name="registration_success_message" value="<?= e((string) $registration['success_message']); ?>" class="rounded-md border border-slate-200 px-3 py-3 font-normal"></label>
                    <label class="grid gap-2 text-sm font-bold text-slate-700 sm:col-span-2">Teks Persetujuan<textarea name="registration_consent_text" rows="2" class="rounded-md border border-slate-200 px-3 py-3 font-normal leading-7"><?= e((string) $registration['consent_text']); ?></textarea></label>
                </div>
                <div class="mt-6">
                    <div class="mb-3 flex items-center justify-between"><div><h4 class="font-bold text-slate-950">Field Formulir dari Google Form</h4><p class="text-xs text-slate-500">Field dan ID dibaca otomatis. Ubah pertanyaan di Google Form, lalu sinkronkan ulang.</p></div><button type="submit" data-form-action="sync_google_form" class="text-sm font-bold text-primary-600 hover:underline">Sinkronkan Ulang</button></div>
                    <div id="registration-fields" class="grid gap-4">
                        <?php foreach ((array) $registration['fields'] as $field): ?>
                            <div class="registration-field grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                                <label class="grid gap-1 text-xs font-bold text-slate-600">Label<input readonly name="registration_field_label[]" value="<?= e((string) $field['label']); ?>" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600">ID Google Form<input readonly value="<?= e((string) $field['name']); ?>" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"><input type="hidden" name="registration_field_name[]" value="<?= e((string) $field['name']); ?>"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600">Tipe<input readonly name="registration_field_type[]" value="<?= e((string) ($field['type'] ?? 'text')); ?>" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600">Placeholder<input readonly name="registration_field_placeholder[]" value="<?= e((string) ($field['placeholder'] ?? '')); ?>" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"></label>
                                <label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Pilihan, satu per baris<textarea readonly name="registration_field_options[]" rows="2" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"><?= e(implode("\n", (array) ($field['options'] ?? []))); ?></textarea></label>
                                <div class="flex items-center justify-between sm:col-span-2"><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="hidden" name="registration_field_required[<?= e((string) array_search($field, $registration['fields'], true)); ?>]" value="<?= !empty($field['required']) ? '1' : '0'; ?>"><input type="checkbox" disabled <?= !empty($field['required']) ? 'checked' : ''; ?>>Wajib diisi</label><span class="text-xs text-slate-500">Kelola tambah/hapus field dari Google Form.</span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="rounded-md bg-primary-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-primary-700">Simpan Perubahan Unit</button>
            <a href="?tab=schools" class="rounded-md border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-secondary-300 hover:text-primary-700">Batal Edit</a>
        </div>
    </form>
    <form method="post" class="mt-3" onsubmit="return confirm('Hapus unit sekolah ini? Konten unit akan hilang dari website.');">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="delete_school">
        <input type="hidden" name="school_id" value="<?= e($editSchoolId); ?>">
        <button type="submit" class="rounded-md border border-red-200 px-5 py-3 text-sm font-bold text-red-700 hover:bg-red-50">Hapus Unit</button>
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
        /* function addRegistrationField() {
            const index = document.querySelectorAll('.registration-field').length;
            const row = document.createElement('div');
            row.className = 'registration-field grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2';
            row.innerHTML = `<label class="grid gap-1 text-xs font-bold text-slate-600">Label<input name="registration_field_label[]" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label><label class="grid gap-1 text-xs font-bold text-slate-600">Nama field Google Form<input name="registration_field_name[]" placeholder="entry.123456" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label><label class="grid gap-1 text-xs font-bold text-slate-600">Tipe<select name="registration_field_type[]" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"><option value="text">Teks</option><option value="tel">Telepon</option><option value="email">Email</option><option value="number">Angka</option><option value="date">Tanggal</option><option value="textarea">Paragraf</option><option value="select">Pilihan</option></select></label><label class="grid gap-1 text-xs font-bold text-slate-600">Placeholder<input name="registration_field_placeholder[]" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label><label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Pilihan, satu per baris<textarea name="registration_field_options[]" rows="2" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></textarea></label><div class="flex items-center justify-between sm:col-span-2"><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="registration_field_required[${index}]" value="1" checked>Wajib diisi</label><button type="button" onclick="this.closest('.registration-field').remove()" class="text-sm font-bold text-red-600 hover:underline">Hapus field</button></div>`;
            document.getElementById('registration-fields').appendChild(row);
        } */
        function addContentCard(type) {
            const row = document.createElement('div');
            row.className = 'content-card grid gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2';
            row.innerHTML = `<label class="grid gap-1 text-xs font-bold text-slate-600">Judul<input name="${type}_title[]" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label><label class="grid gap-1 text-xs font-bold text-slate-600">URL Gambar<input type="url" name="${type}_image[]" placeholder="https://..." class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></label><label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Atau upload gambar<input type="file" name="${type}_image_upload[]" accept="image/jpeg,image/png,image/webp,image/gif" class="rounded border border-slate-200 bg-white px-2 py-2 text-sm font-normal"></label><label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Deskripsi<textarea name="${type}_description[]" rows="2" class="rounded border border-slate-200 px-2 py-2 text-sm font-normal"></textarea></label><button type="button" onclick="this.closest('.content-card').remove()" class="justify-self-start text-sm font-bold text-red-600 hover:underline">Hapus</button>`;
            document.getElementById(`${type}-cards`).appendChild(row);
        }
        const googleFormUrl = document.querySelector('[data-google-form-url]');
        const googleFormStatus = document.querySelector('[data-google-form-status]');
        let googleFormTimer;
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character]);
        function renderGoogleFields(fields) {
            const container = document.getElementById('registration-fields');
            container.innerHTML = fields.map((field, index) => `<div class="registration-field grid gap-3 rounded-md border border-emerald-200 bg-emerald-50/40 p-4 sm:grid-cols-2"><label class="grid gap-1 text-xs font-bold text-slate-600">Label<input readonly name="registration_field_label[]" value="${escapeHtml(field.label)}" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"></label><label class="grid gap-1 text-xs font-bold text-slate-600">ID Google Form<input readonly value="${escapeHtml(field.name)}" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"><input type="hidden" name="registration_field_name[]" value="${escapeHtml(field.name)}"></label><label class="grid gap-1 text-xs font-bold text-slate-600">Tipe<input readonly name="registration_field_type[]" value="${escapeHtml(field.type)}" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"></label><label class="grid gap-1 text-xs font-bold text-slate-600">Placeholder<input readonly name="registration_field_placeholder[]" value="${escapeHtml(field.placeholder)}" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500"></label><label class="grid gap-1 text-xs font-bold text-slate-600 sm:col-span-2">Pilihan, satu per baris<textarea readonly name="registration_field_options[]" rows="2" class="rounded border border-slate-200 bg-slate-100 px-2 py-2 text-sm font-normal text-slate-500">${escapeHtml((field.options || []).join('\n'))}</textarea></label><div class="sm:col-span-2"><input type="hidden" name="registration_field_required[${index}]" value="${field.required ? '1' : '0'}"><span class="text-xs font-semibold text-emerald-700">✓ Terisi otomatis dari Google Form</span></div></div>`).join('');
        }
        async function previewGoogleForm() {
            const url = googleFormUrl?.value.trim();
            if (!url || !url.includes('docs.google.com/forms/')) return;
            googleFormStatus.textContent = 'Memuat struktur Google Form…';
            googleFormStatus.className = 'text-xs font-semibold leading-5 text-primary-700';
            try {
                const body = new URLSearchParams({ action: 'preview_google_form', csrf_token: document.querySelector('input[name="csrf_token"]').value, form_url: url });
                const response = await fetch('admin.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' }, body });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.message || 'Google Form belum dapat dibaca.');
                renderGoogleFields(data.schema.fields);
                googleFormStatus.textContent = `✓ ${data.schema.fields.length} field berhasil diisi otomatis. Klik Simpan Perubahan Unit untuk menyimpan.`;
                googleFormStatus.className = 'text-xs font-semibold leading-5 text-emerald-700';
            } catch (error) {
                googleFormStatus.textContent = error.message || 'Google Form belum dapat dibaca.';
                googleFormStatus.className = 'text-xs font-semibold leading-5 text-red-700';
            }
        }
        googleFormUrl?.addEventListener('input', () => { clearTimeout(googleFormTimer); googleFormTimer = setTimeout(previewGoogleForm, 700); });
        googleFormUrl?.addEventListener('change', previewGoogleForm);
        document.querySelector('input[name="action"][value="save_school"]')?.closest('form')?.addEventListener('submit', (event) => {
            const actionInput = event.currentTarget.querySelector('input[name="action"]');
            if (actionInput) actionInput.value = event.submitter?.dataset.formAction || 'save_school';
            document.querySelectorAll('.registration-field').forEach((row, index) => {
                const required = row.querySelector('input[type="checkbox"]');
                if (required) required.name = `registration_field_required[${index}]`;
            });
        });
    </script>
<?php else: ?>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Daftar Unit Sekolah</h2>
            <p class="mt-1 text-sm text-slate-600">Pilih unit sekolah untuk mengubah konten dan tampilannya.</p>
        </div>
    </div>
    <form method="post" class="mb-8 grid gap-4 rounded-lg border border-secondary-200 bg-secondary-50 p-5 sm:grid-cols-2">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>"><input type="hidden" name="action" value="create_school">
        <p class="sm:col-span-2 text-lg font-bold text-slate-950">Tambah Unit Sekolah</p>
        <label class="grid gap-1 text-sm font-bold text-slate-700">Nama Unit<input name="new_school_name" required class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal"></label>
        <label class="grid gap-1 text-sm font-bold text-slate-700">Singkatan<input name="new_school_short_name" required class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal" placeholder="Contoh: SMA"></label>
        <label class="grid gap-1 text-sm font-bold text-slate-700">Jenjang<input name="new_school_level" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal" placeholder="Contoh: Sekolah menengah atas"></label>
        <label class="grid gap-1 text-sm font-bold text-slate-700">Lokasi<select name="new_school_campus" class="rounded-md border border-slate-200 bg-white px-3 py-3 font-normal"><option value="cirebon">Cirebon</option><option value="losari">Losari</option></select></label>
        <button type="submit" class="sm:col-span-2 justify-self-start rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white">Buat Unit Sekolah</button>
    </form>
    
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
