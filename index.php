    <?php

    /*error_reporting(E_ALL);
    ini_set('display_errors', true);*/
    date_default_timezone_set('America/Mexico_City');
    setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8');
    require_once './includes/Main.php';

    Main::run(
            array(
                'url' => $_REQUEST['path'],
                'blogData' => array(
                    'title' => 'Examen\'s Blog',
                    'subtitle' => 'Programando por un sueño',
                    'elementsPerPage' => 10
                ),
            )
    );
    ?>
