</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand brand-light" href="<?= e(url('home')) ?>">
                <span class="brand-mark"><svg viewBox="0 0 40 40" aria-hidden="true"><path d="M4 31 18.5 8 36 31H4Z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="m13 31 7-11 7 11M18.5 8v7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></span>
                <span>camp<span>rent</span><small>GEAR UP. GET OUT.</small></span>
            </a>
            <p>Perlengkapan tepat, persiapan lebih mantap, dan cerita alam yang lebih berkesan.</p>
        </div>
        <div>
            <strong>Jelajahi</strong>
            <a href="<?= e(url('home')) ?>">Beranda</a>
            <a href="<?= e(url('equipment')) ?>">Katalog perlengkapan</a>
            <a href="<?= e(url('articles')) ?>">Jurnal camping</a>
        </div>
        <div>
            <strong>Hubungi kami</strong>
            <a href="mailto:hello@camprent.test">hello@camprent.test</a>
            <a href="tel:+6281200000000">+62 812 0000 0000</a>
            <span>Senin–Sabtu · 08.00–17.00</span>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> CampRent. Dibuat untuk petualangan yang lebih siap.</span>
        <span>PHP Native · MySQL · HTML · CSS · JavaScript</span>
    </div>
</footer>
<script src="assets/js/app.js?v=2.0.0" defer></script>
</body>
</html>
