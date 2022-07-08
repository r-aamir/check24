<?php

namespace App\Factory;

use App\Form\Base\BaseForm;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RequestStack;

use function array_search;
use function sprintf;

/**
 * Class FormFactory.
 */
class FormFactory implements FormFactoryInterface
{
    private RequestStack $requestStack;
    private EventDispatcherInterface $eventDispatcher;
    private array $formDefinition;

    public function __construct(
        RequestStack $requestStack,
        EventDispatcherInterface $eventDispatcher,
        array $formDefinition
    ) {
        $this->requestStack    = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
        $this->formDefinition  = $formDefinition;
    }

    public function buildForm(
        string $name,
        array $data = [],
        array $options = [],
        string $type = FormType::class,
    ) : BaseForm {

        if (false !== array_search($name, $this->formDefinition, true)) {
            $formClass = $name;
        } elseif (null === $formClass = $this->formDefinition[$name]) {
            throw new Error(sprintf("The form '%s' doesn't exist", $name));
        }

        return new $formClass(
            $this->requestStack->getCurrentRequest(),
            $this->eventDispatcher,
            $data,
            $options,
            $type
        );
    }
}
