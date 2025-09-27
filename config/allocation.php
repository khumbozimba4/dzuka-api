<?php

return [
    // Scoring weights for artisan allocation (should total 100)
    'weights' => [
        'category_match' => 40,     // Category expertise match
        'availability' => 25,       // Current availability
        'performance' => 20,        // Historical performance
        'location' => 10,          // Geographic proximity
        'workload' => 5            // Current workload balance
    ],

    // Maximum concurrent orders per artisan
    'max_concurrent_orders' => 3,

    // Distance considerations
    'max_allocation_distance_km' => 50, // Maximum distance for allocation
    'location_score_decay_rate' => 2,   // How quickly score decreases with distance

    // Performance scoring
    'new_artisan_default_score' => 70,  // Default score for artisans with no history
    'experience_bonus_per_order' => 0.5, // Bonus points per completed order (max 10)

    // Availability scoring
    'availability_penalty_per_order' => 25, // Score penalty per current order

    // Batch allocation
    'batch_allocation_limit' => 50, // Maximum orders to process in one batch

    // Notification settings
    'notify_artisan_on_allocation' => true,
    'notify_admin_on_failed_allocation' => true,

    // Retry settings
    'allocation_retry_attempts' => 3,
    'allocation_retry_delay_seconds' => 30,
];