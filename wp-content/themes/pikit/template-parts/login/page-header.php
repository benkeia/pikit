<?php

/**
 * Composant: barre de navigation principale.
 */

defined('ABSPATH') || exit;
?>
<header class="pk-header">
    <div class="pk-header-inner">
        <div class="pk-topbar">
            <a class="pk-logo" href="<?php echo esc_url(home_url('/')); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 12 Q8 6 12 12 Q16 18 20 12" stroke="#F2764C" stroke-width="3" stroke-linecap="round" />
                </svg>
                Pikit
            </a>

            <nav class="pk-topbar-nav" aria-label="Navigation principale">
                <a href="<?php echo esc_url(home_url('/materiels/')); ?>">Produit</a>
                <a href="<?php echo esc_url(home_url('/fonctionnalites/')); ?>">Fonctionnalit&eacute;s</a>
                <a href="<?php echo esc_url(home_url('/tarifs/')); ?>">Tarifs</a>
            </nav>

            <div class="pk-topbar-actions">
                <a class="pk-topbar-login" href="<?php echo esc_url(home_url('/connexion/')); ?>">Connexion</a>
                <a class="pk-topbar-cta" href="<?php echo esc_url(home_url('/inscription/')); ?>">Cr&eacute;er un compte gratuit</a>
            </div>
        </div>
    </div>
</header>