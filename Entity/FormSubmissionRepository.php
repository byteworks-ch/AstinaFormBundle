<?php
namespace Astina\Bundle\FormBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FormSubmissionRepository extends EntityRepository
{
    /**
     * @param FormConfig $formConfig
     * @param array $data
     * @return FormSubmission
     */
    public function saveFormData(FormConfig $formConfig, array $data)
    {
        $submission = new FormSubmission();
        $submission->setFormConfig($formConfig);
        $submission->setCreated(new \DateTime());

        $submission->setBrowser($data['browser']);
        $submission->setIp($data['ip']);

        foreach ($formConfig->getFields() as $configField) {

            $name = $configField->getName();
            $value = $this->getFieldValue($data, $configField);

            $field = new FormSubmissionField();
            $field->setConfigField($configField);
            $field->setName($name);
            $field->setValue($value);
            $submission->addField($field);
        }
        
        $submission->setCounter($this->getSubmitCount($formConfig));

        $em = $this->getEntityManager();
        $em->persist($submission);
        $em->flush($submission);

        return $submission;
    }


    /**
     * @param $data
     * @param FormConfigField $configField
     * @return string
     */
    private function getFieldValue($data, FormConfigField $configField)
    {
        $value = $data[$configField->getId()];

        if ($value instanceof \DateTime) {
            $value = $value->format('d.m.Y');
        }

        return $value;
    }


    /**
     * get submit count for given form config over all languages
     * that use this form
     * 
     * @param FormConfig $formConfig
     * @return int
     */
    public function getSubmitCount(FormConfig $formConfig)
    {
        return 1 + (int) $this->getEntityManager()
            ->createQuery('
                SELECT MAX(fs.counter) FROM AstinaFormBundle:FormSubmission fs WHERE fs.formConfig IN (
                    SELECT fc.id FROM AstinaFormBundle:FormConfig fc WHERE fc.syncReference = :syncReference
                )
            ')
            ->setParameter('syncReference', $formConfig->getSyncReference())
            ->getSingleScalarResult()
        ;
    }
} 