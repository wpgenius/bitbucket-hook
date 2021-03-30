<?php
$repo_dir = '/home/fathom/repository/mambo_multitenant.git';
$web_root_dir = '/var/www/html';

// Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
$git_bin_path = 'git';

$update = false;

//$data = file_get_contents('php://input');
//file_put_contents('deploy.log', $data, FILE_APPEND);

// Parse data from Bitbucket hook payload
$payload = json_decode(file_get_contents('php://input'), true); //json_decode($_POST['payload']);

/*
if (empty($payload->commits)){
  // When merging and pushing to bitbucket, the commits array will be empty.
  // In this case there is no way to know what branch was pushed to, so we will do an update.
  $update = true;
} else {
  foreach ($payload->commits as $commit) {
    $branch = $commit->branch;
    if ($branch === 'production' || isset($commit->branches) && in_array('production', $commit->branches)) {
      $update =	true;
      break;
    }
  }
*/

  foreach ($payload as $plkey => $plval)
    { if ($plkey === "push")
      { 
        foreach ($plval['changes'] as $change)
          { foreach ($change['commits'] as $cmt)
            { 
              $branch = $change['new']['name']; 
              //$message = $cmt['message']; $sha = $cmt['hash']; $username = $cmt['author']['user']['username']; $uuid = $cmt['author']['user']['uuid'];
              if ($branch === 'production') {
                $update = true;
                break;
              }
            }
          }
      }
  }

if ($update) {
  // Do a git checkout to the web root
  exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' fetch');
  exec('cd ' . $repo_dir . ' && GIT_WORK_TREE=' . $web_root_dir . ' ' . $git_bin_path  . ' checkout -f');

  // Log the deployment
  $commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path  . ' rev-parse --short HEAD');
  file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Deployed branch: " .  $branch . " Commit: " . $commit_hash . "\n", FILE_APPEND);
}


//exec('bin/cd /var/www/html/deploy' && 'bin/cp deploy.log deploy1.log');
// $output = shell_exec('cp deploy.log deploy1.log');
// echo "<pre>$output</pre>";

// file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Deployed branch: " .  "vint-test" . " Commit: " . "vid-test" . "\n", FILE_APPEND);

//echo "success";

//file_put_contents('deploy.log', serialize($_POST['payload']), FILE_APPEND);
