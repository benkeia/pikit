<?php

/**
 * Composant: carte d'authentification.
 *
 * @var array $args
 */

defined('ABSPATH') || exit;

$error_message  = isset($args['error_message']) ? (string) $args['error_message'] : '';
$notice_message = isset($args['notice_message']) ? (string) $args['notice_message'] : '';
$redirect_to    = isset($args['redirect_to']) ? (string) $args['redirect_to'] : '';
$is_logged_in   = isset($args['is_logged_in']) ? (bool) $args['is_logged_in'] : false;
$current_user   = isset($args['current_user']) && $args['current_user'] instanceof WP_User ? $args['current_user'] : null;
$logout_url     = isset($args['logout_url']) ? (string) $args['logout_url'] : home_url('/connexion/');
?>
<div class="pk-auth-card" role="main" aria-labelledby="pk-login-title">

    <?php if ($error_message !== '') : ?>
        <div class="pk-alert" role="alert"><?php echo esc_html($error_message); ?></div>
    <?php endif; ?>

    <?php if ($notice_message !== '') : ?>
        <div class="pk-notice"><?php echo esc_html($notice_message); ?></div>
    <?php endif; ?>

    <?php if (!$is_logged_in) : ?>

        <div class="pk-card-header">
            <svg class="pk-card-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <h2 class="pk-card-title" id="pk-login-title">Bon retour&nbsp;!</h2>
            <p class="pk-card-subtitle">
                Pas encore de compte&nbsp;?
                <a href="<?php echo esc_url(home_url('/inscription/')); ?>">S'inscrire</a>.
            </p>
        </div>

        <form method="post" action="<?php echo esc_url(home_url('/connexion/')); ?>" novalidate>

            <div class="pk-field">
                <label for="pikit-login">E-mail</label>
                <input id="pikit-login" type="text" name="log" autocomplete="username" placeholder="Entrez votre e-mail" required>
            </div>

            <div class="pk-field">
                <div class="pk-field-row">
                    <label for="pikit-password">Mot de passe</label>
                    <a class="pk-forgot" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Mot de passe oubli&eacute;&nbsp;?</a>
                </div>
                <div class="pk-input-wrapper">
                    <input id="pikit-password" type="password" name="pwd" autocomplete="current-password" placeholder="Entrez votre mot de passe" required>
                    <button type="button" class="pk-eye-btn" aria-label="Afficher/masquer le mot de passe">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <?php wp_nonce_field('pikit_login_submit', 'pikit_login_nonce'); ?>
            <input type="hidden" name="pikit_action" value="login">
            <?php if ($redirect_to !== '') : ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
            <?php endif; ?>

            <button type="submit">Continuer</button>

            <p class="pk-terms">
                En vous connectant, vous acceptez nos
                <a href="<?php echo esc_url(home_url('/conditions-generales/')); ?>">Conditions d'utilisation</a>.
            </p>

        </form>

    <?php else : ?>

        <div class="pk-card-header">
            <svg class="pk-card-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <h2 class="pk-card-title" id="pk-login-title">Vous &ecirc;tes connect&eacute;</h2>
            <p class="pk-card-subtitle">
                Bonjour <?php echo esc_html($current_user && $current_user->display_name !== '' ? $current_user->display_name : wp_get_current_user()->user_login); ?>.
            </p>
        </div>

        <div class="pk-connected-actions">
            <a class="pk-primary-action" href="<?php echo esc_url(home_url('/materiels/')); ?>">Acc&eacute;der au catalogue</a>
            <a class="pk-secondary-action" href="<?php echo esc_url($logout_url); ?>">Se d&eacute;connecter</a>
        </div>

    <?php endif; ?>

</div>
<script>
    (function() {
        var btn = document.querySelector('.pk-eye-btn');
        var pwd = document.getElementById('pikit-password');
        if (btn && pwd) {
            btn.addEventListener('click', function() {
                pwd.type = pwd.type === 'password' ? 'text' : 'password';
            });
        }
    }());
</script>