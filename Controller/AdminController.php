<?php

namespace Astina\Bundle\FormBundle\Controller;

use Astina\Bundle\AdminBundle\Controller\BaseController;
use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Entity\FormSubmission;
use Astina\Bundle\FormBundle\Export\SubmissionsExporter;
use Astina\Bundle\CommonsBundle\Util\SlugHelper;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends BaseController
{
    /**
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $forms = $this->findFormConfigs();

        return array(
            'forms' => $forms,
        );
    }

    /**
     * @Template()
     *
     * @param FormConfig $formConfig
     * @param Request $request
     * @return array
     */
    public function formAction(FormConfig $formConfig, Request $request)
    {
        $page = max((int) $request->get('page'), 1);
        $max = 25;
        $from = ($page - 1) * $max;

        $submissions = $this->findSubmissions($formConfig, $from, $max);
        $submissionsCount = $this->countSubmissions($formConfig);

        return array(
            'form' => $formConfig,
            'submissions' => $submissions,
            'page' => $page,
            'pages' => ceil($submissionsCount / $max),
        );
    }

    /**
     * @Template()
     *
     * @param FormSubmission $formSubmission
     * @return array
     */
    public function submissionAction(FormSubmission $formSubmission)
    {
        return array(
            'submission' => $formSubmission,
        );
    }

    /**
     * @param FormSubmission $formSubmission
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submissionDeleteAction(FormSubmission $formSubmission)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($formSubmission);
        $em->flush();

        return $this->redirect($this->generateUrl('astina_form_view', array('id' => $formSubmission->getFormConfig()->getId())));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exportAction(Request $request)
    {
        $formIds = $request->get('forms');

        if (empty($formIds)) {
            $this->error($this->translator()->trans('form_export_empty'));
            return $this->redirect($this->generateUrl('astina_form_admin'));
        }

        $forms = $this->findFormConfigs($formIds);

        /** @var SubmissionsExporter $exporter */
        $exporter = $this->get('astina_form.submissions_exporter');
        $file = $exporter->createCsvFile($forms);
        $filename = $exporter->createFileName($forms, $this->translator());

        return new StreamedResponse(function() use ($file) {
            fpassthru(fopen($file, 'r'));
            unlink($file);
        }, 200, array(
            'content-type' => 'text/plain',
            'content-disposition' => sprintf('attachment; filename="%s"', $filename),
        ));
    }

    /**
     * @param $formIds
     * @return FormConfig[]
     */
    private function findFormConfigs(array $formIds = array())
    {
        /** @var EntityRepository  $repo */
        $repo = $this->getDoctrine()->getRepository('AstinaFormBundle:FormConfig');

        $builder = $repo->createQueryBuilder('f')
            ->join('f.area', 'a')
            ->join('a.page', 'p')
            ->orderBy('f.name', 'asc')
            ->addOrderBy('p.web', 'asc')
        ;

        if (!empty($formIds)) {
            $builder
                ->where('f.id in (:forms)')
                ->setParameter('forms', $formIds)
            ;
        }

        return $builder
            ->getQuery()
            ->getResult()
        ;
    }

    private function findSubmissions(FormConfig $formConfig, $from = 0, $max = 25)
    {
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository('AstinaFormBundle:FormSubmission');

        return $repo->createQueryBuilder('s')
            ->where('s.formConfig = :form')
            ->setParameter('form', $formConfig)
            ->orderBy('s.created', 'desc')
            ->setFirstResult($from)
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;
    }

    private function countSubmissions(FormConfig $formConfig)
    {
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository('AstinaFormBundle:FormSubmission');

        return $repo->createQueryBuilder('s')
            ->select('count(s)')
            ->where('s.formConfig = :form')
            ->setParameter('form', $formConfig)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
} 