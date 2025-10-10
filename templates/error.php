<?php include __DIR__ . '/header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Ошибка</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <p style="font-size: 18px;">
                    <?= isset($message) ? h($message) : 'Произошла ошибка. Пожалуйста, попробуйте позже.' ?>
                </p>
                <p>
                    <a href="<?= url() ?>" class="btn btn-primary">Вернуться на главную</a>
                </p>
            </div>
        </div>

<?php include __DIR__ . '/footer.php'; ?>
