<?php


/** NEW SHORT AND EFFECTIVE PRINT METHOD
 * @param $string string that you want to out
 * @param bool $type if 2nd param in out is true it will not remove html attrs
 */
function out($string, $type=false)
{
    if ($type)
    {
        echo $string;
    }
    else
    {
        echo strip_tags($string);
    }
}


/** EXTRACT POST OR GET WITH AUTO SANITIZATION
 * @param array $array this is your typical array
 * @param bool $filter this is OPTIONAL and decide that array will sanitize or not
 * @return array this is the output
 */

function allout(array &$array, $filter = false)
{
    array_walk_recursive($array, function (&$value) use ($filter) {
        $value = trim($value);
        if ($filter) {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        }
    });

    return $array;
}

/** UPLOAD FILES
 * @param $path upload path
 * @param $name name of file
 */

function upload($path, $name)
{
    $file=$_FILES[$name]['name'];
    $expfile = explode('.',$file[0]);
    $fileexptype=$expfile[1];
    date_default_timezone_set( constant("zone"));
    $date = date('m/d/Yh:i:sa', time());
    $rand=rand(10000,99999);
    $encname=$date.$rand;
    $filename=md5($encname).'.'.$fileexptype;
    $filepath=$path.$filename;
    move_uploaded_file($_FILES[$name]["tmp_name"][0],$filepath);
    $paths = explode("/",$filepath);
    return $paths[count($paths)-1];
}

/** GET SESSION DATA
 * @param $key is sessions key
 * @return mixed the value session have
 */
function gets($key)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION[$key];
}

/** SET DATA IN SESSION
 * @param $key is session's key
 * @param $value is value you want to set in session
 * @return bool always return true
 */
function sets($key, $value)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION[$key] = $value;

    return true;
}

/** CHECK IF KEY EXIST IN SESSION OR NOT
 * @param $key is session's key
 * @return bool define value exist in session or not
 */
function checks($key)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION[$key]))
    {
        return true;
    }
    else
    {
        return false;
    }
}

/** REDIRECT PAGE
 * @param $destination path for new location
 */
function redirect($destination)
{
    header("location:".$destination);
}