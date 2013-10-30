<?php

class ComposerMergeTask extends Task
{
    /** @var  PhingFile */
    protected $toFile;

    /** @var  PhingFile */
    protected $fromFile;

    public function setToFile(PhingFile $toFile)
    {
        $this->toFile = $toFile;
    }

    public function setFromFile(PhingFile $fromFile)
    {
        $this->fromFile = $fromFile;
    }

    public function main()
    {
        if(!$this->toFile->exists() || !$this->toFile->canWrite()) {
            throw new BuildException('Cannot write to '.$this->toFile->getAbsolutePath());
        }

        if(!$this->fromFile->exists() || !$this->fromFile->canRead()) {
            throw new BuildException('Cannot read '.$this->fromFile->getAbsolutePath());
        }

        $fromData = json_decode(file_get_contents($this->fromFile->getAbsolutePath()), true);

        if(!isset($fromData['require']) || !is_array($fromData['require'])) {
            $this->log('No requirements to merge');
            return;
        }

        $toData = json_decode(file_get_contents($this->toFile->getAbsolutePath()), true);

        $changes = false;
        foreach($fromData['require'] as $package => $version)
        {
            if(isset($toData['require'][$package]))
            {
                $currentVersion = $toData['require'][$package];
                $pattern = str_replace(array('.','*'),array('\\.', '[\\d*]+'), $version);
                if(!preg_match('/'.$pattern.'.*/', $currentVersion)) {
                    throw new BuildException('Version conflict for package '.$package);
                }
            } else {
                $this->log('Adding required package '.$package);
                $toData['require'][$package] = $fromData['require'][$package];
                $changes = true;
            }
        }

        if(!$changes) {
            $this->log('Composer Dependencies up to date.');
            return;
        }

        $options = null;
        if(defined('JSON_UNESCAPED_SLASHES') && defined('JSON_PRETTY_PRINT')) {
            $options = JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT;
        }

        $json = json_encode($toData, $options);

        //make up for lack of prettiness in json_encode in php < 5.4
        if(is_null($options)) {
            $json = str_replace(array('{','}',',','\\/'),
                array("{\n","\n}", ",\n",'/'), $json);
        }

        file_put_contents($this->toFile->getAbsolutePath(), $json);
    }
}