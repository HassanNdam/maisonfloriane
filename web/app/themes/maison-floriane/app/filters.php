<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * L'ancienne adresse /notre-histoire/ redirige vers /mon-histoire/ (page renommée le 9 novembre 2026).
 */
add_action('template_redirect', function () {
    if (is_404() && trim(strtok($_SERVER['REQUEST_URI'] ?? '', '?'), '/') === 'notre-histoire') {
        wp_safe_redirect(home_url('/mon-histoire/'), 301);
        exit;
    }
});

/**
 * Le slug de la page ajouté au <body> (ex. « contact ») entre en conflit avec les composants
 * du prototype (.contact, .produit, .legal…) : on le retire.
 */
add_filter('body_class', function ($classes) {
    $slug = is_singular() ? get_post_field('post_name', get_queried_object_id()) : '';

    return $slug ? array_values(array_diff($classes, [$slug])) : $classes;
}, 20);
