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
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,400italic,300italic,300,600&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <?php if (isset($extra_fonts) && $extra_fonts): ?>
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <?php endif; ?>
    <style>
        .navbar-brand {
            padding: 10px 0;
        }
        .navbar-brand .navbar-logo {
            height: 140px;
            margin-top: -30px;
            margin-bottom: -30px;
            transition: all 0.2s ease;
        }
        @media (max-width: 991px) {
            .navbar-brand .navbar-logo {
                height: 130px;
                margin-top: -26px;
                margin-bottom: -26px;
            }
        }
        @media (max-width: 767px) {
            .navbar-header {
                width: 100%;
                text-align: center;
            }
            .navbar-brand {
                float: none;
                display: inline-block;
                padding: 8px 0;
            }
            .navbar-brand .navbar-logo {
                height: 145px;
                margin-top: -28px;
                margin-bottom: -28px;
            }
        }
        @media (max-width: 479px) {
            .navbar-brand .navbar-logo {
                height: 160px;
                margin-top: -32px;
                margin-bottom: -32px;
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
                    <img src='<?= url('logo.png') ?>' class="navbar-logo" alt="Зубрилка">
                </a>
            </div>
        </div>
    </nav>
