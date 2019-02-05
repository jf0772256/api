<?php
namespace JesseFender\AutoLoader;
/**
 * To autoload classes and namespaces for the use through out
 * using code from :
 * https://github.com/Wilkins/composer-file-loader
 * modified to allow for our needed usage.
 */
class Loader
{
    public $dir;
    public function getAutoloadFile($fn)
    {
        return json_decode(file_get_contents($this->dir."/".$fn), 1);
    }
    public function getLocalAutoloadFile($fn){
        return json_decode(file_get_contents(__DIR__.'/'.$fn), 1);
    }
    public function load($dir,$fn)
    {
        $this->dir = $dir;
        if ($this->dir!='') {
            $composer = $this->getAutoloadFile($fn);
        } else {
            $this->dir = \dirname(__DIR__);
            $composer = $this->getLocalAutoloadFile($fn);
        }
        if(isset($composer["autoload"]["psr-4"])){
            $this->loadPSR4($composer['autoload']['psr-4']);
        }
        if(isset($composer["autoload"]["psr-0"])){
            $this->loadPSR0($composer['autoload']['psr-0']);
        }
        if(isset($composer["autoload"]["files"])){
            $this->loadFiles($composer["autoload"]["files"]);
        }
    }

    public function loadFiles($files){
        foreach($files as $file){
            $fullpath = $this->dir."/".$file;
            if(file_exists($fullpath)){
                include_once($fullpath);
            }else{
                echo "File not found: ". $fullpath."<br/>";
            }
        }
    }
    public function loadPSR4($namespaces)
    {
        $this->loadPSR($namespaces, true);
    }
    public function loadPSR0($namespaces)
    {
        $this->loadPSR($namespaces, false);
    }
    public function loadPSR($namespaces, $psr4)
    {
        $dir = $this->dir;
        // Foreach namespace specified in the composer, load the given classes
        foreach ($namespaces as $namespace => $classpaths) {
            if (!is_array($classpaths)) {
                $classpaths = array($classpaths);
            }
            spl_autoload_register(function ($classname) use ($namespace, $classpaths, $dir, $psr4) {
                // Check if the namespace matches the class we are looking for
                if (preg_match("#^".preg_quote($namespace)."#", $classname)) {
                    // Remove the namespace from the file path since it's psr4
                    if ($psr4) {
                        $classname = str_replace($namespace, "", $classname);
                    }
                    $filename = preg_replace("#\\\\#", "/", $classname).".php";
                    foreach ($classpaths as $classpath) {
                        $fullpath = $this->dir."/".$classpath."/$filename";
                        if (file_exists($fullpath)) {
                            include_once $fullpath;
                        }
                    }
                }
            });
        }
    }
}