<?php
/**
 * An override for _env() that throws error on not found.
 *
 * We are using a constant string for our default and for env()
 * default so that we can use 'null' as a possible default value
 * and don't throw an error if there is an entry like
 * VAR=null
 *
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function _env($name, $default = null)
{
    $uniqueDefault = new StdClass;
    $value = env($name, $uniqueDefault);
    $envMissingValue = $value === $uniqueDefault;
    $hasDefaultArg = func_num_args() > 1;

    if ($envMissingValue) {
        if (!$hasDefaultArg) {
            throw new Exception("Environment variable '{$name}' not found.");
        }

        return $default;
    }

    return $value;
}

function set_active($segment)
{
    return Request::segment(1) == $segment ? 'active' : '';
}

function set_active_nav($url)
{
    return Request::is($url) ? 'nav__item--current' : '';
}

function linkOption($url)
{
    $current_url = str_replace(URL::to('/'), "", Request::url());

    if ($url === $current_url) {
        return "value=\"{$url}\" selected=\"selected\"";
    } else {
        return "value=\"{$url}\"";
    }
}

/**
 * @param string $url
 * @param string $imgixParams
 * @param array $options
 * @return string
 */
function imgixUrl($url, $imgixParams = null, array $options = [])
{
    if (!Config::get('imgix.use_imgix')) {
        return $url;
    }

    if (empty($url)) {
        return null;
    }

    $url = \MotionArray\Helpers\Imgix::getImgixUrl($url);

    if ($imgixParams) {
        $url = explode('?', $url)[0];
        $url .= $imgixParams;
    }

    return $url;
}

/**
 * Check if the current Host is a MotionArray.com
 * domain. Otherwise it's post.pro, portfolios, etc.
 *
 * @return boolean
 */
function isMotionArrayDomain()
{
    $maDomains = [
        'motionarray.com',
        'www.motionarray.com',
        'post.pro',
    ];

    if (!isset($_SERVER['HTTP_HOST']))
        return true;

    $domain = strtolower($_SERVER['HTTP_HOST']);
    // If matching APP_HOST, we're good.
    if ($domain == config('app.host'))
        return true;

    return in_array($domain, $maDomains);
}

function syncEvent($className, $args)
{
    $queueManager = \App::make('Illuminate\Queue\QueueManager');

    $defaultDriver = $queueManager->getDefaultDriver();

    $queueManager->setDefaultDriver('sync');

    $event = \event($className, $args);

    $queueManager->setDefaultDriver($defaultDriver);

    return $event;
}

function formatAndRound($amount, $decimals = 2)
{
    if (is_null($amount)) {
        return $amount;
    }

    $pow = pow(10, $decimals);

    $amount = floor($amount * $pow) / $pow;

    return number_format($amount, $decimals);
}

function formatMoney(float $amount = null, $prefix = '$')
{
    if ($amount === null) {
        $amount = 0;
    }
    $amount = round($amount, 2, PHP_ROUND_HALF_DOWN);
    return $prefix . number_format($amount, 2);
}


function profileAndLog($name, Closure $callback)
{
    $then = microtime(true);
    $result = $callback();
    $now = microtime(true);
    $diff = sprintf("%.6fms", ($now - $then) * 1000);
    \Log::info($name . ', elapsed: ' . $diff);

    return $result;
}
