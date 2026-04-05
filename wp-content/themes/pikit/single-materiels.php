<?php
/**
 * Fiche matériel.
 */

if (!defined('ABSPATH')) exit;

get_header();

$equipment_id = get_the_ID();
$gallery = get_field('gallery', $equipment_id) ?: [];
$brand = (string) get_field('brand', $equipment_id);
$model = (string) get_field('model', $equipment_id);
$description = (string) get_field('description', $equipment_id);
$equipment_items = get_field('equipment_items', $equipment_id) ?: [];
$availability_now = function_exists('pikit_get_equipment_available')
    ? pikit_get_equipment_available($equipment_id, wp_date('Y-m-d 00:00:00'), wp_date('Y-m-d 23:59:59'))
    : ['total' => 0, 'usable' => 0, 'reserved' => 0, 'available' => 0];
$is_available = (int) ($availability_now['available'] ?? 0) > 0;
$availability_label = $is_available ? 'Libre' : 'Occupé';
$availability_class = $is_available ? 'is-free' : 'is-busy';
$reservation_error = isset($_GET['reservation_error']) ? sanitize_key((string) wp_unslash($_GET['reservation_error'])) : '';
$reservation_error_message = isset($_GET['reservation_error_message']) ? sanitize_text_field((string) wp_unslash($_GET['reservation_error_message'])) : '';
$reservation_created = isset($_GET['reservation_created']) ? sanitize_key((string) wp_unslash($_GET['reservation_created'])) : '';
$login_url = function_exists('pikit_get_login_page_url') ? pikit_get_login_page_url(['redirect_to' => get_permalink()]) : wp_login_url(get_permalink());
?>
<main class="pk-single-materiel" aria-label="Fiche matériel">
    <section class="pk-single-hero">
        <div class="pk-single-hero-copy">
            <p class="pk-single-eyebrow">Fiche matériel</p>
            <h1 class="pk-single-title"><?php echo esc_html(get_the_title()); ?></h1>
            <?php if ($brand !== '' || $model !== '') : ?>
                <p class="pk-single-meta"><?php echo esc_html(trim($brand . ($brand !== '' && $model !== '' ? ' · ' : '') . $model)); ?></p>
            <?php endif; ?>
            <div class="pk-single-status-row">
                <span class="pk-single-status <?php echo esc_attr($availability_class); ?>"><?php echo esc_html($availability_label); ?></span>
                <span class="pk-single-status-copy">
                    <?php echo esc_html((string) ($availability_now['available'] ?? 0)); ?> disponible<?php echo ((int) ($availability_now['available'] ?? 0) > 1) ? 's' : ''; ?>
                    · <?php echo esc_html((string) ($availability_now['reserved'] ?? 0)); ?> réservé<?php echo ((int) ($availability_now['reserved'] ?? 0) > 1 ? 's' : ''); ?>
                </span>
            </div>
            <?php if ($description !== '') : ?>
                <p class="pk-single-description"><?php echo nl2br(esc_html($description)); ?></p>
            <?php endif; ?>
        </div>

        <aside class="pk-single-panel">
            <div class="pk-single-panel-card">
                <h2>Disponibilité actuelle</h2>
                <p class="pk-single-panel-availability <?php echo esc_attr($availability_class); ?>">
                    <?php echo esc_html($availability_label); ?>
                </p>
                <ul class="pk-single-stats">
                    <li><span>Total</span><strong><?php echo esc_html((string) ($availability_now['total'] ?? 0)); ?></strong></li>
                    <li><span>Utilisables</span><strong><?php echo esc_html((string) ($availability_now['usable'] ?? 0)); ?></strong></li>
                    <li><span>Réservés</span><strong><?php echo esc_html((string) ($availability_now['reserved'] ?? 0)); ?></strong></li>
                </ul>
            </div>
        </aside>
    </section>

    <?php if ($reservation_error !== '' || $reservation_created !== '') : ?>
        <div class="pk-single-notice <?php echo $reservation_error !== '' ? 'is-error' : 'is-success'; ?>">
            <?php if ($reservation_created !== '') : ?>
                Votre réservation a été créée.
            <?php elseif ($reservation_error_message !== '') : ?>
                <?php echo esc_html(rawurldecode($reservation_error_message)); ?>
            <?php else : ?>
                Une erreur est survenue.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="pk-single-grid">
        <div class="pk-single-gallery">
            <?php if (!empty($gallery) && is_array($gallery)) : ?>
                <?php foreach ($gallery as $image) : ?>
                    <?php if (!is_array($image) || empty($image['ID'])) continue; ?>
                    <figure class="pk-single-gallery-item">
                        <?php echo wp_get_attachment_image((int) $image['ID'], 'large', false, ['class' => 'pk-single-gallery-image']); ?>
                    </figure>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="pk-single-gallery-empty">Aucune image disponible</div>
            <?php endif; ?>
        </div>

        <div class="pk-single-specs">
            <section class="pk-single-spec-card">
                <h2>Spécifications</h2>
                <dl class="pk-single-spec-list">
                    <div>
                        <dt>Marque</dt>
                        <dd><?php echo esc_html($brand !== '' ? $brand : '—'); ?></dd>
                    </div>
                    <div>
                        <dt>Modèle</dt>
                        <dd><?php echo esc_html($model !== '' ? $model : '—'); ?></dd>
                    </div>
                    <div>
                        <dt>État</dt>
                        <dd><?php echo esc_html($availability_label); ?></dd>
                    </div>
                </dl>
            </section>

            <section class="pk-single-spec-card">
                <h2>Items physiques</h2>
                <?php if (empty($equipment_items)) : ?>
                    <p class="pk-single-muted">Aucun item détaillé.</p>
                <?php else : ?>
                    <ul class="pk-single-items">
                        <?php foreach ($equipment_items as $item) :
                            $item_name = (string) ($item['name'] ?? '');
                            $item_status = (string) ($item['status'] ?? 'available');
                            $item_label = match ($item_status) {
                                'maintenance' => 'Maintenance',
                                'unavailable' => 'Indisponible',
                                default => 'Libre',
                            };
                            $item_class = match ($item_status) {
                                'maintenance' => 'is-maintenance',
                                'unavailable' => 'is-busy',
                                default => 'is-free',
                            };
                        ?>
                            <li class="pk-single-item-row">
                                <span><?php echo esc_html($item_name !== '' ? $item_name : 'Item'); ?></span>
                                <span class="pk-single-item-badge <?php echo esc_attr($item_class); ?>"><?php echo esc_html($item_label); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="pk-single-spec-card pk-single-reservation-card">
                <h2>Réserver ce matériel</h2>
                <?php if (!is_user_logged_in()) : ?>
                    <p class="pk-single-muted">Connecte-toi pour réserver.</p>
                    <a class="pk-single-login-link" href="<?php echo esc_url($login_url); ?>">Se connecter</a>
                <?php else : ?>
                    <form method="post" class="pk-single-form" data-single-reservation-form novalidate>
                        <?php wp_nonce_field('pikit_create_single_equipment_reservation', 'pikit_nonce'); ?>
                        <input type="hidden" name="pikit_action" value="create_single_equipment_reservation">
                        <input type="hidden" name="equipment_id" value="<?php echo (int) $equipment_id; ?>">

                        <label for="pickup_datetime">Date et heure de retrait</label>
                        <input id="pickup_datetime" name="pickup_datetime" type="datetime-local" required autocomplete="off">

                        <label for="return_datetime">Date et heure de retour</label>
                        <input id="return_datetime" name="return_datetime" type="datetime-local" required autocomplete="off">

                        <p class="pk-single-form-help">Créneaux autorisés : du lundi au vendredi, de 08h30 à 17h30.</p>
                        <div class="pk-single-form-feedback" data-form-feedback aria-live="polite"></div>

                        <button type="submit" class="pk-single-submit">Demander la réservation</button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </section>
</main>

<?php get_footer(); ?>
