<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= isset($page_title) ? h($page_title) : 'АудиоДуа.Онлайн' ?></title>

    <!-- Bootstrap Core CSS -->
    <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= asset('css/modern-business.css') ?>" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="<?= asset('font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&display=swap&subset=cyrillic' rel='stylesheet'>
    <?php if (isset($extra_fonts) && $extra_fonts): ?>
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <?php endif; ?>
    <style>
        /* Глобальные шрифты для всего сайта */
        body {
            font-family: 'Merriweather', serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Merriweather', serif;
        }
        .btn, button, input, select, textarea {
            font-family: 'Merriweather', serif;
        }
        
        /* Единый стиль для page-header на всех страницах */
        h1.page-header {
            font-family: 'Merriweather', serif;
            font-weight: 900;
        }
        h1.page-header small {
            font-family: 'Merriweather', serif;
            font-weight: 400;
            font-style: italic;
        }
        
        .navbar-brand {
            padding: 15px 0 35px 0;
            float: none;
            display: inline-block;
            margin: 0 auto;
        }
        .container > .navbar-header,
        .container > .navbar-collapse,
        .container-fluid > .navbar-header,
        .container-fluid > .navbar-collapse {
            margin-left: 0;
            margin-right: 0;
        }
        .navbar-header {
            width: 100%;
            float: none;
            text-align: center;
        }
        .navbar-brand .navbar-logo {
            height: 100px;
            margin: 0;
            transition: all 0.2s ease;
        }
        @media (max-width: 991px) {
            .navbar-brand {
                padding: 12px 0 20px 0;
            }
            .navbar-brand .navbar-logo {
                height: 100px;
                margin: 0;
            }
        }
        @media (max-width: 767px) {
            .navbar-brand {
                padding: 12px 0 20px 0;
            }
            .navbar-brand .navbar-logo {
                height: 100px;
                margin: 0;
            }
        }
        @media (max-width: 479px) {
            .navbar-brand {
                padding: 10px 0 18px 0;
            }
            .navbar-brand .navbar-logo {
                height: 100px;
                margin: 0;
            }
        }
    </style>
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-static-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="<?= url() ?>">
                    <img src='<?= url('/images/logo.png') ?>' class="navbar-logo" alt="Зубрилка">
                </a>
            </div>
        </div>
    </nav>
