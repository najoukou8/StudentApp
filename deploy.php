<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'StudentApp');



// Project repository
set('repository', 'https://github.com/najoukou8/StudentApp.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', ['.env']);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('testHostdepd')
    ->set('deploy_path', '~/{{application}}');
    ->forwardAgent()
    ->port(25560);
     
// Tasks
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:symlink',
]);

task('build', function () {
   run('cd {{release_path}}');
   run('docker-composer build');
   run('docker-compose up');
});

task('upload', function () {
    upload(__DIR__ . "/", '{{release_path}}');
});

task('release', [
    'deploy:prepare',
    'deploy:release',
    'upload',
    'deploy:shared',
    'deploy:writable',
    'deploy:symlink',
]);

task('deploy', [
    'build',
    'release',
    'cleanup',
    'success'
]);


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

