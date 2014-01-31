<?php

/**
 * Pages content manager.
 */
class ContentPage extends ContentPrototype implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * Return page content first page.
     */
    public function getFirstPage()
    {
        $items = $this->getItems();

        return array_pop($items);
    }
}
