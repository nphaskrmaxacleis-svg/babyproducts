<?php
declare(strict_types=1);

function normalize_login_email(string $email): string
{
    // Remove invisible copy/paste formatting, without accepting malformed UTF-8.
    $clean = preg_replace('/\p{Cf}/u', '', $email);
    if ($clean === null) return '';
    $clean = preg_replace('/\A[\p{Z}\s]+|[\p{Z}\s]+\z/u', '', $clean);
    return strtolower($clean ?? '');
}
