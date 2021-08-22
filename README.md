# Homework assigment for Vendon
The purpose of this project is to demonstrate the basic knowledge of PHP.

#
## The assignment
Create a basic test system, where user can enter his name, choses a test, answers the questions and gets results.
Candidate is required to use PHP, MySQL/MariaDB, HTML, CSS, JavaScript. PHP must be object-oriented. Design solution
is free of choice. Code has to be commented. No frameworks are allowed. Can use 3rd party libraries and Composer if
so desired. There can be many tests in database and unlimited number of questions and answers. Every time user answers
a question, this needs to be saved into database. The final result also has to be saved into database.

Test consists of 3 pages:
1. Home page where user can enter his name and choose a test that exists in database. If user has not entered a name
or has not chosen a test, user cannot proceed to next page. There must be an error message.
2. Test question page where each question can have unlimited number of answers but only one is correct. User chooses
of of possible answers and proceeds to next question. If no answer is selected, user cannot proceed to next question.
Important to note that each question can have different amount of answers. There should also be a progress bar to see
how much questions has the user answered.
3. Results page where user can see his results. This page displays the user name and how many questions where there, how
many of them the user answered correctly.

#
## Prequisites
- Install a decent `PHP` version like at least `7.3`
- Have a `MySQL` database at least `5.6`
- Enable `MySQL PDO extension` in PHP
- Make sure you have `mod rewrite` set up to access the `.htaccess` in the application root folder not some default one.
- You can put it anywhere in your web directory and access it like for example `http://localhost/your_folder/`
- Create a database and import the `Dump20210822.sql` file located in `database` folder.
- Check the `/includes/config.inc.php` and make sure you have set up the database user name and password correctly.

#
## How to use
- Once prequisites are fulfilled, using youw web browser navigate to the application folder. For the purposes of the
example I will be using `http://localhost/vendon/` as root folder.
- If there are no errors, you should see the first page where you can enter the user name and choose a test. If you
don't enter a user name or don't choose a test, you will receive and error. If you try to navigate somewhere else, most
likely you will receive a fatal error saying that controller is not found etc. This is because no other pages are
implemented and a 404 is also not implemented. So in order to see results, stick to this scenario - write user name and
choose a test.
- There are two tests to choose from. It's purely an coincidence that the number of questions for the two possible tests
is the same. Choose a test and press `Start`.
- Once chosen a test, you will be navigated to `http://localhost/vendon/tests/questions/{test_id}/1` where `{test_id}`
is the ID of the chosen test and `1` is the first question. Questions use `sort` and not IDs in URLs.
- In question page choose one of possible answers using radio buttons and press `Next` to proceed to next question. If
you don't choose an answer, you will see and error message. While you have not completed the test, you can go back to
previous pages using the `Back` link. Even till the very beginning. You can also choose a different name or start a
different test. But you will lose all your progress for this one.
- If you have pressed `Back` till you are in home page again, instead if `Start` you will see two buttons `Restart`
and `Continue`. Restarting the test will cause you to lose the progress. You can choose a different name and test while
doing so. If you press `Continue`, you will be navigated to last unanswered question.
- At the bottom, you can see your progress.
- While still not having finished the test, you can also change your answers if you changed your mind.
- If you think you can skip a question by entering unanswered question number in URL, think again. You can't. You will
be navigated to error page `http://localhost/vendon/errors/?ref=http://localhost/vendon/tests/` You can press
`back` to go back to first page.
- If you enter a question number that doesn't exist. You again will be redirected to error page.
- If you enter a `test_id` in URL that does not exist, or test ID that has no questions or existing test ID and existing
question but which has no answers, you will be again redirected to error page. Or if you have started a test and
suddenly enter a test ID that existsm you will be again redirected to error page with appropriate message.
- When you have finished a test, you will be redirected to `http://localhost/vendon/tests/results/` results page. You
again will see your name and how many questions you answered correctly. The questions you have answered will be
displayed in the list. Correct answers will be marked in green text with check mark. Incorrect answers will be marked
with cross and red text. A correct answere will be written below it.
- At the very bottom of the page, you can press a link `Home` to start a new test.

#
## How this all works behind the curtains
To be honest this is more like a parody than actual framework. It needs too much work. The idea was to make some basic
classes like databass wrapper, tests page (controller) and something to process the URLs. So ended up with making a mini
framework. It's most like out of scope and the whole task could've been much simplier.
- This project uses mod rewrite in order to avoid ugly URLs.
- The process starts with accessing `index.php`. Always this one file. It then launches the `App` instance. If success,
there are no error messages. If there are, well. Too bad. Either the project has some wrong settings, folders have no
read access, database has not be set up or maybe some version is not compatible or some other thing. I couldn't test on
other systems, but it should work.
- The `App` autoloads all core classes and controller classes. As well as includes. Then checks the database
connection and tables. Once initial process is complete. PHP session is started and then the routing begins.
- Router checks the URL and determines which controller, function and what parameters should be lauched. As well as
view file. There are no layouts. There is only one HTML layout. If there is no complex URL to parse, the default
controller, function and view will be executed.
- Router reads the controllers folder and loads all the controllers into a list. It also parses the file, so it knows
what public functions are available to call and what parameters the function can have. Though the `FileParser` is
incomplete and buggy. See list of known issues below.
- The router is also capable if validating the conroller and funtions - check if they exist and if not throw and error.
Sadly there is no 404 page, so in case of incorrect URL, you will most likely see that controller is not found.
- Once routed has set the controller, funtion and variables, we then finish up by showing the view. The view folder has
to match controller name. For example `TestsController`, the view folder is Tests and function name must match the file
name. For example `/tests/questions/1/1` view must have `questions.php` view file in `Tests` folder. Controller collects
all the data and then passes it to view via `return`. View then accesses `$this->data` array.
- The view includes header at the top. Shows content in the middle and adds footer at the bottom.
- It short that's it. The rest can be dived in the code and comments.

#
## Known issues and out of scopes
In no specific order here is a list of issues, bugs, complaints etc.
- For a framework there are no models. Just controllers and views which are not really views, but pages with PHP + HTML.
- `FileParser` doesn't support `$var = array()` syntax. It thinks the last `)` is closing of a function, but it's not.
But since we are in 2021 and that syntax is no longer used, it works.
- `FileParser` doesn't differenciate integer, string, array variables and doesn't check if they are mandatory or not. So
if invalid arguments are given, an ugly fatal error will occur.
- `FileParser` is primitive and doesn't work with namespaces, traits etc.
 - Errors that are occurring due to no arguments passed in URL like `http://localhost/tests/questions` are not being
 correctly processed. Because we're not checking if variables are required or not. We assume not.
- The main controller field validator cannot validate array sub elements. In can only detect if it's an array or not.
But if the array elements them selves exist, are string, intergers etc, it cannot be validated at this time.
- Not possible to display error messages for each field. Although it can be implemented later. Like paint input field
borders in red color, for example.
- Router only accepts the format `http://localhost/tests/questions/{testid}/{questionindex}/` which is so 2005. it would
be better if the URL would look like `http://localhost/tests/{testid}/questions/{questionindex}/` but that's a lot of
extra work and a different URL parser is needed.
- Routes cannot be added to a list and used both singular and plural forms, or add a custom route and point it to a
controller of choice. For now only specific URLs are accepted that point to controllers.
- For some reason URLs like `http://localhost/tests?a=""` work, but `http://localhost/tests&a=""` completely break and
show `Access Forbidden`, however `http://localhost/tests&a=1` work just fine. Not sure what the issue is. Might be
incorrect `.htaccess` file and more rules are required.
- Most classes are `final`, so they are not meant to be extended. Parsers don't have a common class, because the operate
differently. They could be unified under one `Parser` class and a different one could be used when necessary.
- UI is not translatable. The `__()` functions do nothing but `vsprintf`. No actual translations exist, though it's good
to have the functions already in place.
- Views are named after the controller and function. `TestsController->questions()` will use
`app/views/Tests/questions.php` file. It's very primitive, so it could be improved.
- No `CSRF` tokens. Possible to submit unauthorized data. Like from outside the project send a `POST` data.
- Need to be careful with lots of forward slashes in URL. The system is still far from fool proof.
- Pages are fully refreshed and there is no AJAX. Though it's not neededm it could be nice to validate user input via
AJAX and then show error in page directly. But for now, like I mentioned before there is only one HTML layout. Could
add JSON layout in the future.
- No cookies are stored. Which could be a good thing, but maybe instead of `Session` we could use a cookie variable that
dies on it's own after some time.
- Originally the idea was to add images to questions and answers. Even make a test with only images or only answers.
But since this has take too much time and is out of scope, the code was scraped.
