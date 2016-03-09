<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Controller;

use Sylius\Bundle\CoreBundle\Form\Model\ApiCustomerModel;
use Sylius\Bundle\UserBundle\Controller\CustomerController as BaseCustomerController;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Resource\Event\ResourceEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomerController extends BaseCustomerController
{
    /**
     * Render user filter form.
     */
    public function filterFormAction(Request $request)
    {
        return $this->render('SyliusWebBundle:Backend/Customer:filterForm.html.twig', array(
            'form' => $this->get('form.factory')->createNamed('criteria', 'sylius_customer_filter', $request->query->get('criteria'))->createView()
        ));
    }

    public function createAction(Request $request)
    {
        $this->isGrantedOr403('create');

        /** @var Customer $resource */
        $resource = $this->createNew();

        $apiCustomerModel = new ApiCustomerModel();
        $form = $this->container->get('form.factory')->createNamed('', 'sylius_api_customer', $apiCustomerModel, array('csrf_protection' => false));

        if ($request->isMethod('POST') && $form->submit($request)->isValid()) {
            $resource->setFirstName($apiCustomerModel->getFirstName());
            $resource->setLastName($apiCustomerModel->getLastName());
            $resource->setEmail($apiCustomerModel->getEmail());

            $user = $this->get('sylius.repository.user')->createNew();
            $user->setPlainPassword($apiCustomerModel->getPlainPassword());
            $user->setEnabled($apiCustomerModel->isEnabled());
            $resource->setUser($user);

            $resource = $this->domainManager->create($resource);

            if ($this->config->isApiRequest()) {
                if ($resource instanceof ResourceEvent) {
                    throw new HttpException($resource->getErrorCode(), $resource->getMessage());
                }

                return $this->handleView($this->view($resource, 201));
            }

            if ($resource instanceof ResourceEvent) {
                return $this->redirectHandler->redirectToIndex();
            }

            return $this->redirectHandler->redirectTo($resource);
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form, 400));
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('create.html'))
            ->setData(array(
                $this->config->getResourceName() => $resource,
                'form'                           => $form->createView(),
            ))
        ;

        return $this->handleView($view);
    }
}
