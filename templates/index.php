<?php include __DIR__ . '/header.php'; ?>

    <style>
        .index-page {
            margin-bottom: 52px;
        }
        .index-hero {
            text-align: center;
            margin-bottom: 39px;
        }
        .index-hero h1 {
            margin-bottom: 7px;
            font-size: 48px;
        }
        .index-hero small {
            font-size: 20px;
        }
        .index-card {
            background: #f9fafb;
            border: 1px solid #e7eaef;
            border-radius: 18px;
            padding: 33px 39px;
            box-shadow: 0 13px 33px rgba(20, 56, 97, 0.08);
        }
        .index-card.accent {
            background: #fff6f0;
            color: #553d2b;
            border: 1px solid #ffe0cc;
        }
        .index-card.accent h3 {
            color: #d87339;
            font-size: 28px;
        }
        .index-section-title {
            font-size: 31px;
            font-weight: 600;
            margin-bottom: 23px;
            color: #2c3e50;
        }
        .index-category {
            margin-bottom: 21px;
        }
        .index-category-name {
            font-weight: 700;
            font-size: 23px;
            color: #1a365d;
        }
        .index-category ol {
            padding-left: 26px;
            margin-top: 10px;
        }
        .index-category ol li {
            margin-bottom: 8px;
            font-size: 18px;
        }
        .index-category a {
            color: #2c6ad4;
            font-weight: 500;
            font-size: 18px;
        }
        .index-category a:hover {
            text-decoration: none;
            color: #17479e;
        }
        .index-benefits {
            list-style: none;
            padding: 0;
            margin: 20px 0 0;
        }
        .index-benefits li {
            margin-bottom: 13px;
            padding-left: 29px;
            position: relative;
            font-size: 19px;
        }
        .index-benefits li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #ffb37a;
            font-weight: bold;
            font-size: 22px;
        }
        @media (max-width: 767px) {
            .index-card {
                margin-bottom: 26px;
                padding: 26px;
            }
            .index-section-title {
                font-size: 29px;
            }
        }
    </style>

    <!-- Page Content -->
    <div class="container index-page">

        <!-- Page Heading -->
        <div class="row index-hero">
            <div class="col-md-12">
                <h1 class="page-header">Зубрилка - учи стихи легко!<br>
                <small>Простая, бесплатная, с гибкими настройками и приятным фоновым чтением</small></h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-sm-7">
                <div class="index-card">
                    <h2 class="index-section-title">Содержание</h2>
                    <?php foreach($categories as $category): ?>
                        <div class="index-category">
                            <div class="index-category-name">
                                <?= h($category['sort_order']) ?>. <?= h($category['name']) ?>
                            </div>
                            <ol>
                                <?php foreach($category['poems'] as $poem): ?>
                                    <li>
                                        <a href="<?= url('poem.php?id=' . $poem['id']) ?>">
                                            <?= h($poem['name']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-4 col-sm-5">
                <div class="index-card accent">
                    <h3>Почему это работает</h3>
                    <p>Привет! Зубрилка помогает учить стихи шаг за шагом: короткие фрагменты, повторение и дружелюбная подача.</p>
                    <ul class="index-benefits">
                        <li>Бесплатно и без рекламы</li>
                        <li>Короткие фрагменты для запоминания</li>
                        <li>Зацикливание выбранных строк</li>
                        <li>Регулировка скорости и громкости</li>
                        <li>Красивое дикторское чтение</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

<?php include __DIR__ . '/footer.php'; ?>
