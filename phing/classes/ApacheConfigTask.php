<?php
/**
 * Generates apache config file contents
 */
class ApacheConfigTask extends DomainsTask
{
    public function main()
    {
        $codes = explode(',', $this->codes);
        $domains = array();

        $domains[] = $this->getVirtualHost();

        foreach($codes as $code) {
            $domains[] = $this->getVirtualHost($code);
        }

        $value = implode("\n\n", $domains);

        $this->project->setProperty($this->property, $value);
    }

    protected function getVirtualHost($code = null)
    {
        $domain = $this->getDomain($code);
        $project = $this->project->getProperty('project.name');
        $runType = $this->project->getProperty('project.magento.runtype');
        if(empty($runType)) {
            $runType = 'store';
        }
        $documentRoot = $this->project->getProperty('project.magedir');

        $config = "<Virtualhost *:80>\n    ServerName {$domain}\n    DocumentRoot {$documentRoot}\n";
        $config .= "\n    ErrorLog logs/{$project}_error_log\n    CustomLog logs/{$project}_access_log common\n";

        if(!empty($code)) {
            $config .= "\n    SetEnv MAGE_RUN_CODE {$code}\n    SetEnv MAGE_RUN_TYPE {$runType}\n";
        }

        $config .= "</Virtualhost>";
        return $config;
    }
}