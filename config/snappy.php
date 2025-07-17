<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"', // 👈 Quotes inside string
        'timeout' => false,
        'options' => [
            'encoding' => 'UTF-8',
        ],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"',
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],


];
