<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nasza wspólna komunistyczna chmura!</title>
    <style>
        body {
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-image: url('tlo.jpg');
            background-repeat: repeat;
            margin: 0;
            padding: 20px;
        }
        form, #tex_new_name {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            width: 100%;
            max-width: 500px;
        }
        textarea, input[type="text"], input[type="password"] {
            width: 100%;
            margin-bottom: 10px;
        }
        input[type="submit"], .file-list a, .folder-list a, .rename-button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            font-size: 16px;
            color: white;
            background-color: #333;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover, .file-list a:hover, .folder-list a:hover, .rename-button:hover {
            background-color: #111;
        }
        .delete-button {
            background-color: red;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin-right: 10px;
        }
        .delete-button:hover {
            background-color: darkred;
        }
        .rename-input {
            margin-left: 10px;
            padding: 5px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #333;
            width: 150px;
        }
        .rename-button {
            background-color: #333;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin-left: 10px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .rename-button:hover {
            background-color: #111;
        }
    </style>
</head>
<body>
    <h1>Nasza wspólna komunistyczna chmura!</h1>

    <?php
    session_start();
    ob_start();

    $password = '12358';

    if (isset($_POST['login'])) {
        if ($_POST['haslo'] === $password) {
            $_SESSION['zalogowany'] = true;
        } else {
            echo "<p>Błędne hasło. Spróbuj ponownie.</p>";
        }
    }

    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if (!isset($_SESSION['zalogowany'])) {
        echo '<form action="" method="post">
                <label for="haslo">Podaj hasło:</label><br>
                <input type="password" name="haslo" id="haslo"><br><br>
                <input type="submit" name="login" value="Zaloguj">
              </form>';
    } else {
        ?>

        <form action="" method="get"> <input type="submit" name="logout" value="Wyloguj" class="logout-button"> </form>

        <form action="" method="post">
            <label for="tekst">Wpisz swój tekst:</label><br>
            <textarea name="tekst" id="tekst"></textarea><br><br>
            <input type="submit" name="zapisz_tekst" value="Zapisz tekst">
        </form>

        <form action="" method="post" enctype="multipart/form-data">
            <label for="plik">Wybierz pliki do przesłania:</label><br>
            <input type="file" name="plik[]" id="plik" multiple><br><br>
            <input type="submit" name="przeslij_plik" value="Prześlij pliki">
        </form>

        <form action="" method="post">
        <label for="folder">Nazwa nowego folderu:</label><br>
        <input type="text" name="folder" id="folder"><br><br>
        <input type="submit" name="utworz_folder" value="Utwórz folder">
        </form>

        <script>
        function zmienNazwe(item_path) {
            const newName = prompt("Podaj nową nazwę (z rozszerszeniem) dla " + item_path);
            if (newName) {
                const form = document.createElement('form');
                form.method = 'post'; form.action = '';
                const oldNameInput = document.createElement('input');
                oldNameInput.type = 'hidden'; oldNameInput.name = 'old_name';
                oldNameInput.value = item_path;
                const newNameInput = document.createElement('input');
                newNameInput.type = 'hidden';
                newNameInput.name = 'new_name'
                newNameInput.value = newName;
                const renameInput = document.createElement('input');
                renameInput.type = 'hidden';
                renameInput.name = 'rename';
                renameInput.value = 'rename';
                form.appendChild(oldNameInput);
                form.appendChild(newNameInput);
                form.appendChild(renameInput);
                document.body.appendChild(form);
                form.submit();
            } 
        }
        </script>

        <script>
        function confirmDelete() {
            return confirm("aby na pewno? na 100% chcesz USUNĄĆ TEN PLIK NA ZAWSZE!?");
        }
        </script>


        <?php

        $current_dir = isset($_GET['dir']) ? $_GET['dir'] : 'uploads';

        function zipFolder($folderPath, $zipFilePath) {
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath), RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($folderPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                return true;
            } else {
                return false;
            }
        }

        if (isset($_GET['download']) && is_dir($_GET['download'])) {
            $folder_to_zip = $_GET['download'];
            $zip_file = $folder_to_zip . '.zip';
                if (zipFolder($folder_to_zip, $zip_file)) {
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
                    readfile($zip_file);
                    unlink($zip_file);
                    exit();
            } else {
                echo "Nie udało się spakować folderu.";
            }
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $redirect_url = $_SERVER['PHP_SELF'] . "?dir=" . urlencode($current_dir);

            if (isset($_POST["zapisz_tekst"])) {
                $tekst = $_POST["tekst"];
                $plik = $current_dir . "/tekst_" . time() . ".txt";
                file_put_contents($plik, $tekst);
                header("Location: " . $redirect_url);
                exit();
            }

            if (isset($_POST["przeslij_plik"]) && isset($_FILES["plik"])) {
                $files = $_FILES["plik"];
                $num_files = count($files["name"]);
                $upload_dir = $current_dir . "/";

                for ($i = 0; $i < $num_files; $i++) {
                    $plik_tmp = $files["tmp_name"][$i];
                    $plik_nazwa = basename($files["name"][$i]);
                    $cel = $upload_dir . $plik_nazwa;

                    if (move_uploaded_file($plik_tmp, $cel)) {
                        echo "Plik " . htmlspecialchars($plik_nazwa) . " został przesłany pomyślnie.<br>";
                    } else {
                        echo "Wystąpił problem podczas przesyłania pliku " . htmlspecialchars($plik_nazwa) . ".<br>";
                    }
                }
                header("Location: " . $redirect_url);
                exit();
            }


            if (isset($_POST["utworz_folder"])) {
                $nowy_folder = $current_dir . "/" . $_POST["folder"];
                if (!is_dir($nowy_folder)) {
                    mkdir($nowy_folder, 0777, true);
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    echo "Folder o tej nazwie już istnieje.<br>";
                }
            }

            if (isset($_POST["usun"])) {
                $usun_sciezka = $_POST["usun"];
                if (is_file($usun_sciezka)) {
                    unlink($usun_sciezka);
                    echo "Plik został usunięty.<br>";
                } elseif (is_dir($usun_sciezka)) {
                    function usunFolder($dir) {
                        foreach (scandir($dir) as $item) {
                            if ($item == '.' || $item == '..') continue;
                            $item_path = $dir . DIRECTORY_SEPARATOR . $item;
                            if (is_dir($item_path)) {
                                usunFolder($item_path);
                            } else {
                                unlink($item_path);
                            }
                        }
                        rmdir($dir);
                    }
                    usunFolder($usun_sciezka);
                    echo "Folder został usunięty.<br>";
                }
                header("Location: " . $redirect_url);
                exit();
            }


            if (isset($_POST["rename"])) {
                $old_name = $_POST["old_name"];
                $new_name = $_POST["new_name"];
                $new_name2 = dirname($old_name).DIRECTORY_SEPARATOR.$new_name;
                if (rename($old_name, $new_name2)) {
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    echo "Nie udało się zmienić nazwy pliku lub folderu.<br>";
                }
            }
        }

        ob_end_flush();

        if (is_dir($current_dir)) {
            $items = scandir($current_dir);
            echo "<div class='file-list'><h2>Lista plików i folderów:</h2>";
            if ($current_dir != 'uploads') {
                $parent_dir = dirname($current_dir);
                echo "<div class='folder-list'><a href='" . $_SERVER['PHP_SELF'] . "?dir=" . urlencode($parent_dir) . "'>Powrót do folderu nadrzędnego</a></div>";
            }
            foreach ($items as $item) {
                if ($item != "." && $item != "..") {
                    $item_path = $current_dir . "/" . $item;
                    if (is_dir($item_path)) {
                        echo "<div class='folder-list'>";
                        echo "<form action='' method='post' style='display: inline;' onsubmit='return confirmDelete(\"" . htmlspecialchars($item) . "\")'>
                                <input type='hidden' name='usun' value='" . htmlspecialchars($item_path) . "'>
                                <input type='submit' value='Usuń' class='delete-button'>
                              </form>";
                        echo "<a href='" . $_SERVER['PHP_SELF'] . "?dir=" . urlencode($item_path) . "'>" . htmlspecialchars($item) . "</a>";
                        echo " | <a href='" . $_SERVER['PHP_SELF'] . "?download=" . urlencode($item_path) . "'>Pobierz</a>";
                        echo "<button type='button' onclick='zmienNazwe(\"" . htmlspecialchars($item_path) . "\")' class='rename-button'>Zmień nazwę</button>";
                        echo "</div>";
                    } else {
                        echo "<div>";
                        echo "<form action='' method='post' style='display: inline;' onsubmit='return confirmDelete(\"" . htmlspecialchars($item) . "\")'>
                                <input type='hidden' name='usun' value='" . htmlspecialchars($item_path) . "'>
                                <input type='submit' value='Usuń' class='delete-button'>
                              </form>";
                        echo "<a href='" . $item_path . "' download>" . htmlspecialchars($item) . "</a>";
                        echo " | <button type='button' onclick='zmienNazwe(\"" . htmlspecialchars($item_path) . "\")' class='rename-button'>Zmień nazwę</button>";
                        echo "</div>";
                    }
                }
            }
            echo "</div>";
        }
    }
    ?>
</body>
</html>
