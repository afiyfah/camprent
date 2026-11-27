<?php http_response_code(404); ?>

<section class="container" style="padding:100px 20px; text-align:center; min-height:60vh;">
    <h1 style="font-size:64px; color:#284c38;">404</h1>

    <h2>Halaman tidak ditemukan.</h2>

    <p style="color:#718078; margin:20px 0;">
        Maaf, halaman yang kamu cari belum tersedia
        atau alamatnya tidak valid.
    </p>

    <a href="<?= e(url('home')) ?>" class="btn btn-primary">
        Kembali ke Beranda
    </a>
</section>
