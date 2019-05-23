#!/usr/bin/php
<?php
$linterRegex = array(
    "/\b(po-group|pogroup|product owner)/i" => "PO group",
    "/\b(scrum\sGroup|scrum-group)/i" => "process group",
    "/\b(backend-group)/i" => "backend group",
    "/\b(frontend-group)/i" => "frontend group",
    "/\b(choose\scitizen\sscreen)/i" => "citizen selection screen",
    "/\b(offline-mode)/i" => "offline mode",
    "/\b(user\sstory|userstory)/i" => "issue",
    "/\b(flutter)/" => "Flutter",
    "/\b(user\sinterface)/" => "UI",
    "/\b(re-design|re\sdesign)/" => "redesign",
    "/\b(client\sapi|client-api)/" => "client API",
    "/\b(api)/" => "API",
    "/\b(Costumers|costomer)/" => "customer",
    "/\b(Stakeholder)/" => "customer",
    "/\b(github|Github|git\hub)/" => "GitHub",
    "/\b(webapi|web-api)/" => "web-API",
    "/\b(multi-platform|multi\splatform)/" => "multiplatform",
    "/\b(multiproject|multi\sproject)/" => "multi-project",
    "/\b\b(bloc)\b/" => "BLoC",
    "/\b(\\cite\{)/" => "\\citep",
    "/\b(giraf|Giraf)/" => "GIRAF",
    "/\b([^(\sf\.eks|fx|\seg|\sex|etc)]\.\s+[a-z])/" => "Capital letter after period",
    "/\b(JSON)/" => "json",
    "/\b(AutoLogin|Auto\sLogin)/i" => "auto-login",
    "/\b(sub\stask|sub-task)/i" => "subtask",
    "/\b(http)/" => "HTTP",
    "/\b(https)/" => "HTTPS",

);


$path = getcwd() . "/";
if ($argc == "2") {
    $path = $argv[1];
}
function getDirContents($dir, &$results = array())
{
    if (is_file($dir)) {
        return $dir;
    }
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            if (stripos($path, ".tex") !== false) {
                $results[] = $path;
            }
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            //$results[] = $path;
        }
    }

    return $results;
}


function CheckAllFiles($files, $linter)
{
    $output = array();
    if (is_array($files)) {
        foreach ($files as $file) {
            CheckFile($file, $linter, $output);
        }
    } else {
        CheckFile($files, $linter, $output);
    }
    return array_unique($output);
}

function CheckFile($file, $linter, &$output = array())
{
    $fh = fopen($file, 'rb');
    $line_nr = 1;
    while ($line = fgets($fh)) {
        foreach ($linter as $regex => $replacement) {
            if (preg_match($regex, $line, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $val = "$file, Line $line_nr:$match[1], '$match[0]' should be '$replacement'";
                    if (!array_key_exists($val, $output)) {
                        $output[] = $val;
                    }
                }
            }
        }
        $line_nr++;
    }
}

$contents = getDirContents($path);
$output = CheckAllFiles($contents, $linterRegex);
foreach ($output as $error) {
    echo "$error\n";
}

echo count($output) . " warning(s) found!";
