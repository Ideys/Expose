<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Section;
use Ideys\Content\Item\Entity\Item;
use Symfony\Component\Form\FormFactory;

/**
 * Item type factory.
 */
class ItemTypeFactory
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Form\FormFactory   $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Return the item form.
     *
     * @param \Ideys\Content\Item\Entity\Item $item
     *
     * @return \Symfony\Component\Form\Form
     *
     * @throws \Exception If item type is not found
     */
    public function createForm(Item $item)
    {
        $itemType = $item->getType();
        $typeClassName = '\Ideys\Content\Item\Type\\'.ucfirst($itemType).'Type';
        $type = new $typeClassName($this->formFactory);

        if ( ! $type instanceof ItemType) {
            throw new \Exception(sprintf('Unable to find a form type for Item "%s"', $itemType));
        }

        $formBuilder = $type->formBuilder($item);

        return $formBuilder->getForm();
    }
}
