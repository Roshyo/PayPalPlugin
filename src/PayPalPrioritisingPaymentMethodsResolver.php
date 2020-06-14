<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\PayPalPlugin;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class PayPalPrioritisingPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    /** @var PaymentMethodsResolverInterface */
    private $decoratedPaymentMethodsResolver;

    public function __construct(PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver)
    {
        $this->decoratedPaymentMethodsResolver = $decoratedPaymentMethodsResolver;
    }

    public function getSupportedMethods(BasePaymentInterface $payment): array
    {
        return $this->sortPayments(
            $this->decoratedPaymentMethodsResolver->getSupportedMethods($payment),
            'sylius.pay_pal'
        );
    }

    public function supports(BasePaymentInterface $payment): bool
    {
        return $this->decoratedPaymentMethodsResolver->supports($payment);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    private function sortPayments(array $payments, string $firstPaymentFactoryName): array
    {
        /** @var PaymentMethodInterface[] $sortedPayments */
        $sortedPayments = [];

        /** @var PaymentMethodInterface $payment */
        foreach ($payments as $payment) {
            $gatewayConfig = $payment->getGatewayConfig();

            if ($gatewayConfig !== null && $gatewayConfig->getFactoryName() === $firstPaymentFactoryName) {
                array_unshift($sortedPayments, $payment);
            } else {
                $sortedPayments[] = $payment;
            }
        }

        return $sortedPayments;
    }
}