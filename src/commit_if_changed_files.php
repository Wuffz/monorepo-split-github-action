<?php

declare(strict_types=1);


// use like: php/commit_if_changed_files.php "<repository path to push>" "<commit sha-1>" "<branch>"
// $argv[0] is the file name itself
$repositoryPathToPush = $argv[1];
$currentCommitHash = $argv[2];
$branch = $argv[3];


// WARNING! this function happen before we change directory
// if we do this in split repository, the original hash is missing there and it will fail
$commitMessage = createCommitMessage($currentCommitHash);


$formerWorkingDirectory = getcwd();
chdir($repositoryPathToPush);


exec('git add .', $output);
$outputContent = implode(PHP_EOL, $output);
echo $outputContent . PHP_EOL;


exec('git status', $output);
$outputContent = implode(PHP_EOL, $output);
echo $outputContent . PHP_EOL;

// avoids doing the git commit failing if there are no changes to be commit, see https://stackoverflow.com/a/8123841/1348344
exec('git diff-index --quiet HEAD', $output, $hasChangedFiles);

// debug
var_dump($hasChangedFiles);

// 1 = changed files
// 0 = no changed files
if ($hasChangedFiles === 1) {
    note('Adding git commit');

    $message = sprintf('Pushing git commit with "%s" message to "%s"', $commitMessage, $branch);
    note($message);

    exec("git commit --message '$commitMessage'");
    exec('git push --quiet origin ' . $branch);
} else {
    note('No files to change');
}


// restore original directory to avoid nesting WTFs
chdir($formerWorkingDirectory);




// functions

function createCommitMessage(string $commitSha): string
{
    exec("git show -s --format=%B $commitSha", $output);
    return $output[0] ?? '';
}


function note(string $message) {
    echo PHP_EOL . "\033[0;33m[NOTE] " . $message . "\033[0m" . PHP_EOL . PHP_EOL;
}


