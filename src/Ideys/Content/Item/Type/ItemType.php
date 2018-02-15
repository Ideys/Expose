<?php

namespace Ideys\Content\Item\Type;

use Symfony\Component\Form\FormFactory;

/**
 * Item type base class.
 */
abstract class ItemType
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }
}
