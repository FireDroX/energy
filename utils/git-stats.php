<?php

$cacheFile = __DIR__ . "/uploads/github-statistique/github_stats.json";

if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) > 3600) {

    $url = "https://api.github.com/repos/FireDroX/energy/stats/contributors";

    $options = [
        "http" => [
            "header" => "User-Agent: Monster-Website\r\n"
        ]
    ];

    $context = stream_context_create($options);
    $json = @file_get_contents($url, false, $context);

    if ($json !== false) {

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

        $stats = [
            "commits" => $totalCommits,
            "added" => $totalAdded,
            "deleted" => $totalDeleted
        ];

        file_put_contents($cacheFile, json_encode($stats));
    }
}

$stats = json_decode(file_get_contents($cacheFile), true);
return $stats;
?>