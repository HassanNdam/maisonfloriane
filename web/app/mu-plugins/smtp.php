<?php

/**
 * Plugin Name: SMTP (.env)
 * Description: Envoie les e-mails par le SMTP défini dans le .env (MAIL_HOST, MAIL_PORT…). En local : Mailpit sur 127.0.0.1:1025.
 */

use function Env\env;

if (! env('MAIL_HOST')) {
    return;
}

add_action('phpmailer_init', function ($mailer) {
    $mailer->isSMTP();
    $mailer->Host = env('MAIL_HOST');
    $mailer->Port = (int) (env('MAIL_PORT') ?: 25);
    $mailer->SMTPAuth = (bool) env('MAIL_USERNAME');
    $mailer->Username = (string) env('MAIL_USERNAME');
    $mailer->Password = (string) env('MAIL_PASSWORD');
    $mailer->SMTPSecure = (string) env('MAIL_ENCRYPTION');
    $mailer->SMTPAutoTLS = (bool) env('MAIL_ENCRYPTION');
});

if ($from = env('MAIL_FROM_ADDRESS')) {
    add_filter('wp_mail_from', fn () => $from);
}

if ($name = env('MAIL_FROM_NAME')) {
    add_filter('wp_mail_from_name', fn () => $name);
}
