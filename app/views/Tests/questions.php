<?php include_once __DIR__.'/../partials/header.php'; ?>

<h1 class="my-5 text-center"><?= __('Question') ?> #<?= $this->data['question']['sort'] ?></h1>
<h2 class="display-4 my-5 text-center"><?= $this->data['question']['question'] ?></h2>
<hr />

<?php if (Messages::hasErrors()): ?>
    <div class="alert alert-danger" role="alert">
        <div class="font-medium text-red-600"><?= __('Whoops! Something went wrong.') ?></div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            <?php foreach (Messages::flashErrors() as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="text-center">
    <a class="btn btn-primary" href="<?= $this->data['prev'] ?>"><?= __('Back') ?></a>
</div>

<div class="card-body">
    <form action="" method="post">
        <?php foreach ($this->data['answers'] as $answer): ?>
            <div class="form-check my-5">
                <input class="form-check-input fs-2" style="margin-top:12px;" type="radio" name="answer"
                        id="answer_<?= $answer['id'] ?>" value="<?= $answer['id'] ?>"<?php
                    if ($this->data['answer_id'] != 0 && bccomp($this->data['answer_id'], $answer['id']) == 0): ?>
                        checked
                    <?php endif; ?> />
                <label class="form-check-label fs-2" for="answer_<?= $answer['id'] ?>">
                    <?= $answer['answer'] ?>
                </label>
            </div>
        <?php endforeach; ?>

        <div class="text-center">
            <input class="btn btn-primary" type="submit" name="next" value="<?= __('Next') ?>" />
        </div>
    </form>
</div>

<div class="progress mt-5">
    <div class="progress-bar" role="progressbar" style="width: <?= $this->data['progress_bar_value'] ?>%;"
        aria-valuenow="<?= $this->data['progress_bar_value'] ?>" aria-valuemin="0" aria-valuemax="100">
            <?= $this->data['progress_bar_value'] ?>% <?= __('completed') ?>
    </div>
</div>

<?php include_once __DIR__.'/../partials/footer.php'; ?>
