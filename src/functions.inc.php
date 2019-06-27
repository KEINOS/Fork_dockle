<?php

function addStringRedirect($command)
{
    if (! IS_MODE_DEBUG) {
        return $command;
    }
    return trim($command) . ' 2>&1';
}

function echoArrayAsJsonAndExit(array $array, $is_pretty=true)
{
    if (! headers_sent()) {
        header('Content-Type: application/json');
    }
    $options = ($is_pretty) ? JSON_PRETTY_PRINT : 0;
    echo json_encode($array, $options);
    exit(SUCCESS);
}

function echoErrorAndExit($message, $code_responce=404)
{
    http_response_code($code_responce);
    echoMessage($message);
    exit(FAILURE);
}

function echoMessage($message)
{
    if (! headers_sent()) {
        header("Content-Type: text/plain");
    }
    echo $message, PHP_EOL;
}

function echoMessageAndExit($message)
{
    echoMessage($message);
    exit(SUCCESS);
}

function echoUsageAndExit()
{
    $path_file_usage = dirname(__FILE__) . DIR_SEP . NAME_FILE_USAGE;
    $usage = file_get_contents($path_file_usage);
    $host  = "${_SERVER['REQUEST_SCHEME']}://${_SERVER['HTTP_HOST']}";
    $usage = str_replace('%HOST%', $host, $usage);

    echoMessageAndExit($usage);
}

function getBadgeAsArray(array $summary)
{
    $badge_format = [
        'schemaVersion' => 1,
        'cacheSeconds'  => 300,
        'label'   => 'Dockle',
        'message' => '',
        'color'   => '',
    ];

    if (0 < $summary['pass']) {
        $color   = 'green';
        $string .= "PASS ${summary['pass']} ";
    }
    if (! empty(getIgnoreDockle())) {
        $color   = 'yellow';
    }
    if (0 < $summary['warn']) {
        $color = 'yellow';
        $string .= "/ WARN ${summary['warn']} ";
    }
    if (0 < $summary['info']) {
        $color = 'yellow';
        $string .= "/ INFO ${summary['info']} ";
    }
    if (0 < $summary['fatal']) {
        $color = 'red';
        $string .= "/ FATAL ${summary['fatal']} ";
    }
    if (empty($string)) {
        $color  = 'red';
        $string = 'Image Not found.';
        $badge_format['isError']='true';
    }

    $badge_format['message'] = trim($string);
    $badge_format['color']   = $color;

    return $badge_format;
}

function getCacheBadge()
{
    $id = getIdRequest();
    return loadData($id);
}

function getCacheDetails()
{
    $id = getIdRequest() . '.details';
    return loadData($id);
}

function getCommandDockle($name_image)
{
    $path_bin_dockle = '/usr/local/bin/dockle';
    $path_dir_cache  = '/app/cache';
    $opt_format      = '--format json';
    $opt_exitcode    = '--exit-code 0';
    $opt_cache       = "--cache-dir ${path_dir_cache}";
    $opt_ignore      = getIgnoreDockle();
    $opt = trim("${opt_format} ${opt_exitcode} ${opt_cache} ${opt_debug} ${opt_ignore}");

    return "${path_bin_dockle} ${opt} ${name_image}";
}

function getHeader($url)
{
    $handle  = curl_init();
    $headers = [];
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);

    // this function is called by curl for each header received
    curl_setopt(
        $handle,
        CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$headers) {
            $len    = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $name = strtolower(trim($header[0]));
            if (!array_key_exists($name, $headers)) {
                $headers[$name] = [trim($header[1])];
            } else {
                $headers[$name][] = trim($header[1]);
            }
            return $len;
        }
    );
    $data = curl_exec($handle);

    return $headers;
}

function getIdRequest()
{
    static $id;
    if (isset($id)) {
        return $id;
    }

    $id = hash('md5', getNameImage());
    if ('/' === $_SERVER['REQUEST_URI']) {
        $id = false;
    }
    return $id;
}

function getIgnoreDockle()
{
    $list_ignore = getQueryAsArray()['ignore'] ?: [];
    $result = '';
    foreach ($list_ignore as $ignore) {
        $result .= empty($ignore) ? '' : "--ignore ${ignore} ";
    }
    return trim($result);
}

function getNameImage()
{
    static $name_image;

    if (isset($name_image)) {
        return $name_image;
    }

    if (! file_exists(PATH_DIR_DATA)) {
        if (! mkdir(PATH_DIR_DATA)) {
            echoErrorAndExit('Fail to create Cache directory.');
        }
    }
    $name_image = parse_url($_SERVER['REQUEST_URI'])['path'];
    $name_image = trim($name_image, '/');
    return $name_image;
}

function getPathFileData($id)
{
    return PATH_DIR_DATA . DIR_SEP . $id;
}

function getQueryAsArray()
{
    static $result;
    if (isset($result)) {
        return $result;
    }
    if (empty($_SERVER[QUERY_STRING])) {
        $result = [];
    } else {
        parse_str($_SERVER[QUERY_STRING], $result);
    }
    return $result;
}

function getUrlRedirectCached()
{
    $name_image = getNameImage();
    $url_base   = "${_SERVER['REQUEST_SCHEME']}://${_SERVER['HTTP_HOST']}/${name_image}";
    return $url_base;
}

function isRequestDetails()
{
    $query = getQueryAsArray();
    return isset($query['details']);
}

function isRequestEmpty()
{
    return empty(getNameImage());
}

function isRequestUpdate()
{
    $query = getQueryAsArray();
    return isset($query['update']);
}

function isValidImageName($name_image)
{
    if ('--' === substr($name_image, 0, 2)) {
        return false;
    }
    return preg_match('/^[a-zA-Z0-9_:\.\-\/]+$/u', $name_image);
}

function loadData($id)
{
    $path_file_data = getPathFileData($id);
    if (! file_exists($path_file_data)) {
        return false;
    }
    $string = file_get_contents($path_file_data);
    return unserialize($string);
}

function putCacheBadge($data)
{
    $id = getIdRequest();
    return saveData($id, $data);
}

function putCacheDetails($data)
{
    $id = getIdRequest() . '.details';
    return saveData($id, $data);
}

function redirectToUrlAndExit($url)
{
    http_response_code(301);
    header("Location: ${url}");
    exit;
}

function runDockle($name_image)
{
    static $result;

    $command = getCommandDockle($name_image);
    $result  = runExec($command);

    if (false === $result) {
        echoErrorAndExit('Fail while running Dockle.', 500);
    }
    return $result;
}

function runExec($command)
{
    $command  = addStringRedirect($command);
    $lastline = exec($command, $output, $return_var);

    if (SUCCESS === $return_var) {
        return implode(PHP_EOL, $output);
    }
    $msg_return = trimColorSequesnce(implode(PHP_EOL, $output));
    echoMessage('RETURN MESSAGE.' . PHP_EOL . $msg_return);
    return false;
}

function saveData($id, $data)
{
    $path_file_data = getPathFileData($id);
    $string = serialize($data);
    return file_put_contents($path_file_data, $string);
}

function trimColorSequesnce($string)
{
    $pattern = '\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[m|K]';
    return preg_replace('/'.$pattern.'/', '', $string);
}
