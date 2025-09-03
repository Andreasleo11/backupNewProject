<?php

return [
    'timezone' => 'Asia/Jakarta',

    // Map HR strings to normalized values
    'status_map' => [
        'ALL IN MANAJEMEN' => 'TETAP',
        'ALL IN ASING'     => 'TETAP',
        'KONTRAK GAMA'     => 'MAGANG',
        'TETAP'            => 'TETAP',
        'YAYASAN'          => 'YAYASAN',
        'KONTRAK'          => 'KONTRAK',
        'MAGANG'           => 'MAGANG',
    ],

    // Example branch hints (tune to your data)
    'branch_hints' => [
        'KARAWANG' => 'KARAWANG',
    ],
];
