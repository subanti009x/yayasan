<?php

function registration_default_config(array $school): array
{
    $field = static fn (string $label, string $name, string $type = 'text', string $placeholder = '', bool $required = true, array $options = []): array => [
        'label' => $label, 'name' => $name, 'type' => $type, 'placeholder' => $placeholder, 'required' => $required, 'options' => $options,
    ];
    $name = (string) ($school['name'] ?? '');
    $configs = [
        'TKIT Cendekia' => [
            $field('Nama lengkap', 'entry.794168599', 'text', 'Nama calon siswa'), $field('Nama panggilan', 'entry.627662662', 'text', 'Nama panggilan anak'),
            $field('No. WA', 'entry.1052167666', 'tel', '08xxxxxxxxxx'), $field('Asal TK', 'entry.1697485376', 'text', 'Asal sekolah/TK sebelumnya'),
            $field('Nama Ayah', 'entry.150230774', 'text', 'Nama lengkap Ayah'), $field('Pekerjaan Ayah', 'entry.1280888089', 'text', 'Pekerjaan Ayah'),
            $field('Nama Ibu', 'entry.1615103016', 'text', 'Nama lengkap Ibu'), $field('Pekerjaan Ibu', 'entry.1310556616', 'text', 'Pekerjaan Ibu'),
            $field('Alamat Rumah', 'entry.277342110', 'textarea', 'Alamat domisili lengkap'),
        ],
        'SDIT SABILUL QURAN' => [
            $field('Nama Lengkap', 'entry.1267371777', 'text', 'Nama calon siswa'), $field('Nama Panggilan', 'entry.567078244', 'text', 'Nama panggilan anak'),
            $field('Jenis Kelamin', 'entry.484836580', 'select', '', true, ['Laki-laki', 'Perempuan']), $field('Tempat, Tanggal Lahir', 'entry.149872710', 'text', 'Contoh: Cirebon, 12 Mei 2019'),
            $field('Agama', 'entry.1744331187', 'text', 'Islam'), $field('Kewarganegaraan', 'entry.539583406', 'text', 'WNI'),
            $field('Asal Sekolah', 'entry.1590909408', 'text', 'Asal TK/sekolah sebelumnya'), $field('No. WA', 'entry.676149694', 'tel', '08xxxxxxxxxx'),
            $field('Nama Ayah', 'entry.935546412', 'text', 'Nama lengkap Ayah'), $field('Pendidikan Ayah', 'entry.1717883185', 'text', 'Pendidikan terakhir Ayah'),
            $field('Pekerjaan Ayah', 'entry.2090349951', 'text', 'Pekerjaan Ayah'), $field('Nama Ibu', 'entry.837404399', 'text', 'Nama lengkap Ibu'),
            $field('Pendidikan Ibu', 'entry.1098953204', 'text', 'Pendidikan terakhir Ibu'), $field('Pekerjaan Ibu', 'entry.216663890', 'text', 'Pekerjaan Ibu'),
            $field('Alamat Tempat Tinggal', 'entry.1035227650', 'textarea', 'Alamat lengkap domisili'),
        ],
        'SMPIT TAHFIDZUL QURAN' => [
            $field('Nama Lengkap', 'entry.2055510208', 'text', 'Nama calon siswa'), $field('Nama Panggilan', 'entry.558526172', 'text', 'Nama panggilan anak'),
            $field('Jenis Kelamin', 'entry.324562681', 'select', '', true, ['Laki-laki', 'Perempuan']), $field('Tempat, Tanggal Lahir', 'entry.56417918', 'text', 'Contoh: Cirebon, 12 Mei 2013'),
            $field('Agama', 'entry.810990337', 'text', 'Islam'), $field('Kewarganegaraan', 'entry.2002042407', 'text', 'WNI'),
            $field('Asal Sekolah', 'entry.181234805', 'text', 'Asal sekolah/SD sebelumnya'), $field('No. WA', 'entry.408552828', 'tel', '08xxxxxxxxxx'),
            $field('Nama Ayah', 'entry.1001224564', 'text', 'Nama lengkap Ayah'), $field('Pendidikan Ayah', 'entry.51377260', 'text', 'Pendidikan terakhir Ayah'),
            $field('Pekerjaan Ayah', 'entry.698003970', 'text', 'Pekerjaan Ayah'), $field('Nama Ibu', 'entry.108118824', 'text', 'Nama lengkap Ibu'),
            $field('Pendidikan Ibu', 'entry.137652037', 'text', 'Pendidikan terakhir Ibu'), $field('Pekerjaan Ibu', 'entry.1959004689', 'text', 'Pekerjaan Ibu'),
            $field('Alamat Tempat Tinggal', 'entry.488607707', 'textarea', 'Alamat lengkap tempat tinggal'),
        ],
    ];
    return [
        'title' => 'Formulir Pendaftaran',
        'intro' => 'Isi data calon siswa dengan benar. Admin sekolah akan menindaklanjuti pendaftaran Anda.',
        'notice' => '', 'is_open' => isset($configs[$name]),
        'submit_label' => 'Kirim Pendaftaran',
        'success_message' => 'Terima kasih. Data pendaftaran Anda sudah terkirim.',
        'consent_text' => 'Saya menyetujui pengiriman data pendaftaran kepada admin sekolah untuk proses penerimaan siswa.',
        'fields' => $configs[$name] ?? [],
    ];
}

function registration_config(array $school): array
{
    $config = registration_default_config($school);
    $saved = $school['registration'] ?? [];
    if (!is_array($saved)) return $config;
    foreach (['title', 'intro', 'notice', 'submit_label', 'success_message', 'consent_text', 'is_open', 'fields'] as $key) {
        if (array_key_exists($key, $saved)) $config[$key] = $saved[$key];
    }
    return $config;
}

function render_managed_registration_form(array $school): void
{
    $config = registration_config($school);
    $formUrl = (string) ($school['form_url'] ?? '');
    $action = preg_replace('/\/viewform(\?.*)?$/', '/formResponse', $formUrl) ?: $formUrl;
    $isOpen = !empty($config['is_open']) && $action !== '';
    ?>
    <form class="grid gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm" action="<?= e($action); ?>" method="post" target="_blank" data-google-form>
        <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label>Jangan isi kolom ini<input type="text" name="website" tabindex="-1" autocomplete="off" data-form-honeypot></label></div>
        <div><h3 class="text-xl font-bold text-slate-950"><?= e((string) $config['title']); ?></h3><p class="mt-2 text-sm leading-7 text-slate-600"><?= nl2br(e((string) $config['intro'])); ?></p></div>
        <?php if ((string) $config['notice'] !== ''): ?><div class="rounded-md border border-secondary-200 bg-secondary-50 p-4 text-sm leading-6 text-slate-700"><?= nl2br(e((string) $config['notice'])); ?></div><?php endif; ?>
        <?php foreach ((array) $config['fields'] as $field): ?>
            <?php $type = in_array($field['type'] ?? '', ['text', 'tel', 'email', 'number', 'date', 'textarea', 'select'], true) ? $field['type'] : 'text'; ?>
            <label class="grid gap-2 text-sm font-semibold text-slate-700 <?= $type === 'textarea' ? 'sm:col-span-2' : '' ?>">
                <?= e((string) ($field['label'] ?? '')); ?>
                <?php if ($type === 'textarea'): ?><textarea name="<?= e((string) $field['name']); ?>" rows="3" placeholder="<?= e((string) ($field['placeholder'] ?? '')); ?>" <?= !empty($field['required']) ? 'required' : '' ?> class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"></textarea>
                <?php elseif ($type === 'select'): ?><select name="<?= e((string) $field['name']); ?>" <?= !empty($field['required']) ? 'required' : '' ?> class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><option value="">Pilih salah satu</option><?php foreach ((array) ($field['options'] ?? []) as $option): ?><option><?= e((string) $option); ?></option><?php endforeach; ?></select>
                <?php else: ?><input type="<?= e($type); ?>" name="<?= e((string) $field['name']); ?>" placeholder="<?= e((string) ($field['placeholder'] ?? '')); ?>" <?= !empty($field['required']) ? 'required' : '' ?> class="rounded-md border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-primary-500 focus:ring-4 focus:ring-secondary-100"><?php endif; ?>
            </label>
        <?php endforeach; ?>
        <?php if ($isOpen): ?><label class="flex items-start gap-3 text-xs leading-6 text-slate-600"><input type="checkbox" required class="mt-1 h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"><span><?= e((string) $config['consent_text']); ?></span></label><button type="submit" class="rounded-md bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700"><?= e((string) $config['submit_label']); ?></button><p class="hidden rounded-md bg-secondary-50 px-4 py-3 text-sm font-semibold text-primary-800" data-form-success><?= e((string) $config['success_message']); ?></p>
        <?php else: ?><button type="button" disabled class="rounded-md bg-slate-400 px-5 py-3 text-sm font-bold text-white">Pendaftaran Belum Dibuka</button><?php endif; ?>
    </form>
    <?php
}
