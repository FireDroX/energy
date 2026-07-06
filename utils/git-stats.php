<?php

$url = "https://api.github.com/repos/FireDroX/energy/stats/contributors";

$options = [
    "http" => [
        "header" => "User-Agent: Monster-Website\r\n"
    ]
];

$context = stream_context_create($options);
$json = file_get_contents($url, false, $context);
$data = json_decode($json, true);

$totalCommits = 0;
$totalAdded = 0;
$totalDeleted = 0;

foreach ($data as $contributor) {

    $totalCommits += $contributor["total"];

    foreach ($contributor["weeks"] as $week) {
        $totalAdded += $week["a"];
        $totalDeleted += $week["d"];
    }

}

return [
    "commits" => $totalCommits,
    "added" => $totalAdded,
    "deleted" => $totalDeleted
];
?>