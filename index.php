<!DOCTYPE html>
<?
    define('URL', $_ENV['URL']);

    error_reporting(E_ERROR | E_PARSE);

    function extract_sections(): array
    {
        $folders = extract_links(file_get_contents(ignore_spaces(URL)), 'html');
        $sections = array();
        
        foreach($folders as $folder) {
            $sections[] = str_replace(basename($folder), '', $folder);
        }

        return array_unique($sections);
    }

    function extract_folders($sections): array
    {
        $folders = array();

        foreach (extract_links(file_get_contents(ignore_spaces(URL)), 'html') as $folder) {
            $section = str_replace(basename($folder), '', $folder);

            if (in_array($section, $sections)) {
                $folders[] = $folder;
            }
        }
        
        return $folders;
    }

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
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSK Scraper</title>
</head>
<body>
    <header>
        <h1>BSK Scraper</h1>
        <nav>
            <ul>
                <li><a href="<?= URL ?>"><?= URL ?></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Liste des sections</h2>
        <form id="sections">
<?
    foreach(extract_sections() as $index => $section) {
        echo "<section><input type='checkbox' id='$index' name='$index' class='section' value='$section'><label for='$index'>$section</label></section>\n";      
    }
?>
            <br>
            <input type="submit" id="download" value="Télécharger">
        </form>
    </main>
    <footer>
        <br>
        <div id="response"></div>
<?
    if ($_GET) {
        $sections = implode(', ', $_GET);
        download(extract_folders($_GET));
        echo "<div id='message'><b>Téléchargement terminé.</b></div>\n";
    }
?>
    </footer>
    <script>
        window.history.replaceState({}, document.title, window.location.origin + window.location.pathname);

        const download = document.querySelector("#download");
        const sections = document.querySelectorAll(".section");
        const response = document.querySelector("#response");
        const message = document.querySelector("#message");

        let result;

        sections.forEach(section => {
            section.addEventListener("change", function() {
                result = "";

                sections.forEach(section => {
                    if (section.checked) {
                        result += section.value + ", ";
                    }
                });

                result = result.slice(0, -2);
            });
        });

        download.addEventListener("click", function() {
            if (result) {
                response.innerHTML = `Téléchargement de ${result}...`;
                if (message) {
                    message.remove();
                }
            }
        });
    </script>
</body>
</html>
