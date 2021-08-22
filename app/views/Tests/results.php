<?php include_once __DIR__.'/../partials/header.php'; ?>

<h1 class="my-5 text-center">
    <?= __('Congratulations, %1$s! You have completed the test.', $this->data['user']['results']['name']) ?>
</h1>
<h2 class="display-4 my-5 text-center <?php
    $total_answer_count = count($this->data['user']['progress']);

    if ($total_answer_count == $this->data['user']['results']['correct']): ?>
        text-success"><?= __('You have answered all of the %1$d questions correctly!', $total_answer_count) ?>
    <?php else: ?>">
        <?= __('You have answered %1$d of the %2$d questions correctly.',
            $this->data['user']['results']['correct'], $total_answer_count) ?>
    <?php endif; ?>
</h2>

<div class="card-body">
    <ul class="list-group list-group-flush">
        <?php foreach ($this->data['questions'] as $question): ?>
            <li class="list-group-item">
                <div class="my-4">
                    <div class="d-flex w-100 justify-content-between">
                        <h3 class="mb-2 display-6"><?= $question['question'] ?></h3>
                    </div>
            
                    <?php
                    $answer_id = $this->data['user']['progress'][$question['id']]['answer_id'];

                    if ($this->data['user']['progress'][$question['id']]['is_correct']): ?>
                        <div class="d-flex flex-row">
                            <div class="p-2"><i class="text-success bi bi-check-lg"></i></div>
                            <div class="p-1" style="text-align: justify;">
                                <h4 class="text-success"><?= $this->data['answers'][$answer_id]['answer']; ?></h4>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-row">
                            <div class="p-2"><i class="text-danger bi bi-x-lg"></i></div>
                            <div class="p-1" style="text-align: justify;">
                                <h4 class="text-danger"><?= $this->data['answers'][$answer_id]['answer']; ?></h4>
                            </div>
                        </div>

                        <div class="d-flex flex-row">
                            <div class="p-2"><div style="width: 16px;"></div></div>
                            <div class="p-1" style="text-align: justify;">
                                <h4>
                                    <?php foreach ($this->data['answers'] as $answer):
                                        if (bccomp($answer['question_id'], $question['id']) == 0
                                                && $answer['is_correct']):
                                            echo $answer['answer'];
                                            break;
                                        endif;
                                    endforeach;
                                    ?>
                                </h4>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="text-center">
        <a class="btn btn-primary" href="<?= $this->data['home_url']; ?>"><?= __('Home') ?></a>
    </div>
</div>

<?php include_once __DIR__.'/../partials/footer.php'; ?>
