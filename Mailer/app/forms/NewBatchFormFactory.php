<?php

namespace Remp\MailerModule\Forms;

use Nette\Application\UI\Form;
use Nette\Object;
use Remp\MailerModule\Repository\BatchesRepository;
use Remp\MailerModule\Repository\BatchTemplatesRepository;
use Remp\MailerModule\Repository\JobsRepository;
use Remp\MailerModule\Repository\ListsRepository;
use Remp\MailerModule\Repository\TemplatesRepository;
use Remp\MailerModule\Segment\Aggregator;
use Tracy\Debugger;

class NewBatchFormFactory extends Object
{
    private $jobsRepository;

    private $batchesRepository;

    private $templatesRepository;

    private $batchTemplatesRepository;

    private $listsRepository;

    private $segmentAggregator;

    public $onSuccess;

    public function __construct(
        JobsRepository $jobsRepository,
        BatchesRepository $batchesRepository,
        TemplatesRepository $templatesRepository,
        BatchTemplatesRepository $batchTemplatesRepository,
        ListsRepository $listsRepository,
        Aggregator $segmentAggregator
    ) {
        $this->jobsRepository = $jobsRepository;
        $this->batchesRepository = $batchesRepository;
        $this->templatesRepository = $templatesRepository;
        $this->batchTemplatesRepository = $batchTemplatesRepository;
        $this->listsRepository = $listsRepository;
        $this->segmentAggregator = $segmentAggregator;
    }

    public function create($jobId = null)
    {
        $form = new Form;
        $form->addProtection();

        if (!$jobId) {
            $segments = [];
            $segmentList = $this->segmentAggregator->list();
            array_walk($segmentList, function ($segment) use (&$segments) {
                $segments[$segment['provider']][$segment['provider'] . '::' . $segment['code']] = $segment['name'];
            });
            if ($this->segmentAggregator->hasErrors()) {
                $form->addError('Unable to fetch list of segments, please check the application configuration.');
                Debugger::log($this->segmentAggregator->getErrors()[0], Debugger::WARNING);
            }

            $form->addSelect('segment_code', 'Segment', $segments)
                ->setPrompt('Select segment')
                ->setRequired("Field 'Segment' is required.");
        }

        $methods = [
            'random' => 'Random',
            'sequential' => 'Sequential',
        ];
        $form->addSelect('method', 'Method', $methods);

        $listPairs = $this->listsRepository->all()->fetchPairs('id', 'title');

        $form->addSelect('mail_type_id', 'Email A alternative', $listPairs)
            ->setPrompt('Select newsletter list');

        if (isset($_POST['mail_type_id'])) {
            $templateList = $this->templatesRepository->pairs($_POST['mail_type_id']);
        } else {
            $templateList = null;
        }
        $form->addSelect('template_id', null, $templateList)
            ->setPrompt('Select email')
            ->setRequired('Email for A alternative is required');

        $form->addSelect('b_mail_type_id', 'Email B alternative (optional, can be added later)', $listPairs)
            ->setPrompt('Select newsletter list');

        if (isset($_POST['b_mail_type_id'])) {
            $templateList = $this->templatesRepository->pairs($_POST['b_mail_type_id']);
        } else {
            $templateList = null;
        }
        $form->addSelect('b_template_id', null, $templateList)
            ->setPrompt('Select alternative email');

        $form->addText('email_count', 'Number of emails');

        $form->addText('start_at', 'Start date');

        $form->addHidden('job_id', $jobId);

        $form->addSubmit('save', 'Save')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="zmdi zmdi-mail-send"></i> Save');

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        if (!$values['job_id']) {
            $segment = explode('::', $values['segment_code']);
            $values['job_id'] = $this->jobsRepository->add($segment[1], $segment[0]);
        }

        $batch = $this->batchesRepository->add(
            $values['job_id'],
            !empty($values['email_count']) ? (int)$values['email_count'] : null,
            $values['start_at'],
            $values['method']
        );

        $this->batchTemplatesRepository->add(
            $values['job_id'],
            $batch->id,
            $values['template_id']
        );

        if ($values['b_template_id'] !== null) {
            $this->batchTemplatesRepository->add(
                $values['job_id'],
                $batch->id,
                $values['b_template_id']
            );
        }

        ($this->onSuccess)($batch->job);
    }
}
