<?php

namespace Astina\Bundle\FormBundle\Export;

use Astina\Bundle\CommonsBundle\Util\SlugHelper;
use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Entity\FormSubmission;
use Symfony\Component\Translation\TranslatorInterface;

class SubmissionsExporter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    protected $tempDir;

    function __construct(TranslatorInterface $translator, $tempDir)
    {
        $this->translator = $translator;
        $this->tempDir = $tempDir;
    }

    /**
     * @param FormConfig[] $formConfigs
     * @return string
     */
    public function createCsvFile(array $formConfigs)
    {
        $file = tempnam($this->tempDir, 'astina-form-export');
        $fp = fopen($file, 'w');

        foreach ($formConfigs as $formConfig) {
            $this->addSubmissions($formConfig, $fp);
        }

        fclose($fp);

        return $file;
    }

    private function addSubmissions(FormConfig $formConfig, $fp)
    {
        fputcsv($fp, array($formConfig->getName(), $formConfig->getWeb()));

        $headers = array();
        foreach ($formConfig->getFields() as $field) {
            if ($field->getName() == '-') {
                continue;
            }

            $headers[] = $field->getName();
        }

        $headers[] = 'Counter';
        $headers[] = 'Browser';
        $headers[] = 'Ip';
        $headers[] = 'Time';

        fputcsv($fp, $headers);

        /** @var FormSubmission $submission */
        foreach ($formConfig->getSubmissions() as $submission) {
            $row = array();

            foreach ($formConfig->getFields() as $field) {
                if ($field->getName() == '-') {
                    continue;
                }

                $row[] = $submission->findValue($field);
            }

            $row[] = $submission->getCounter();
            $row[] = $submission->getBrowser();
            $row[] = $submission->getIp();
            $row[] = $submission->getCreated()->format('d.m.Y H:i:s');

            fputcsv($fp, $row);
        }

        fputcsv($fp, array());
    }

    /**
     * @param FormConfig[] $forms
     * @return string
     */
    public function createFileName(array $forms)
    {
        $filename = $this->translator->trans('form_export_filename');

        if (count($forms) == 1) {
            $form = current($forms);
            $filename = sprintf('%s_%s', $this->createFileNameFromName($form->getName()), $form->getWeb());
        }

        $names = array();
        foreach ($forms as $form) {
            $names[] = $form->getName();
        }
        $names = array_unique($names);
        if (count($names) == 1) {
            $filename = $this->createFileNameFromName($names[0]);
        }

        return sprintf('%s_%s.csv', $filename, date('dmY'));
    }

    protected function createFileNameFromName($name)
    {
        return str_replace(' ', '_', SlugHelper::normalizeUtf8String($name));
    }
}