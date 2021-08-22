<?php declare(strict_types = 1);

/**
 * A controller to manage tests, show questions, answers, process user input, store and show test results.
 */
class TestsController extends Controller
{

    /**
     * Default URL to redirect user back in case of an error. This will be appended to base URI.
     *
     * @var string
     */
    protected $default_back_url = 'tests/';

    /**
     * Main page if the controller that will display user name input, a test selection and button to start the test.
     *
     * @return array    Return data to view.
     */
    public function index(): array
    {
        $user = $this->getUserData();

        // Process the user submitted values.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Set the rules for validator and add custom error messages for several fields.
            $rules = [
                'username' => [
                    'rules' => [
                        'required' => __('User name is required'),
                        'not_empty' => __('User name cannot be empty')
                    ]
                ],
                'test_id' => [
                    'rules' => [
                        'required' => __('Test is required'),
                        'not_empty' => __('You must choose a test to proceed')
                    ]
                ],
                'start' => [
                    'rules' => [
                        'required' => ''
                    ]
                ]
            ];

            // Validate the fields.
            if ($this->validateRequest($rules)) {
                // Check if test still exits. It can be deleted or wrong ID submitted.
                $test = DB::execute('SELECT id FROM tests WHERE id=?', [$this->getInput('test_id')])->fetch();

                if ($test) {
                    if ($user) {
                        // Type casting necessary is due to unknown MySQL bug that causes bccomp to fail.
                        if (bccomp((string) $user['test_id'], $this->getInput('test_id')) == 0) {
                            // This is the same test ID, but user might have updated his name.
                            DB::execute(
                                'UPDATE users set name=?,updated_at=?'.
                                ' WHERE id=?',
                                [$this->getInput('username'), date('Y-m-d H:i:s'), $user['id']]
                            );
                        }
                        else {
                            /*
                             * User chose to start a different test. This means the previous test was not completed.
                             * Remove the previous user ID and make a new one. The name can be changed or left the same.
                             * If there was a user progress, do not remove it. It can be beneficial for statistics. For
                             * example to see how many users actutally completed the test and how many abandoned it.
                             * But that is not yet implemented.
                             */
                            DB::execute('DELETE FROM users WHERE id=?', [$user['id']]);
                            Session::restart();

                            // Make a new user with new user ID and name.
                            DB::execute(
                                'INSERT INTO users (name,test_id,sessionid,updated_at)'.
                                ' VALUES (?,?,?,?)',
                                [
                                    $this->getInput('username'),
                                    $this->getInput('test_id'),
                                    Session::getId(),
                                    date('Y-m-d H:i:s')
                                ]
                            );
                        }
                    }
                    else {
                        /*
                         * If user did not exist, create a new user with the given name, chosen test ID (only one test
                         * can be active at a time), session ID and add a timestamp.
                         */
                        DB::execute(
                            'INSERT INTO users (name,test_id,sessionid,updated_at)'.
                            ' VALUES (?,?,?,?)',
                            [
                                $this->getInput('username'),
                                $this->getInput('test_id'),
                                Session::getId(),
                                date('Y-m-d H:i:s')
                            ]
                        );
                    }

                    /*
                     * Either a new user was created or existing one has been updated, redirect to same controller
                     * (no need to enter name "TestController", it can be left ampty), but a different another function
                     * (a page) that will handle the questions.
                     */
                    $url = $this->getRouter()->getUrl('', 'questions', [$test['id'], 1]);
                    header('Location: '.$url);
                    exit;
                }
                else {
                    // Set and error that will be displayed in this view.
                    $this->setError(__('Test does not exist'));
                }
            }
        }

        // Get tests for dropdown that have answers and questions.
        $tests = DB::query(
            'SELECT id,name'.
            ' FROM tests t'.
            ' WHERE EXISTS ('.
                'SELECT NULL'.
                ' FROM questions q'.
                ' WHERE q.test_id=t.id'.
            ')'.
            'AND EXISTS ('.
                'SELECT NULL'.
                ' FROM answers a,questions q'.
                ' WHERE a.question_id=q.id AND q.test_id=t.id'.
            ')'.
            ' ORDER BY t.sort ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        /*
         * Check if user has already started a test and just returned to home page. If user started a test, create a
         * link to last unanswered question. If user has not answered the first question, the link will be to the first
         * question instead.
         */
        $continue = '';
        if ($user) {
            if ($user['progress']) {
                $last_question = array_pop($user['progress']);
                $question = DB::execute('SELECT sort FROM questions WHERE id=?',
                    [$last_question['question_id']]
                )->fetch();

                // Create link to last unanswered question.
                $continue = $this->getRouter()->getUrl('', 'questions', [$user['test_id'], $question['sort'] + 1]);
            }
            else {
                // Create a link to first question.
                $continue = $this->getRouter()->getUrl('', 'questions', [$user['test_id'], 1]);
            }
        }

        // Pass data to view - page title, tests dropdown, user data, link to contiue and user submitted values.
        return [
            'page_title' => __('Choose a test'),
            'tests' => $tests,
            'user' => $user,
            'continue' => $continue,
            'submit' => $this->getInputs()
        ];
    }

    /**
     * Page that supplies the test questions. Asks questions till there is no more to ask and processes user answers.
     *
     * @param string $test_id       Test ID.
     * @param string $question_idx  Index (sort number) of question.
     *
     * @return array                Return data to view.
     */
    public function questions(string $test_id, string $question_idx): array
    {
        $user = $this->getUserData();
        // If user is not created, redirect to home page with error.
        if (!$user) {
            $this->setError(__('Invalid user. Create user first and start the test.'));
            header('Location: '.$this->getRouter()->getUrl('', 'index', null));
            exit;
        }

        // Check if given test ID exists and does it match the user progress.
        $test = DB::execute('SELECT id,name FROM tests WHERE id=?', [$test_id])->fetch();

        // If test does not exist, redirect to home page with error.
        if (!$test) {
            $this->setError(__('Test does not exist'));
            header('Location: '.$this->getRouter()->getUrl('', 'index', null));
            exit;
        }

        // Check if the user is trying to access a different test before his current one is completed.
        if (bccomp((string) $user['test_id'], $test_id) != 0) {
            $this->setError(__('Please, finish this test before beginning a new one.'));
            header('Location: '.$this->getRouter()->getUrl('', 'errors', null, $this->getBackUrl()));
            exit;
        }

        // Get question text for current test.
        $question = DB::execute(
            'SELECT id,question,sort'.
            ' FROM questions'.
            ' WHERE test_id=? AND sort=?',
            [$test_id, $question_idx]
        )->fetch();

        /*
         * Redirect to error page if no questions exist for this test. This is done because we might not have a previous
         * page to return to. We might not have a referer (URL is manually entered) or something fatal in the system
         * occurred. Instead of a "404 not found" page, we are redirecting to custom error page from which user can
         * press "Back" to redirect to home. Otherwise in case of fatal error, we might stuck in redirect loops. This is
         * why a seprate page was created.
         */
        if (!$question) {
            $this->setError(__('Error! Question does not exist.'));
            header('Location: '.$this->getRouter()->getUrl('', 'errors', null, $this->getBackUrl()));
            exit;
        }

        // Get list of answers (radio buttons) for current question.
        $answers = DB::execute(
            'SELECT id,answer,is_correct'.
            ' FROM answers'.
            ' WHERE question_id=?'.
            ' ORDER BY sort ASC',
            [$question['id']]
        )->fetchAll(PDO::FETCH_ASSOC);

        // Redirect to error page if no answers exist for current question.
        if (!$answers) {
            $this->setError(__('Error! No answers found for this question.'));
            header('Location: '.$this->getRouter()->getUrl('', 'errors', null, $this->getBackUrl()));
            exit;
        }

        // Get total question count for this test.
        $question_count = DB::execute('SELECT count(id) FROM questions WHERE test_id=?', [$test_id])->fetchColumn();

        /*
         * Determine if there is a next question possible. If it is, create link to it. Once user answers the current
         * question, it will then redirect to the next question URL.
         */
        $next = '';
        if ($question_idx != $question_count) {
            $next_question = DB::execute(
                'SELECT id FROM questions WHERE test_id=? AND sort=?',
                [$test_id, $question_idx + 1]
            )->fetch();

            if ($next_question) {
                $next = $this->getRouter()->getUrl('', 'questions', [$test_id, $question_idx + 1]);
            }
        }

        // Process the user submitted data.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Create rules for validation and add custom error message if user did not select an answer.
            $rules = [
                'answer' => [
                    'rules' => [
                        'required' => __('Answer was not selected')
                    ]
                ],
                'next' => [
                    'rules' => [
                        'required' => ''
                    ]
                ]
            ];

            // Validat user inpu.
            if ($this->validateRequest($rules)) {
                // Check if submitted answer ID is valid for this question.
                $answer_id = $this->getInput('answer');
                $answers = arrayAddKey($answers, 'id');

                // If it is not valid user might have tampered with IDs on page, so redirect to errors page.
                if (!array_key_exists($answer_id, $answers)) {
                    $this->setError(__('Error! Answer does not exist for this question.'));
                    header('Location: '.$this->getRouter()->getUrl('', 'errors', null, $this->getBackUrl()));
                    exit;
                }

                /*
                 * Check if current question was already answered before and perhaps user changed his mind and submits
                 * a new answer.
                 */
                $update = false;
                if ($user['progress']) {
                    $user['progress'] = arrayAddKey($user['progress'], 'question_id');
                    if (array_key_exists($question['id'], $user['progress'])) {
                        // User has answered this question before.
                        $update = true;
                    }
                }

                if ($update) {
                    // Update the user answer. Does not check if the answer was the same as before. It just updates it.
                    DB::execute(
                        'UPDATE progress SET answer_id=?,is_correct=? WHERE user_id=? AND test_id=? AND question_id=?',
                        [$answer_id, $answers[$answer_id]['is_correct'], $user['id'], $test_id, $question['id']]
                    );
                }
                else {
                    // Question was not answered before so, insert it as a new answer.
                    DB::execute(
                        'INSERT INTO progress (user_id,test_id,question_id,answer_id,is_correct)'.
                        ' VALUES (?,?,?,?,?)',
                        [$user['id'], $test_id, $question['id'], $answer_id, $answers[$answer_id]['is_correct']]
                    );
                }

                /*
                 * update session time. Inactive users for longer that some time must be deleted from DB by some
                 * external service like a cron job.
                 */
                DB::execute(
                    'UPDATE users set updated_at=?'.
                    ' WHERE id=?',
                    [date('Y-m-d H:i:s'), $user['id']]
                );

                if ($next === '') {
                    /*
                     * If there are no more questions and this was the last one, get all user answers and store them
                     * in results. Then create a link results page.
                     */
                    $user = $this->getUserData();
                    $correct_answers = array_sum(array_column($user['progress'], 'is_correct'));

                    DB::execute(
                        'INSERT INTO results (user_id,name,test_id,correct)'.
                        ' VALUES (?,?,?,?)',
                        [$user['id'], $user['name'], $test_id, $correct_answers]
                    );

                    $url = $this->getRouter()->getUrl('', 'results', null);
                }
                else {
                    // Create a link to next question.
                    $url = $next;
                }

                // Redirect to results or next question.
                header('Location: '.$url);
                exit;
            }
        }

        // Check if this is the first question or is it possible to return to prevous question and change the answer.
        $prev = '';
        if ($question_idx == 1) {
            // Link to first question.
            $prev = $this->getRouter()->getUrl('', 'index', [$test_id]);
        }
        else {
            // Get the previous question and create a link to it.
            $prev_question = DB::execute(
                'SELECT id FROM questions WHERE test_id=? AND sort=?',
                [$test_id, $question_idx - 1]
            )->fetch();

            if ($prev_question) {
                $prev = $this->getRouter()->getUrl('', 'questions', [$test_id, $question_idx - 1]);
            }
        }

        // For checking if user has answered this question before and get his answer if it was.
        $answer_id = 0;

        // Completed test in percentage.
        $progress_bar_value = 0;

        if ($user['progress']) {
            $is_answered = false;

            foreach ($user['progress'] as $data) {
                /*
                 * No need to compare test ID. User can only have one active test at a time. So "progress" will contain
                 * the current only test ID anyway.
                 */

                if (bccomp((string) $data['question_id'], (string) $question['id']) == 0) {
                    // This question has been answered, so get the answer ID.
                    $answer_id = $data['answer_id'];
                    $is_answered = true;
                    break;
                }
                // or else the question is not answered.
            }

            /*
             * If this question was not answered, there are two possibilities:
             * 1) this is the next question in line;
             * 2) this is a question in the future. For example user answered 1,2, but in URL he enters 5, so he thinks
             * he can skip 3 and 4, but that is not allowed and that is what we are checking here.
             */
            if (!$is_answered) {
                if ($question_idx != 1) {
                    $question_allowed = false;

                    // This is not the first question, so a user progress must exist.
                    foreach ($user['progress'] as $data) {
                        if (bccomp((string) $data['question_id'], (string) $prev_question['id']) == 0) {
                            $question_allowed = true;
                            break;
                        }
                        
                    }

                    // In case user tried to skip a question, redirect to errors page with message.
                    if (!$question_allowed) {
                        $this->setError(__('Please finish answering the previous question first.'));
                        header('Location: '.$this->getRouter()->getUrl('', 'errors', null, $this->getBackUrl()));
                        exit;
                    }
                }
                // or else this is the first unanswered question, so there is no progress to look at.
            }

            // Check how many questions has the user answered and calculate percentage.
            $progress_bar_value = round(count($user['progress']) * 100 / $question_count);
        }

        /*
         * Pass data to view - page title, question text, previous link, user answer ID, radio button answer list
         * and percentage value for progress bar.
         */
        return [
            'page_title' => $test['name'],
            'question' => $question,
            'prev' => $prev,
            'answer_id' => $answer_id,
            'answers' => $answers,
            'progress_bar_value' => $progress_bar_value
        ];
    }

    /**
     * A page to display results.
     *
     * @return array    Return data to view.
     */
    public function results(): array
    {
        $user = $this->getUserData();

        // In case user tries to access results page without even entering his name, redirect to home page with error.
        if (!$user) {
            $this->setError(__('Invalid user. Create user first and start the test.'));
            header('Location: '.$this->getRouter()->getUrl('', 'index', null));
            exit;
        }

        // In case user tried to access results before finishing (or starting a test), redirect to home with error.
        if (!$user['results']) {
            $this->setError(__('You have not finished the test.'));
            header('Location: '.$this->getRouter()->getUrl('', 'errors', null, $this->getBackUrl()));
            exit;
        }

        // Get list of questions for current user test.
        $questions = DB::execute(
            'SELECT id,question'.
            ' FROM questions'.
            ' WHERE test_id=? ORDER BY sort ASC',
            [$user['test_id']]
        )->fetchAll(PDO::FETCH_ASSOC);

        $questions = arrayAddKey($questions, 'id');

        // Get list of answers for each of those questions.
        $answers = DB::execute(
            'SELECT id,question_id,answer,is_correct'.
            ' FROM answers'.
            ' WHERE question_id IN ('.(str_repeat('?,', count($questions) - 1) . '?').')'.
            ' ORDER BY question_id, sort ASC',
            array_keys($questions)
        )->fetchAll(PDO::FETCH_ASSOC);

        $answers = arrayAddKey($answers, 'id');

        $user['progress'] = arrayAddKey($user['progress'], 'question_id');

        // Clear the user session, so user can start again with new ID.
        DB::execute('DELETE FROM users WHERE id=?', [$user['id']]);

        // Create a link so user can go to home page again and take a different test.
        $home_url = $this->getRouter()->getUrl('', 'index', null);

        // Pass data to view - page title, questions, answers, user data with his answers and home page URL.
        return [
            'page_title' => __('Test completed!'),
            'answers' => $answers,
            'questions' => $questions,
            'user' => $user,
            'home_url' => $home_url
        ];
    }

    /**
     * A page to display errors and link to home page. This serves as a 404 or fatal error page.
     * 
     * @return array    Return data to view.
     */
    public function errors(): array
    {
        $back_url = '';

        // Get the referer "?ref=" we set previously. 
        [,,,,$query_string] = $this->getRouter()->getRoute();
        $pos = stripos($query_string, 'ref=');
        if ($pos !== false) {
            $back_url = substr($query_string, $pos + strlen('ref='));
        }

        // Pass data to view - page title and home page URL.
        return [
            'page_title' => __('An error occurred!'),
            'back_url' => $back_url
        ];
    }

    /**
     * Get user data by current session ID. Get ID, name, current test ID as well as progress and results if possible.
     *
     * @return array    User data.
     */
    private function getUserData(): array {
        $result = [];

        // Get basic information - checks if user exists. If exists, get user ID, name and test ID.
        $user = DB::execute('SELECT id,name,test_id FROM users WHERE sessionid=?', [Session::getId()])->fetch();

        if ($user) {
            $result = $user + [
                'results' => false,
                'progress' => false
            ];

            // Check if user has finished the test.
            $results = DB::execute('SELECT test_id,name,correct FROM results WHERE user_id=?', [$user['id']])->fetch();

            if ($results) {
                $result['results'] = $results;
            }

            // Check if user has answered at least one question.
            $progress = DB::execute(
                'SELECT test_id,question_id,answer_id,is_correct'.
                ' FROM progress'.
                ' WHERE user_id=?',
                [$user['id']]
            )->fetchAll(PDO::FETCH_ASSOC);

            if ($progress) {
                $result['progress'] = $progress;
            }
        }
        // or else there is no user session.

        return $result;
    }
}
