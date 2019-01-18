<?php

namespace Creativestyle\AdminListBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Adds a 'help' parameter to the forms, which allows
 * to render a short help text next to the field.
 */
class HelpExtension extends AbstractTypeExtension
{
    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help'] = $options['help'];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'help' => null,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes()
    {
        return [FormType::class];
    }
}