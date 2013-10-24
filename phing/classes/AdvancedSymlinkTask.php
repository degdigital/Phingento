<?php
/**
 * Recursive symlink task
 */
class AdvancedSymlinkTask extends SymlinkTask
{
    protected $taskType = 'advanced-symlink';
    protected $_createParent = true;
    protected $_relative = true;
    protected $_silentOnExisting = true;

    protected function symlink($target, $link)
    {
        $originalTarget = $target;
        if ($this->_createParent) {
            $linkParent = dirname($link);
            if (!is_dir($linkParent) && !is_file($linkParent)) {
                mkdir($linkParent, 0775, true);
            }
        }

        if ($this->_relative) {
            $target = $this->resolveToRelative($link, $target);
        }

        if ($this->_silentOnExisting) {
            if(file_exists($link)) {
                if(is_link($link) && is_dir($originalTarget)) {
                    //resolve symlinks within a symlinked dir correctly
                    $originalDir = getcwd();
                    chdir($originalTarget);
                    foreach(glob('*') as $file)
                    {
                        $childTarget = $originalTarget . DIRECTORY_SEPARATOR . $file;
                        $childLink = realpath($link) . DIRECTORY_SEPARATOR . $file;
                        unlink($childLink);
                        $this->symlink($childTarget, $childLink);
                    }
                    chdir($originalDir);
                    return false;
                } else if (is_file($link) && realpath($link) == $link){
                    unlink($link); //replace files with symlinks
                } else {
                    return false; //don't try to overwrite an existing symlink
                }
            }
        }

        return parent::symlink($target, $link);
    }

    protected function resolveToRelative($source, $target)
    {
        $common = dirname($source);
        $back = 0;
        while (substr($target, 0, strlen($common)) !== $common) {
            $common = dirname($common);
            $back++;
            if($back > 100) {
                throw new Exception("Common parent not close enough ({$source}) ({$target})");
            }
        }

        return str_repeat('../', $back) . substr($target, strlen($common . '/'));
    }
}