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

set('param', 'value');

task('deploy', function () {
    $param = get('param');
});

// Hosts

host('project.com')
    ->set('deploy_path', '~/{{application}}');    
    
// Tasks

task('build', function () {
   run('cd {{release_path}} && build');
});


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

