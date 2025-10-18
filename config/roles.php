<?php

return [
    // Map each role to its dashboard route name
    'redirects' => [
        'admin'      => 'admin.dashboard',
        'patient'    => 'patient.dashboard',
        'dispatcher' => 'dispatcher.dashboard',
        'doctor'     => 'doctor.dashboard',
        'pharmacy'   => 'pharmacy.dashboard',
        'transport'  => 'transport.dashboard',
        'labtech'    => 'labtech.dashboard',
        'health'     => 'health.dashboard', // if you have a health admin area
        'hospital'   => 'hospital.dashboard',
    ],
];
