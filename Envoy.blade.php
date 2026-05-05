
@setup
$server = 'srv961648.hstgr.cloud';

$baseDir = '/var/www/html/unisystems';
$user = 'cristi';

$php = 'php';
$userAndServer = $user . '@' . $server;
$repository = 'cripantea/cv-manager-eu.git';
$npm = 'npm';

$branch = $branch ?? 'main';

# naming convention
$releasesDir  = "{$baseDir}/releases";
$persistentDir = "{$baseDir}/persistent";
$currentDir   = "{$baseDir}/current";
$newReleaseName = date('Ymd-His');
$newReleaseDir = "{$releasesDir}/{$newReleaseName}";

function logMessage($message) {
    return "echo '\033[32m" . $message . "\033[0m';\n";
}
@endsetup

@servers(['local' => '127.0.0.1', 'remote' => $userAndServer])

@story('deploy')
    cloneRepository
    runComposer
    runNpm
    generateAssets
    updateSymlinks
    optimizeInstallation
    migrateDatabase
    blessNewRelease
    cleanOldReleases
    finishDeploy
@endstory

@task('cloneRepository', ['on' => 'remote'])
    {{ logMessage('🌀  Cloning repository…') }}
    [ -d {{ $releasesDir }} ]   || mkdir -p {{ $releasesDir }};
    [ -d {{ $persistentDir }} ] || mkdir -p {{ $persistentDir }};
    [ -d {{ $persistentDir }}/storage ]                    || mkdir -p {{ $persistentDir }}/storage;
    [ -d {{ $persistentDir }}/storage/app ]                || mkdir -p {{ $persistentDir }}/storage/app;
    [ -d {{ $persistentDir }}/storage/app/public ]         || mkdir -p {{ $persistentDir }}/storage/app/public;
    [ -d {{ $persistentDir }}/storage/framework ]          || mkdir -p {{ $persistentDir }}/storage/framework;
    [ -d {{ $persistentDir }}/storage/framework/cache ]    || mkdir -p {{ $persistentDir }}/storage/framework/cache;
    [ -d {{ $persistentDir }}/storage/framework/sessions ] || mkdir -p {{ $persistentDir }}/storage/framework/sessions;
    [ -d {{ $persistentDir }}/storage/framework/views ]    || mkdir -p {{ $persistentDir }}/storage/framework/views;
    [ -d {{ $persistentDir }}/storage/logs ]               || mkdir -p {{ $persistentDir }}/storage/logs;

    cd {{ $releasesDir }};
    mkdir {{ $newReleaseDir }};

    git clone --depth 1 --branch {{ $branch }} git@github.com:{{ $repository }} {{ $newReleaseName }};

    cd {{ $newReleaseDir }};
    git config core.sparsecheckout true;
    echo "*"           > .git/info/sparse-checkout;
    echo "!storage"   >> .git/info/sparse-checkout;
    echo "!public/build" >> .git/info/sparse-checkout;
    git read-tree -mu HEAD;

    echo "{{ $newReleaseName }}" > public/release-name.txt;
@endtask

@task('runComposer', ['on' => 'remote'])
    {{ logMessage('🚚  Running Composer…') }}
    cd {{ $newReleaseDir }};
    composer install --prefer-dist --no-scripts --no-dev -q -o;
@endtask

@task('runNpm', ['on' => 'remote'])
    {{ logMessage('📦  Running npm…') }}
    cd {{ $newReleaseDir }};
    npm install;
@endtask

@task('generateAssets', ['on' => 'remote'])
    {{ logMessage('🌅  Generating assets…') }}
    cd {{ $newReleaseDir }};
    npm run build;
@endtask

@task('updateSymlinks', ['on' => 'remote'])
    {{ logMessage('🔗  Updating symlinks to persistent data…') }}
    rm -rf {{ $newReleaseDir }}/storage;
    ln -nfs {{ $persistentDir }}/storage {{ $newReleaseDir }}/storage;
    ln -nfs {{ $baseDir }}/.env {{ $newReleaseDir }}/.env;
@endtask

@task('optimizeInstallation', ['on' => 'remote'])
    {{ logMessage('✨  Optimizing installation…') }}
    cd {{ $newReleaseDir }};
    {{ $php }} artisan clear-compiled;
@endtask

@task('migrateDatabase', ['on' => 'remote'])
    {{ logMessage('🙈  Migrating database…') }}
    cd {{ $newReleaseDir }};
    {{ $php }} artisan migrate --force;
@endtask

@task('blessNewRelease', ['on' => 'remote'])
    {{ logMessage('🙏  Blessing new release…') }}
    ln -nfs {{ $newReleaseDir }} {{ $currentDir }};
    cd {{ $newReleaseDir }};
    {{ $php }} artisan config:clear;
    {{ $php }} artisan view:clear;
    {{ $php }} artisan schedule:clear-cache;
    {{ $php }} artisan queue:restart;
    {{ $php }} artisan config:cache;
    {{ $php }} artisan storage:link;
@endtask

@task('cleanOldReleases', ['on' => 'remote'])
    {{ logMessage('🚾  Cleaning up old releases…') }}
    cd {{ $releasesDir }};
    ls -dt {{ $releasesDir }}/* | tail -n +6 | xargs -d "\n" rm -rf;
@endtask

@task('finishDeploy', ['on' => 'local'])
    {{ logMessage('🚀  Application deployed!') }}
@endtask

@task('deployOnlyCode', ['on' => 'remote'])
    {{ logMessage('💻  Deploying code changes only…') }}
    cd {{ $currentDir }};
    git pull origin {{ $branch }};
    {{ $php }} artisan config:clear;
    {{ $php }} artisan view:clear;
    {{ $php }} artisan schedule:clear-cache;
    {{ $php }} artisan queue:restart;
    {{ $php }} artisan config:cache;
@endtask
