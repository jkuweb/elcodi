<?php

/*
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 * @author Elcodi Team <tech@elcodi.com>
 */

namespace Elcodi\Plugin\StoreSetupWizardBundle\Templating;

use Symfony\Component\HttpFoundation\RequestStack;

use Elcodi\Component\Configuration\Services\ConfigurationManager;
use Elcodi\Component\Plugin\Entity\Plugin;
use Elcodi\Component\Plugin\Interfaces\EventInterface;
use Elcodi\Component\Plugin\Templating\Traits\TemplatingTrait;
use Elcodi\Plugin\StoreSetupWizardBundle\Services\WizardRoutes;
use Elcodi\Plugin\StoreSetupWizardBundle\Services\WizardStatus;

/**
 * Class TwigRenderer
 */
class TwigRenderer
{
    use TemplatingTrait;

    /**
     * @var Plugin
     *
     * Plugin
     */
    protected $plugin;

    /**
     * @var WizardStatus
     *
     * A WizardStatus
     */
    protected $wizardStatus;

    /**
     * @var RequestStack
     *
     * A request stack
     */
    protected $requestStack;

    /**
     * @var ConfigurationManager
     *
     * A configuration manager
     */
    protected $configurationManager;

    /**
     * @var WizardRoutes
     *
     * The wizard routes service
     */
    protected $wizardRoutes;

    /**
     * Builds a new class
     *
     * @param WizardStatus         $wizardStatus         The Wizard status
     * @param RequestStack         $requestStack         A request stack
     * @param ConfigurationManager $configurationManager A configuration manager
     * @param WizardRoutes         $wizardRoutes         A wizard routes service
     */
    public function __construct(
        WizardStatus $wizardStatus,
        RequestStack $requestStack,
        ConfigurationManager $configurationManager,
        WizardRoutes $wizardRoutes
    ) {
        $this->wizardStatus         = $wizardStatus;
        $this->requestStack         = $requestStack;
        $this->configurationManager = $configurationManager;
        $this->wizardRoutes = $wizardRoutes;
    }

    /**
     * Set plugin
     *
     * @param Plugin $plugin Plugin
     *
     * @return $this Self object
     */
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Renders the mini wizard bar
     *
     * @param EventInterface $event The event
     */
    public function renderMiniWizard(EventInterface $event)
    {
        if (
            $this->plugin->isEnabled() &&
            $this->isVisible() &&
            !$this->wizardStatus->isWizardFinished()
        ) {
            $stepsFinished = $this
                ->wizardStatus
                ->getStepsFinishStatus();

            $activeStep = $this
                ->wizardStatus
                ->getNextStep();

            $this->appendTemplate(
                '@ElcodiStoreSetupWizard/Wizard/wizard.html.twig',
                $event,
                [
                    'stepsFinished' => $stepsFinished,
                    'activeStep'    => $activeStep,
                    'isMiniWizard'  => true,
                ]
            );
        }
    }

    /**
     * Render the message to enable store.
     *
     * @param EventInterface $event The event
     */
    public function renderEnableStoreMessage(EventInterface $event)
    {
        if ($this->plugin->isEnabled()) {
            $storeEnabled =
                'on' == $this
                    ->configurationManager
                    ->get('store.enabled');

            $masterRequest = $this
                ->requestStack
                ->getMasterRequest();

            $route = $masterRequest
                ->attributes
                ->get('_route');

            if (
                $this->wizardStatus->isWizardFinished() &&
                'admin_configuration_list' != $route &&
                !$storeEnabled
            ) {
                $this->appendTemplate(
                    '@ElcodiStoreSetupWizard/Wizard/enable-store.html.twig',
                    $event
                );
            }
        }
    }

    /**
     * Render the next step message.
     *
     * @param EventInterface $event The event
     */
    public function renderGoNextStepMessage(EventInterface $event)
    {
        if ($this->plugin->isEnabled()) {
            $masterRequest = $this
                ->requestStack
                ->getMasterRequest();
            $currentRoute  = $masterRequest
                ->attributes
                ->get('_route');

            $isWizardRoute = $this
                ->wizardRoutes
                ->isWizardSetupRoute($currentRoute);

            $isWizardFinished = $this
                ->wizardStatus
                ->isWizardFinished();

            if (
                $isWizardRoute &&
                !$isWizardFinished
            ) {
                $currentStep = $this
                    ->wizardRoutes
                    ->getStepByRoute($currentRoute);

                $isStepFinished = $this
                    ->wizardStatus
                    ->isStepFinished($currentStep);

                if (
                    1 != $currentStep &&
                    $isStepFinished
                ) {
                    $stepsFinished = $this
                        ->wizardStatus
                        ->getStepsFinishStatus();

                    $activeStep = $this
                        ->wizardStatus
                        ->getNextStep();

                    $this->appendTemplate(
                        '@ElcodiStoreSetupWizard/Wizard/wizard.html.twig',
                        $event,
                        [
                            'stepsFinished' => $stepsFinished,
                            'activeStep'    => $activeStep,
                            'isMiniWizard'  => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Checks if the wizard is visible for this request.
     *
     * @return bool If visible
     */
    protected function isVisible()
    {
        $masterRequest = $this
            ->requestStack
            ->getMasterRequest();

        if ($masterRequest->query->get('modal', false)) {
            return false;
        }

        $route = $masterRequest
            ->attributes
            ->get('_route');

        return !$this->wizardRoutes->isWizardHidden($route);
    }
}