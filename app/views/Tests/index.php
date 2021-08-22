<?php include_once __DIR__.'/../partials/header.php'; ?>

<h1 class="my-5 text-center"><?= __('Welcome!') ?></h1>
<h2 class="display-4 my-5 text-center"><?= __('Pick a name and test.') ?></h2>
<hr />
<div class="col-md-4 offset-md-4">
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

    <div class="card">
        <div class="card-body">
            <form action="" method="post">
                <div class="mb-3 form-group required">
                    <label class="form-label control-label" for="username"><?= __('User name') ?>:</label>
                    <input class="form-control" type="text" name="username" value="<?php
                        if (array_key_exists('username', $this->data['submit'])):
                            echo trim($this->data['submit']['username']);
                        elseif ($this->data['user']):
                            echo $this->data['user']['name'];
                        endif; ?>" />
                </div>

                <div class="mb-3 form-group">
                    <label class="form-label control-label" for="test_id"><?= __('Test') ?>:</label>
                    <select class="form-select" name="test_id">
                        <option value="">- <?= __('Choose one') ?> -</option>
                        <?php foreach ($this->data['tests'] as $test): ?>
                            <option value="<?= $test['id'] ?>"<?php
                                if ((array_key_exists('test_id', $this->data['submit'])
                                        && bccomp($this->data['submit']['test_id'], $test['id']) == 0)
                                        || ($this->data['user']
                                            && bccomp($this->data['user']['test_id'], $test['id']) == 0)): ?>
                                    selected
                                <?php endif;?>><?= $test['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input class="btn btn-primary" type="submit" name="start" value="<?php
                    if ($this->data['user']):
                        echo __('Restart');
                    else:
                        echo __('Start');
                    endif; ?>" />
                <?php if ($this->data['continue'] !== ''): ?>
                    <a class="btn btn-primary float-end" href="<?= $this->data['continue'] ?>"><?= __('Continue') ?></a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__.'/../partials/footer.php'; ?>
