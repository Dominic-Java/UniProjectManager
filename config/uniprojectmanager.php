<?php

return [
    'force_https' => (bool) env('FORCE_HTTPS', false),
    'expired_project_retention_hours' => (int) env('EXPIRED_PROJECT_RETENTION_HOURS', ((int) env('EXPIRED_PROJECT_RETENTION_DAYS', 1)) * 24),
    'institutional_domains' => array_values(array_filter(array_map('trim', explode(',', env('INSTITUTIONAL_EMAIL_DOMAINS', ''))))),
    'student_domains' => array_values(array_filter(array_map('trim', explode(',', env('STUDENT_EMAIL_DOMAINS', 'gmail.com,yahoo.com,outlook.com,gmx.com,hotmail.com'))))),
    'professor_domains' => array_values(array_filter(array_map('trim', explode(',', env('PROFESSOR_EMAIL_DOMAINS', ''))))),
    'professor_emails' => array_values(array_filter(array_map('trim', explode(',', env('PROFESSOR_EMAIL_WHITELIST', ''))))),
    'admin_emails' => array_values(array_filter(array_map('trim', explode(',', env('ADMIN_EMAILS', ''))))),
];
