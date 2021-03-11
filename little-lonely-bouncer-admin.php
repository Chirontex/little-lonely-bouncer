<?php if (!defined('ABSPATH')) die ?>
<div class="container-fluid">
    <div class="mx-auto width-limited">
        <h1 class="h3 text-center my-5">Добавление защиты на страницу</h1>
        <form action="" method="post">
            <div class="mb-3">
                <input type="text" class="form-control form-control-sm" id="llb-page-add-uri" name="llb-page-add-uri" placeholder="Укажите URI страницы" oninput="pageAdditionSubmitCheck();">
            </div>
            <div class="mb-3">
                <textarea name="llb-page-add-passwords" id="llb-page-add-passwords" rows="5" class="form-control form-control-sm" placeholder="Укажите пароли для доступа через запятую" oninput="pageAdditionSubmitCheck();"></textarea>
            </div>
            <?php wp_nonce_field('llb-page-add', 'llb-pages-wpnp') ?>
            <div class="mb-3 text-center">
                <button type="submit" id="llb-page-add-submit" class="button button-primary" disabled="true">Добавить</button>
            </div>
        </form>
    </div>
    <div class="container-fluid mt-5">
        <h3 class="text-center my-5">Защищённые страницы</h3>
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">URI</th>
                    <th class="text-center">Пароли</th>
                </tr>
            </thead>
            <tbody>
            <?= apply_filters('llb-pages-tbody', '') ?>
            </tbody>
        </table>
    </div>
</div>