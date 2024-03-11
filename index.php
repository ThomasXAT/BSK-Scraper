<?php

define('URL', $_ENV['URL']);

error_reporting(E_ERROR | E_PARSE);

download(extract_links(file_get_contents(ignore_spaces(URL)), 'html'));

function download(array $folders): void
{
    foreach($folders as $folder) {
        if ($folder_contents = file_get_contents(ignore_spaces(URL . $folder))) {
            $tracks = extract_links($folder_contents, 'mp3');
            $section = str_replace(basename($folder), '', $folder);
            $directory = './downloads/' . str_replace('.html', '/', $folder);

            foreach ($tracks as $track) {
                $filename = $directory . basename($track);

                if (!file_exists($filename)) {
                    if ($track_contents = file_get_contents(ignore_spaces(URL . $section . $track))) {
                        if (!file_exists($directory)) {
                            mkdir($directory, 0777, true);
                        }

                        file_put_contents($filename, $track_contents);
                    }
                }
            }
        }
    }
}

function extract_links(string $subject, string $extension): array
{
    return
        preg_match_all(
            '/<a\s[^>]*href="(?!.*?javascript:void\(0\))(.*?\.' . $extension . ')"/i',
            $subject,
            $matches
        ) ?
        $matches[1] :
        array()
    ;
}

function ignore_spaces(string $subject): string
{
    return
        str_replace(
            ' ',
            '%20',
            $subject
        )
    ;
}
