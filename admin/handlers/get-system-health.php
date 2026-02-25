<?php
/**
 * Prime Beast - System Health Data Provider
 */
header('Content-Type: application/json');

// Real CPU Load (Linux logic, Windows e dummy value dibe)
$cpu_load = 0;
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    $cpu_load = round($load[0] * 10, 1);
} else {
    $cpu_load = rand(5, 15); // Dummy for non-linux
}

$response = [
    'cpu' => $cpu_load,
    'ram' => rand(30, 45), // Memory usage dummy
    'latency' => rand(12, 28) // DB latency dummy in ms
];

echo json_encode($response);