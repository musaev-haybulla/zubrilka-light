<?php include __DIR__ . '/header.php'; ?>

<style>
.admin-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}
input[type="file"].form-control {
    height: 100%;
    padding: 8px 10px;
    line-height: 1.5;
}
textarea.form-control {
    min-height: 200px;
    font-family: monospace;
}
.btn-primary {
    background: #5cb85c;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
}
.btn-primary:hover {
    background: #4cae4c;
}
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.help-text {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
.format-example {
    background: #fff;
    padding: 15px;
    border-left: 4px solid #5cb85c;
    margin-top: 10px;
    font-family: monospace;
    white-space: pre-line;
}
</style>

<div class="container">
    <div class="admin-container">
        <h2>Добавить новый стих</h2>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category_id">Категория *</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Выберите категорию</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= h($category['id']) ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                            <?= h($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="poem_name">Название стиха *</label>
                <input type="text" name="poem_name" id="poem_name" class="form-control" 
                       value="<?= isset($_POST['poem_name']) ? h($_POST['poem_name']) : '' ?>" 
                       placeholder="Например: Письмо Татьяны к Онегину" required>
            </div>
            
            <div class="form-group">
                <label for="poem_description">Описание</label>
                <textarea name="poem_description" id="poem_description" class="form-control" 
                          rows="3" placeholder="Краткое описание стиха (необязательно)"><?= isset($_POST['poem_description']) ? h($_POST['poem_description']) : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="poem_text">Текст стиха с таймкодами *</label>
                <textarea name="poem_text" id="poem_text" class="form-control" 
                          placeholder="Введите текст..." required><?= isset($_POST['poem_text']) ? h($_POST['poem_text']) : '' ?></textarea>
                <div class="help-text">
                    Формат: каждая строка заканчивается таймкодом (в секундах)
                </div>
                <div class="format-example">Пример:
Я Вам пишу, чего же боле 2.54
Что я могу еще сказать 3.45
Теперь я знаю, в Вашей воле 5.1
Меня презреньем наказать 7
← пустая строка означает конец параграфа
Но вы к моей несчастной доле 8.51
Хоть каплю жалости храня 10.11</div>
            </div>
            
            <div class="form-group">
                <label for="audio_file">Аудиофайл (MP3) *</label>
                <input type="file" name="audio_file" id="audio_file" class="form-control" 
                       accept=".mp3,audio/mpeg" required>
                <div class="help-text">
                    Загрузите MP3 файл с записью стиха
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" name="add_poem" class="btn-primary">
                    Добавить стих
                </button>
                <a href="<?= url() ?>" style="margin-left: 15px;">Вернуться на главную</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
