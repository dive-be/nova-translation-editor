<?php declare(strict_types=1);

return [
    /*
     * Your app's default locale
     */
    'default_locale' => 'en',

    /*
     * The languages the should be translatable via nova
     */
    'supported_locales' => [
        'en',
    ],

    /*
     * Specify which files don't need to be translatable via nova
     */
    'exclude' => [
        /*
        'auth',
        'validation',
        'dive/cases',
        'dive/services',
        */
    ],
];
