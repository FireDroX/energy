<?php

function git($cmd){
    $output = shell_exec($cmd . " 2>&1");

    return $output !== null ? trim($output) : "";
}

$commits = (int) git("git rev-list --count HEAD");

$contributors = count(array_filter(explode("\n", git("git shortlog -sn"))));
$log = git("git log --shortstat");

preg_match_all('/(\d+) insertion/', $log, $insertions);
preg_match_all('/(\d+) deletion/', $log, $deletions);

$added = array_sum($insertions[1]);
$deleted = array_sum($deletions[1]);

return [
    "commits" => $commits,
    "contributors" => $contributors,
    "added" => $added,
    "deleted" => $deleted
];