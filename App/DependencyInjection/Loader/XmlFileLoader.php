<?php

namespace App\DependencyInjection\Loader;

use DOMDocument;
use ErrorException;
use SimpleXMLElement;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Reference;

use function array_keys;
use function constant;
use function max;
use function simplexml_import_dom;
use function str_replace;
use function strtolower;

class XmlFileLoader extends FileLoader
{
    public function load($file, $type = null) : void
    {
        $path = $this->locator->locate($file);

        $xml = $this->parseFile($path);
        $xml->registerXPathNamespace('config', 'http://c24.net/schema');

        $this->container->addResource(new FileResource($path));

        $this->parseParameters($xml);

        $this->parseCommands($xml);

        $this->parseForms($xml);

        $this->parseDefinitions($xml, $path);
    }

    protected function parseCommands(SimpleXMLElement $xml) : void
    {
        if (false === $commands = $xml->xpath('//config:commands/config:command')) {
            return;
        }
        try {
            $commandConfig = $this->container->getParameter('command.definition');
        } catch (ParameterNotFoundException $e) {
            $commandConfig = [];
        }

        foreach ($commands as $command) {
            $commandConfig[] = $this->getAttributeAsPhp($command, 'class');
        }

        $this->container->setParameter('command.definition', $commandConfig);
    }

    /**
     * Parses parameters.
     */
    protected function parseParameters(SimpleXMLElement $xml) : void
    {
        if (! $xml->parameters) {
            return;
        }

        $this->container->getParameterBag()->add($this->getArgumentsAsPhp($xml->parameters, 'parameter'));
    }

    protected function parseForms(SimpleXMLElement $xml) : void
    {
        if (false === $forms = $xml->xpath('//config:forms/config:form')) {
            return;
        }

        try {
            $formConfig = $this->container->getParameter('forms');
        } catch (ParameterNotFoundException $e) {
            $formConfig = [];
        }

        foreach ($forms as $form) {
            $formConfig[$this->getAttributeAsPhp($form, 'name')] = $this->getAttributeAsPhp($form, 'class');
        }

        $this->container->setParameter('forms', $formConfig);
    }

    protected function parseDefinitions(SimpleXMLElement $xml, $file) : void
    {
        if (false === $services = $xml->xpath('//config:services/config:service')) {
            return;
        }
        foreach ($services as $service) {
            $this->parseDefinition((string) $service['id'], $service, $file);
        }
    }

    protected function parseDefinition($id, $service, $file) : void
    {
        $definition = $this->parseService($id, $service, $file);
        if (null !== $definition) {
            $this->container->setDefinition($id, $definition);
        }
    }

    protected function parseService($id, $service, $file)
    {
        if ((string) $service['alias']) {
            $public = true;
            if (isset($service['public'])) {
                $public = $this->getAttributeAsPhp($service, 'public');
            }
            $this->container->setAlias($id, new Alias((string) $service['alias'], $public));

            return;
        }

        if (isset($service['parent'])) {
            $definition = new ChildDefinition((string) $service['parent']);
        } else {
            $definition = new Definition();
        }

        foreach (['class', 'shared', 'scope', 'public', 'factory', 'synthetic', 'abstract'] as $key) {
            if (isset($service[$key])) {
                $method = 'set' . str_replace('-', '', $key);
                $value  = $this->getAttributeAsPhp($service, $key);
                $definition->$method($value);
            }
        }

        if ($service->file) {
            $definition->setFile((string) $service->file);
        }

        $definition->setArguments($this->getArgumentsAsPhp($service, 'argument'));
        $definition->setProperties($this->getArgumentsAsPhp($service, 'property'));
        if (! empty($this->getArgumentsAsPhp($service, 'factory'))) {
            $definition->setFactory($this->getServiceFactory($service->factory));
        }

        if (isset($service->configurator)) {
            if (isset($service->configurator['function'])) {
                $definition->setConfigurator((string) $service->configurator['function']);
            } else {
                if (isset($service->configurator['service'])) {
                    $class = new Reference((string) $service->configurator['service'], ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
                } else {
                    $class = (string) $service->configurator['class'];
                }

                $definition->setConfigurator([$class, (string) $service->configurator['method']]);
            }
        }

        foreach ($service->call as $call) {
            $definition->addMethodCall((string) $call['method'], $this->getArgumentsAsPhp($call, 'argument'));
        }

        foreach ($service->tag as $tag) {
            $parameters = [];
            foreach ($tag->attributes() as $name => $value) {
                if ('name' === $name) {
                    continue;
                }

                $parameters[$name] = XmlUtils::phpize($value);
            }

            $definition->addTag((string) $tag['name'], $parameters);
        }

        return $definition;
    }

    protected function parseFile($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, [$this, 'isValidXml']);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return simplexml_import_dom($dom);
    }

    public function isValidXml(DOMDocument $dom)
    {
        /* @todo validate this file against schema */
        return true;
    }

    public function supports($resource, $type = null) : void
    {
    }

    private function getArgumentsAsPhp(SimpleXMLElement $xml, $name, $lowercase = true)
    {
        $arguments = [];
        foreach ($xml->$name as $arg) {
            if (isset($arg['name'])) {
                $arg['key'] = (string) $arg['name'];
            }
            $key = isset($arg['key']) ? (string) $arg['key'] : (! $arguments ? 0 : max(array_keys($arguments)) + 1);

            if ('parameter' === $name && $lowercase) {
                $key = strtolower($key);
            }

            if (isset($arg['index'])) {
                $key = 'index_' . $arg['index'];
            }

            switch ($arg['type']) {
                case 'service':
                    $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
                    if (isset($arg['on-invalid']) && 'ignore' === $arg['on-invalid']) {
                        $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
                    } elseif (isset($arg['on-invalid']) && 'null' === $arg['on-invalid']) {
                        $invalidBehavior = ContainerInterface::NULL_ON_INVALID_REFERENCE;
                    }

                    $arguments[$key] = new Reference((string) $arg['id'], $invalidBehavior);
                    break;
                case 'collection':
                    $arguments[$key] = $this->getArgumentsAsPhp($arg, $name, false);
                    break;
                case 'string':
                    $arguments[$key] = (string) $arg;
                    break;
                case 'constant':
                    $arguments[$key] = constant((string) $arg);
                    break;
                default:
                    $arguments[$key] = XmlUtils::phpize($arg);
            }
        }

        return $arguments;
    }

    protected function getServiceFactory($factoryXml)
    {
        $factoryMethod = $this->getAttributeAsPhp($factoryXml, 'method');

        if ('' === $factoryMethod) {
            $factoryMethod = '__invoke';
        }

        if ('' !== $this->getAttributeAsPhp($factoryXml, 'service')) {
            return [
                new Reference($this->getAttributeAsPhp($factoryXml, 'service')),
                $factoryMethod,
            ];
        }

        if ('' !== $this->getAttributeAsPhp($factoryXml, 'class')) {
            return [
                $this->getAttributeAsPhp($factoryXml, 'class'),
                $factoryMethod,
            ];
        }

        throw new ErrorException('You must specify either a class or a service in factory');
    }

    public function getAttributeAsPhp(SimpleXMLElement $xml, $name)
    {
        return XmlUtils::phpize($xml[$name]);
    }
}
