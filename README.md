# skirmish

In development.

Seed database with `php index.php seed-database`.

## Dependencies:

- firejail
- beanstalk
- See composer

## TODO

- Add more languages: Java, C, Python2
- Find some way to measure execution time and memory of submissions (may need runner overhaul)
- Add table sorting, filtering, pagination to problems.index, submissions.index, etc.
- Detect in submit if the uploaded file exceeds the size limit.
- Get around to contests
- Possible administrator panel?
- Handle solutions exceeding post limits and finish up form validation errors in submissions.show

## Notes:

- Changing file upload limit for `submissions.submit` can only be done in `php.ini`
