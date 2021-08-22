<?php include_once __DIR__.'/../partials/header.php'; ?>

<?php if (Messages::hasErrors()): ?>
    <div class="alert alert-danger mt-5" role="alert">
        <div class="font-medium text-red-600"><?= __('Whoops! Something went wrong.') ?></div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            <?php foreach (Messages::flashErrors() as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="text-center mt-5">
    <a class="btn btn-primary" href="<?= $this->data['back_url']; ?>"><?= __('Back') ?></a>
</div>

<?php include_once __DIR__.'/../partials/footer.php'; ?>
