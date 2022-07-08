<?php

namespace App\Form\Base;

use App\Enum\FormEnum;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function implode;
use function sprintf;

abstract class BaseForm implements BaseFormInterface
{
    protected FormBuilderInterface $formBuilder;

    private Form $form;
    private Request $request;
    private EventDispatcherInterface $dispatcher;
    private ?FormView $view;

    public function __construct(
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        array $data = [],
        array $options = [],
        string $type = FormType::class
    ) {
        $this->request = $request;

        $this->dispatcher = $eventDispatcher;

        $formFactoryBuilder = Forms::createFormFactoryBuilder();
        $formFactoryBuilder->addExtensions([
            new HttpFoundationExtension(),
            new ValidatorExtension(Validation::createValidatorBuilder()->getValidator()),
        ]);

        $this->formBuilder = $formFactoryBuilder
            ->getFormFactory()
            ->createNamedBuilder($this::getName(), $type, $data, $options);
        $this->buildForm();

        $this->form = $this->formBuilder->getForm();
    }

    public function getRequest() : Request
    {
        return $this->request;
    }

    public function createView()
    {
        return $this->view = $this->form->createView();
    }

    public function getView() : FormView
    {
        return $this->view;
    }

    /**
     * Form validation method.
     *
     * @todo extend this BaseForm class to add extra security, such as
     *  - custom exception
     *  - max attempts
     *  - max time passed
     */
    public function validateForm(?string $method = 'POST')
    {
        $request = $this->getRequest();
        if (null !== $method && ! $request->isMethod($method)) {
            throw new Error(sprintf('Submit method did not match, expected %s.', $method));
        }

        $this->form->handleRequest($request);
        if ($this->form->isValid()) {
            return true;
        }

        $message = implode("\0", $this->form->getErrors());
        throw new Error($message);
    }

    public function isValid(?string $method = 'POST') : bool
    {
        try {
            $this->validateForm($method);
        } catch (Throwable $ex) {
            return false;
        }

        return true;
    }

    /**
     * Get the form error message.
     */
    public function getErrors() : string
    {
        return implode("\0", $this->form->getErrors());
    }

    public function getForm() : Form
    {
        return $this->form;
    }

    public function isSubmitted() : bool
    {
        return $this->form->isSubmitted();
    }

    private function dispatchEvent($event, FormEnum $eventType) : void
    {
        $eventName = $eventType->value . '.' . $this::getName();
        $this->dispatcher->dispatch($event, $eventName);
    }

    /**
     * Add form elements.
     */
    abstract protected function buildForm();
}
