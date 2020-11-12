<?php

return [
    'secret' => env('LARACAPTCHA_SECRET'),
    'sitekey' => env('LARACAPTCHA_SITEKEY'),
    'form_id' => env('LARACAPTCHA_FORM_ID', 'lara-form'),
    'hidden_field_name' => env('LARACAPTCHA_HIDDEN_FIELD_NAME', 'recaptcha_token')
];
