<?php

/**
 * Composant: pied de page et bandeau cookies.
 */

defined('ABSPATH') || exit;
?>
<footer class="pk-login-footer">
    <div class="pk-footer-inner">

        <div class="pk-footer-col">
            <a class="pk-footer-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 12 Q8 6 12 12 Q16 18 20 12" stroke="#F2764C" stroke-width="3" stroke-linecap="round" />
                </svg>
                Pikit
            </a>
            <p class="pk-footer-copy">&copy;&nbsp;Copyright <?php echo esc_html(date('Y')); ?> Pikit</p>
        </div>

        <div class="pk-footer-col">
            <h3>Solutions</h3>
            <div class="pk-footer-links">
                <a href="<?php echo esc_url(home_url('/etudiants/')); ?>">Pour les &eacute;tudiants</a>
                <a href="<?php echo esc_url(home_url('/enseignants/')); ?>">Pour les enseignants</a>
                <a href="<?php echo esc_url(home_url('/administration/')); ?>">Pour l'administration</a>
            </div>
        </div>

        <div class="pk-footer-col">
            <h3>Pikit</h3>
            <div class="pk-footer-links">
                <a href="<?php echo esc_url(home_url('/conditions-generales/')); ?>">Conditions</a>
                <a href="<?php echo esc_url(home_url('/politique-confidentialite/')); ?>">Politique de confidentialit&eacute;</a>
                <a href="<?php echo esc_url(home_url('/mentions-legales/')); ?>">Mentions l&eacute;gales</a>
                <a href="<?php echo esc_url(home_url('/nouveautes/')); ?>">Nouveaut&eacute;s</a>
                <a href="<?php echo esc_url(home_url('/cookies/')); ?>">Cookies</a>
                <a href="<?php echo esc_url(home_url('/partenariat/')); ?>">Programme partenaire</a>
            </div>
        </div>

        <div class="pk-footer-col">
            <h3>Liens utiles</h3>
            <div class="pk-footer-links">
                <a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a>
                <a href="https://x.com" target="_blank" rel="noopener noreferrer">X/Twitter</a>
                <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer">LinkedIn</a>
            </div>
            <div class="pk-lang-switch" aria-label="Choix de la langue">
                <span class="active" title="Fran&ccedil;ais">fr</span>
                <span title="English">en</span>
            </div>
        </div>

    </div>
</footer>

<div class="pk-cookie-banner" id="pk-cookie-banner" role="region" aria-label="Bandeau cookies">
    <p class="pk-cookie-text">
        Nous utilisons des cookies pour am&eacute;liorer votre exp&eacute;rience et analyser le trafic.
        <a href="<?php echo esc_url(home_url('/cookies/')); ?>">En savoir plus</a>
    </p>
    <div class="pk-cookie-actions">
        <button class="pk-cookie-decline" onclick="pkDismissCookies(false)">Refuser</button>
        <button class="pk-cookie-accept" onclick="pkDismissCookies(true)">Accepter</button>
    </div>
</div>
<script>
    (function() {
        var banner = document.getElementById('pk-cookie-banner');
        if (banner && localStorage.getItem('pk_cookie_consent') !== null) {
            banner.classList.add('hidden');
        }
    }());

    function pkDismissCookies(accepted) {
        try {
            localStorage.setItem('pk_cookie_consent', accepted ? '1' : '0');
        } catch (e) {}
        var banner = document.getElementById('pk-cookie-banner');
        if (banner) {
            banner.classList.add('hidden');
        }
    }
</script>