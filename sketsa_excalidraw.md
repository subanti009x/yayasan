# Petunjuk Import Flow ke Excalidraw (Hanya TK & SD Tersedia)

Gunakan kode Mermaid berikut untuk membuat diagram alur di **Excalidraw** yang mencerminkan kondisi saat ini (hanya jenjang TK & SD yang tersedia, serta pemisahan Google Form per cabang).

---

## 1. Salin Kode Mermaid Berikut:

```mermaid
flowchart TD
    A[Pengunjung Masuk Website] --> B[Halaman Beranda Yayasan Cendekia]
    
    B --> C[Halaman Unit Sekolah TK / PAUD]
    B --> D[Halaman Unit Sekolah SD IT]
    B --> E[Halaman Artikel & Informasi]

    C --> C1[TKIT TAHFIDZUL QURAN - Cabang Kota Cirebon]
    C --> C2[TKIT TAHFIDZUL QURAN 2 - Cabang Losari]
    C1 --> F1[Google Form Cabang Cirebon]
    C2 --> F2[Google Form Cabang Losari]

    D --> D1[SD IT Sabilul Quran - Cabang Kota Cirebon]
    D --> D2[SD IT Cendekia 2 - Cabang Losari]
    D1 --> F1
    D2 --> F2
    
    E --> G[(Database JSON Articles)]
    H[Dashboard Admin] -->|Tulis & Edit Artikel| G
    G -->|Tampil Dinamis| E
```

---

## 2. Cara Mengimpor di Excalidraw:

1. Buka [**Excalidraw**](https://excalidraw.com/) di browser Anda.
2. Klik ikon **More Tools** (ikon 3 titik atau kubus) di toolbar -> pilih **Mermaid**.
3. Hapus kode contoh bawaan, lalu **paste** kode di atas.
4. Klik **Insert** untuk me-render kotak dan panah alur secara otomatis ke kanvas.
