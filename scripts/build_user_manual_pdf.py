from __future__ import annotations

from pathlib import Path

from PIL import Image as PILImage
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    Image,
    KeepTogether,
    ListFlowable,
    ListItem,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path("C:/yayasansekolah")
ASSET_DIR = ROOT / "manual-assets"
OUT_DIR = ROOT / "deliverables"
PDF_PATH = OUT_DIR / "Manual_Pengguna_Teknis_Yayasan_Cendekia.pdf"
OUT_DIR.mkdir(exist_ok=True)


FIGURES = {
    "home": ("01-beranda-dashboard.png", "Gambar 1. Dashboard utama halaman Beranda."),
    "unit_cards": ("02-beranda-unit-sekolah.png", "Gambar 2. Daftar unit sekolah pada halaman Beranda."),
    "unit_hero": ("03-halaman-unit-hero.png", "Gambar 3. Halaman profil unit sekolah dan tombol pendaftaran."),
    "registration": ("04-form-pendaftaran.png", "Gambar 4. Formulir pendaftaran online pada halaman unit sekolah."),
    "articles": ("05-artikel.png", "Gambar 5. Halaman artikel sekolah."),
    "faq": ("06-faq.png", "Gambar 6. Halaman FAQ pendaftaran."),
    "contact": ("07-kontak.png", "Gambar 7. Halaman kontak dan peta lokasi."),
    "admin_login": ("08-admin-login.png", "Gambar 8. Form login dashboard admin."),
    "admin_articles": ("09-admin-artikel.png", "Gambar 9. Modul pengelolaan artikel di dashboard admin."),
    "admin_branding": ("10-admin-branding.png", "Gambar 10. Modul Branding & Tampilan."),
    "admin_identity": ("11-admin-identitas.png", "Gambar 11. Modul Identitas & Kontak Yayasan."),
    "admin_school_list": ("12-admin-unit-sekolah.png", "Gambar 12. Daftar unit sekolah di dashboard admin."),
    "admin_school_edit": ("13-admin-edit-unit.png", "Gambar 13. Form edit konten unit sekolah."),
    "admin_faq": ("14-admin-faq.png", "Gambar 14. Modul pengelolaan FAQ pendaftaran."),
}


def styles():
    base = getSampleStyleSheet()
    return {
        "title": ParagraphStyle(
            "TitleManual",
            parent=base["Title"],
            fontName="Helvetica-Bold",
            fontSize=24,
            leading=30,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#0B2545"),
            spaceAfter=12,
        ),
        "subtitle": ParagraphStyle(
            "SubtitleManual",
            parent=base["Normal"],
            fontName="Helvetica",
            fontSize=11,
            leading=16,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#555555"),
            spaceAfter=20,
        ),
        "h1": ParagraphStyle(
            "Heading1Manual",
            parent=base["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=15,
            leading=19,
            textColor=colors.HexColor("#2E74B5"),
            spaceBefore=14,
            spaceAfter=8,
        ),
        "h2": ParagraphStyle(
            "Heading2Manual",
            parent=base["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=12,
            leading=15,
            textColor=colors.HexColor("#1F4D78"),
            spaceBefore=10,
            spaceAfter=6,
        ),
        "body": ParagraphStyle(
            "BodyManual",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.4,
            leading=13,
            alignment=TA_LEFT,
            spaceAfter=6,
        ),
        "caption": ParagraphStyle(
            "CaptionManual",
            parent=base["Normal"],
            fontName="Helvetica-Oblique",
            fontSize=8.5,
            leading=11,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#555555"),
            spaceAfter=9,
        ),
        "note_title": ParagraphStyle(
            "NoteTitle",
            parent=base["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=9,
            leading=12,
            textColor=colors.HexColor("#1F4D78"),
            spaceAfter=2,
        ),
        "note_body": ParagraphStyle(
            "NoteBody",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=8.8,
            leading=12,
            textColor=colors.HexColor("#1F2937"),
        ),
    }


ST = styles()


def para(text: str):
    return Paragraph(text, ST["body"])


def h1(text: str):
    return Paragraph(text, ST["h1"])


def h2(text: str):
    return Paragraph(text, ST["h2"])


def bullet_list(items: list[str], numbered: bool = False):
    flow_items = [ListItem(Paragraph(item, ST["body"]), leftIndent=12) for item in items]
    return ListFlowable(
        flow_items,
        bulletType="1" if numbered else "bullet",
        leftIndent=18,
        bulletFontName="Helvetica",
        bulletFontSize=8.5,
    )


def figure(key: str):
    filename, caption = FIGURES[key]
    path = ASSET_DIR / filename
    with PILImage.open(path) as im:
        width, height = im.size
    max_width = 6.45 * inch
    img_width = max_width
    img_height = img_width * height / width
    if img_height > 4.45 * inch:
        img_height = 4.45 * inch
        img_width = img_height * width / height
    return KeepTogether([
        Image(str(path), width=img_width, height=img_height),
        Paragraph(caption, ST["caption"]),
    ])


def note(title: str, body: str):
    table = Table(
        [[Paragraph(title, ST["note_title"]), Paragraph(body, ST["note_body"])]],
        colWidths=[1.2 * inch, 5.05 * inch],
    )
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), colors.HexColor("#F4F6F9")),
        ("BOX", (0, 0), (-1, -1), 0.5, colors.HexColor("#DADCE0")),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 7),
        ("RIGHTPADDING", (0, 0), (-1, -1), 7),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
    ]))
    return table


def data_table(headers: list[str], rows: list[list[str]], widths: list[float]):
    data = [[Paragraph(h, ST["note_title"]) for h in headers]]
    data.extend([[Paragraph(cell, ST["note_body"]) for cell in row] for row in rows])
    table = Table(data, colWidths=[w * inch for w in widths], repeatRows=1)
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#E8EEF5")),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#0B2545")),
        ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#DADCE0")),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    return table


def footer(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(colors.HexColor("#666666"))
    canvas.drawString(0.85 * inch, 0.45 * inch, "Manual Pengguna Teknis Yayasan Cendekia")
    canvas.drawRightString(7.65 * inch, 0.45 * inch, f"Halaman {doc.page}")
    canvas.restoreState()


def build():
    doc = SimpleDocTemplate(
        str(PDF_PATH),
        pagesize=letter,
        rightMargin=0.85 * inch,
        leftMargin=0.85 * inch,
        topMargin=0.75 * inch,
        bottomMargin=0.7 * inch,
        title="Manual Pengguna Teknis Yayasan Cendekia",
    )
    story = []

    story.append(Paragraph("Manual Pengguna Teknis<br/>Aplikasi Website Yayasan Cendekia", ST["title"]))
    story.append(Paragraph("Panduan operasional untuk pengguna publik dan administrator konten<br/>Versi 1.0 | 13 Juli 2026", ST["subtitle"]))
    story.append(note("Ruang Lingkup", "Dokumen ini disusun berdasarkan aplikasi yang berjalan secara lokal di http://127.0.0.1:8080. Screenshot diambil dari halaman aktual aplikasi dan diberi penanda merah agar tombol, menu, dan area kerja utama mudah dikenali."))
    story.append(Spacer(1, 10))

    sections = [
        ("1. Gambaran Umum Aplikasi", ["Aplikasi Website Yayasan Cendekia digunakan sebagai pusat informasi digital untuk calon orang tua siswa, sekaligus sebagai CMS sederhana bagi admin yayasan. Pengunjung dapat melihat profil yayasan, memilih unit sekolah, membaca artikel, membuka FAQ, menghubungi admin, dan mengisi formulir pendaftaran. Admin dapat memperbarui artikel, identitas yayasan, branding, data unit sekolah, serta FAQ melalui dashboard."], "home"),
        ("2. Prasyarat dan Hak Akses", ["Pengguna publik tidak memerlukan akun untuk membuka website. Akses admin memerlukan password dashboard. Pada lingkungan lokal, aplikasi menggunakan password bawaan apabila variabel ADMIN_PASSWORD belum diatur. Pada production, password dan session secret sebaiknya dikonfigurasi melalui environment variable agar tidak menggunakan nilai bawaan."], "admin_login"),
        ("3. Navigasi Halaman Publik", ["Header website berisi menu Yayasan, unit sekolah Cirebon, Cabang Losari, Artikel, FAQ, Kontak, serta tombol Hubungi Kami. Tombol utama pada beranda mengarahkan pengguna ke daftar unit sekolah atau halaman kontak yayasan."], "home"),
        ("4. Modul Unit Sekolah", ["Beranda menampilkan unit sekolah berdasarkan lokasi layanan. Area Cendekia Cirebon memuat TK/PAUD, SD, SMP, dan SMK. Area Cabang Losari memuat TKIT Cendekia 2 Losari dan SD IT Cendekia 2 Losari. Setiap kartu menyediakan tombol Lihat Sekolah dan Daftar."], "unit_cards"),
        ("4.1 Profil Unit Sekolah", ["Halaman unit sekolah menampilkan nama sekolah, deskripsi singkat, tombol pendaftaran, tombol WhatsApp, program unggulan, fasilitas, kegiatan siswa, formulir pendaftaran, dan peta lokasi. Konten ini bersumber dari data unit sekolah yang dapat diperbarui melalui dashboard admin."], "unit_hero"),
        ("4.2 Formulir Pendaftaran Online", ["Form pendaftaran berada pada bagian Pendaftaran di setiap halaman unit. Data yang diminta dapat berbeda sesuai jenjang, misalnya data calon siswa, nomor WhatsApp, asal sekolah, data orang tua, dan alamat. Untuk sebagian unit, form mengarah ke Google Form yang telah dikonfigurasi pada data sekolah."], "registration"),
        ("5. Modul Artikel", ["Halaman Artikel menampilkan berita, kegiatan, dan informasi sekolah yang berstatus Published. Artikel berisi kategori, judul, ringkasan, gambar utama, penulis, tanggal, dan detail bacaan. Artikel yang masih berstatus Draft tidak ditampilkan di halaman publik."], "articles"),
        ("6. Modul FAQ Pendaftaran", ["Halaman FAQ membantu calon orang tua mendapatkan jawaban cepat sebelum menghubungi admin. Pertanyaan ditampilkan dalam bentuk panel yang dapat dibuka dan ditutup."], "faq"),
        ("7. Modul Kontak", ["Halaman Kontak menampilkan alamat yayasan, email, tombol WhatsApp Yayasan, peta lokasi, serta daftar kontak WhatsApp masing-masing unit sekolah. Modul ini menjadi jalur utama untuk konsultasi pendaftaran dan informasi lokasi."], "contact"),
        ("8. Masuk ke Dashboard Admin", ["Dashboard admin digunakan untuk mengelola konten website. Setelah login, admin akan melihat tab Artikel, Branding & Tampilan, Identitas & Kontak, Unit Sekolah, FAQ, serta tombol Keluar."], "admin_login"),
        ("9. Admin - Modul Artikel", ["Modul Artikel digunakan untuk menambah, mengubah, menerbitkan, menyimpan sebagai draft, atau menghapus artikel. Artikel Published muncul pada halaman Beranda dan halaman Artikel, sedangkan Draft tetap tersimpan di dashboard."], "admin_articles"),
        ("10. Admin - Branding & Tampilan", ["Modul Branding & Tampilan mengatur identitas visual dan teks antarmuka website. Admin dapat memilih warna tema, tipe logo, teks logo, logo gambar, sub-teks header, teks tombol kontak, slogan footer, dan teks copyright."], "admin_branding"),
        ("11. Admin - Identitas & Kontak Yayasan", ["Modul ini mengatur informasi yayasan yang tampil pada beranda, footer, halaman kontak, tombol WhatsApp, dan peta lokasi. Data yang diisi harus konsisten dengan informasi resmi yayasan."], "admin_identity"),
        ("12. Admin - Unit Sekolah", ["Tab Unit Sekolah menampilkan seluruh unit yang tersedia. Admin dapat memilih salah satu unit untuk mengubah nama, deskripsi, warna aksen, nomor WhatsApp, link pendaftaran, peta, gambar hero, program unggulan, fasilitas, dan kegiatan siswa."], "admin_school_list"),
        ("12.1 Edit Konten Unit Sekolah", ["Pada halaman edit unit, admin mengelola konten detail yang akan tampil pada halaman publik sekolah tersebut. Program unggulan dapat ditambah atau dihapus, sedangkan fasilitas dan kegiatan siswa diisi satu item per baris."], "admin_school_edit"),
        ("13. Admin - FAQ Pendaftaran", ["Modul FAQ digunakan untuk menambah, mengedit, dan menghapus pertanyaan yang tampil pada halaman FAQ publik. FAQ sebaiknya berisi pertanyaan yang sering muncul dari calon orang tua siswa."], "admin_faq"),
    ]

    for idx, (heading, paragraphs, fig_key) in enumerate(sections):
        if idx in {3, 8, 12}:
            story.append(PageBreak())
        story.append(h1(heading) if "." not in heading.split(" ")[0] else h2(heading))
        for text in paragraphs:
            story.append(para(text))
        if heading.startswith("4.2"):
            story.append(bullet_list([
                "Masuk ke halaman unit sekolah.",
                "Klik Daftar Sekarang atau gulir ke bagian Pendaftaran.",
                "Isi seluruh kolom wajib sesuai data calon siswa dan orang tua.",
                "Klik Kirim Pendaftaran.",
                "Hubungi admin sekolah melalui WhatsApp apabila perlu verifikasi.",
            ], numbered=True))
        elif heading.startswith("9."):
            story.append(bullet_list([
                "Isi Judul, Kategori, Status, Penulis, Ringkasan, dan Isi artikel.",
                "Tambahkan gambar melalui upload lokal atau URL gambar internet.",
                "Pilih Published jika artikel siap tampil; pilih Draft jika belum selesai.",
                "Gunakan Edit atau Hapus pada daftar artikel untuk mengelola artikel lama.",
            ], numbered=True))
        elif heading.startswith("12.1"):
            story.append(bullet_list([
                "Perbarui nama sekolah, deskripsi, warna aksen, WhatsApp, link pendaftaran, dan Maps Embed.",
                "Kelola Program Unggulan dengan tombol Tambah Program atau Hapus.",
                "Isi Fasilitas dan Kegiatan Siswa satu item per baris.",
                "Klik Simpan Perubahan Unit.",
            ], numbered=True))
        story.append(figure(fig_key))

    story.append(PageBreak())
    story.append(h1("14. Catatan Teknis Operasional"))
    story.append(data_table(
        ["Topik", "Keterangan"],
        [
            ["Penyimpanan", "Saat development dan production, data CMS dibaca langsung dari MySQL/MariaDB."],
            ["Upload gambar", "Gambar tersimpan di tabel cms_uploads dan dilayani melalui endpoint uploads."],
            ["Environment", "Atur DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, ADMIN_PASSWORD, dan SESSION_SECRET di server; tidak ada kredensial bawaan."],
            ["Batas upload", "Maksimal 5 MB untuk JPG, JPEG, PNG, WEBP, atau GIF."],
        ],
        [1.7, 4.7],
    ))
    story.append(Spacer(1, 8))
    story.append(note("Peringatan", "Jangan menyimpan password atau kredensial database dalam source code. Gunakan user database khusus dengan hak akses minimum."))

    story.append(h1("15. Checklist Penggunaan Harian Admin"))
    story.append(bullet_list([
        "Login hanya dari perangkat yang aman.",
        "Periksa kembali status artikel sebelum menyimpan sebagai Published.",
        "Gunakan gambar yang jelas, relevan, dan aman dari sisi hak penggunaan.",
        "Pastikan nomor WhatsApp menggunakan format internasional 62.",
        "Uji halaman publik setelah mengubah link pendaftaran atau peta.",
        "Logout setelah selesai melakukan perubahan.",
    ]))

    story.append(h1("16. Pemecahan Masalah Singkat"))
    story.append(data_table(
        ["Kondisi", "Kemungkinan Penyebab", "Tindakan"],
        [
            ["Tidak bisa login admin", "Password salah atau ADMIN_PASSWORD berbeda.", "Periksa password yang berlaku di environment tersebut."],
            ["Artikel tidak tampil", "Status masih Draft.", "Ubah status menjadi Published dan simpan ulang."],
            ["Gambar gagal diupload", "Ukuran atau format file tidak sesuai.", "Gunakan JPG, PNG, WEBP, atau GIF maksimal 5 MB."],
            ["Peta tidak tampil", "URL Maps Embed salah.", "Gunakan link embed Google Maps, bukan link berbagi biasa."],
            ["Perubahan tidak tersimpan", "Konfigurasi MySQL salah atau tabel belum diimpor.", "Periksa DB_* dan import database/schema.sql."],
        ],
        [1.8, 2.1, 2.5],
    ))

    doc.build(story, onFirstPage=footer, onLaterPages=footer)
    print(PDF_PATH)


if __name__ == "__main__":
    build()
