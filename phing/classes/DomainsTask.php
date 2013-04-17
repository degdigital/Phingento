<?php
/**
 * Get a space delimited list of domains for the host file
 */
class DomainsTask extends Task
{
    protected $property;
    protected $codes = '';

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function setCodes($codes)
    {
        $this->codes = $codes;
    }

    public function main()
    {
        $domains = array();
        $domains[] = $this->getDomain();

        if(!empty($this->codes))
        {
            $codes = explode(',', $this->codes);

            foreach($codes as $code) {
                $domains[] = $this->getDomain($code);
            }
        }

        $value = implode(' ', $domains);
        $this->project->setProperty($this->property, $value);
    }

    protected function getDomain($code = null)
    {
        $host = $this->project->getProperty('env.HOSTNAME');
        $project = $this->project->getProperty('project.name');
        $project = str_replace('_', '', $project);

        if(empty($code)) {
            return "{$project}.{$host}";
        } else {
            $code = str_replace('_', '', $code);
            return "{$project}-{$code}.{$host}";
        }
    }
}