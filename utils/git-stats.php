<?php

$cacheFile = __DIR__ . "/../uploads/github_stats.json";

if (!file_exists($cacheFile) || (time() - $stats["timestamp"]) > 3600) {

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
            "commits"  => $totalCommits,
            "added"    => $totalAdded,
            "deleted"  => $totalDeleted,
            "timestamp"=> time()
        ];

        file_put_contents(
            $cacheFile,
            json_encode($stats, JSON_PRETTY_PRINT)
        );
    }
}

if (file_exists($cacheFile)) {
    return json_decode(file_get_contents($cacheFile), true);
}

return [
    "commits" => 0,
    "added" => 0,
    "deleted" => 0,
    "timestamp" => 0
];

?>