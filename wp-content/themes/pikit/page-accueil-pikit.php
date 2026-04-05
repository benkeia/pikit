<?php

/**
 * Template Name: Accueil Pikit
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="pk-home" aria-label="Accueil Pikit">

    <section class="pk-hero" aria-labelledby="pk-hero-title">
        <p class="pk-hero-eyebrow">IUT - Materiel audiovisuel</p>
        <h1 class="pk-hero-title" id="pk-hero-title">
            Reservez du materiel.<br>
            <em>Sans les allers-retours.</em>
        </h1>
        <p class="pk-hero-sub">
            Pikit remplace les fichiers Excel et les echanges par email par une plateforme simple
            ou chaque emprunt est trace, valide et confirme en quelques clics.
        </p>
        <div class="pk-hero-actions">
            <a href="<?php echo esc_url(home_url('/connexion/')); ?>" class="pk-btn-primary">Se connecter</a>
        </div>
        <p class="pk-hero-note">Acces reserve aux etudiants et enseignants de l'IUT.</p>
    </section>

    <section class="pk-hero-visual" aria-label="Apercu interface">
        <div class="pk-mockup-shell">
            <div class="pk-mockup-bar" aria-hidden="true">
                <div class="pk-mockup-dot"></div>
                <div class="pk-mockup-dot"></div>
                <div class="pk-mockup-dot"></div>
            </div>
            <div class="pk-mockup-inner">
                <article class="pk-mock-card">
                    <div class="pk-mock-card-img" aria-hidden="true">📷</div>
                    <h3 class="pk-mock-card-name">Canon EOS R50</h3>
                    <span class="pk-mock-badge pk-available">Disponible</span>
                </article>
                <article class="pk-mock-card">
                    <div class="pk-mock-card-img" aria-hidden="true">🎙️</div>
                    <h3 class="pk-mock-card-name">Rode NT-USB</h3>
                    <span class="pk-mock-badge pk-taken">Reserve</span>
                </article>
                <article class="pk-mock-card">
                    <div class="pk-mock-card-img" aria-hidden="true">💡</div>
                    <h3 class="pk-mock-card-name">Kit lumiere LED</h3>
                    <span class="pk-mock-badge pk-available">Disponible</span>
                </article>
            </div>
        </div>
    </section>

    <div class="pk-section-divider" aria-hidden="true"></div>

    <section class="pk-features" aria-labelledby="pk-features-title">
        <p class="pk-section-eyebrow pk-reveal">Fonctionnalites</p>
        <h2 class="pk-section-title pk-reveal" id="pk-features-title">Tout ce dont vous avez besoin,<br>rien de
            superflu.</h2>
        <p class="pk-section-sub pk-reveal">Concu pour les etudiants qui empruntent, et les responsables qui gerent.</p>

        <div class="pk-features-grid">
            <article class="pk-feat-card pk-reveal">
                <div class="pk-feat-icon" aria-hidden="true">📦</div>
                <h3 class="pk-feat-name">Catalogue de materiel</h3>
                <p class="pk-feat-desc">Consultez l'ensemble du parc audiovisuel : appareils photo, micros, eclairages,
                    trepieds et plus encore.</p>
            </article>
            <article class="pk-feat-card pk-reveal">
                <div class="pk-feat-icon" aria-hidden="true">📅</div>
                <h3 class="pk-feat-name">Disponibilite en temps reel</h3>
                <p class="pk-feat-desc">Verifiez instantanement si un equipement est libre sur vos dates, sans avoir a
                    contacter personne.</p>
            </article>
            <article class="pk-feat-card pk-reveal">
                <div class="pk-feat-icon" aria-hidden="true">✅</div>
                <h3 class="pk-feat-name">Validation simplifiee</h3>
                <p class="pk-feat-desc">Votre demande est examinee par le responsable du materiel. Vous recevez une
                    confirmation par email.</p>
            </article>
            <article class="pk-feat-card pk-reveal">
                <div class="pk-feat-icon" aria-hidden="true">🕐</div>
                <h3 class="pk-feat-name">Creneaux horaires IUT</h3>
                <p class="pk-feat-desc">Les retraits et retours s'alignent automatiquement sur les horaires d'ouverture
                    de l'IUT (8h30-17h30).</p>
            </article>
            <article class="pk-feat-card pk-reveal">
                <div class="pk-feat-icon" aria-hidden="true">📋</div>
                <h3 class="pk-feat-name">Suivi de vos emprunts</h3>
                <p class="pk-feat-desc">Retrouvez l'historique complet de vos reservations passees et en cours depuis
                    votre espace personnel.</p>
            </article>
            <article class="pk-feat-card pk-reveal">
                <div class="pk-feat-icon" aria-hidden="true">🔔</div>
                <h3 class="pk-feat-name">Notifications par email</h3>
                <p class="pk-feat-desc">Recevez une confirmation des la validation et un rappel avant la date de retour
                    prevue.</p>
            </article>
        </div>
    </section>

    <section class="pk-workflow" aria-labelledby="pk-workflow-title">
        <div class="pk-workflow-inner">
            <p class="pk-section-eyebrow pk-reveal">Comment ca marche</p>
            <h2 class="pk-section-title pk-reveal" id="pk-workflow-title">De la demande au retour,<br>en quatre etapes.
            </h2>
            <p class="pk-section-sub pk-reveal">Un processus clair, tracable et sans ambiguite pour tout le monde.</p>

            <div class="pk-steps">
                <article class="pk-step pk-reveal">
                    <div class="pk-step-num">1</div>
                    <h3 class="pk-step-title">Je cherche le materiel</h3>
                    <p class="pk-step-desc">Je parcours le catalogue et verifie la disponibilite sur mes dates.</p>
                </article>
                <article class="pk-step pk-reveal">
                    <div class="pk-step-num">2</div>
                    <h3 class="pk-step-title">Je fais ma demande</h3>
                    <p class="pk-step-desc">Je selectionne le creneau et soumets ma reservation en un clic.</p>
                </article>
                <article class="pk-step pk-reveal">
                    <div class="pk-step-num">3</div>
                    <h3 class="pk-step-title">Le responsable valide</h3>
                    <p class="pk-step-desc">La demande est traitee et je recois une confirmation par email.</p>
                </article>
                <article class="pk-step pk-reveal">
                    <div class="pk-step-num">4</div>
                    <h3 class="pk-step-title">Je rends le materiel</h3>
                    <p class="pk-step-desc">A la date prevue, je depose le materiel. La reservation est cloturee.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="pk-cta-section" aria-labelledby="pk-cta-title">
        <h2 class="pk-section-title pk-reveal" id="pk-cta-title">Pret a faire votre<br>premiere reservation ?</h2>
        <p class="pk-section-sub pk-reveal">Connectez-vous avec votre compte IUT et accedez au catalogue en quelques
            secondes.</p>
        <a href="<?php echo esc_url(home_url('/connexion/')); ?>" class="pk-btn-cta pk-reveal">Se connecter</a>
        <p class="pk-cta-tagline">Les comptes sont crees par l'administration de l'IUT.</p>
    </section>

    <footer class="pk-footer">
        <span class="pk-footer-logo">Pikit</span>
        <span class="pk-footer-copy">&copy; <?php echo esc_html(date('Y')); ?> IUT - Projet universitaire</span>
    </footer>
</main>

<?php get_footer(); ?>