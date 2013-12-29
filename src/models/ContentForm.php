<?php

use Symfony\Component\Form\FormFactory;

/**
 * Forms content manager.
 */
class ContentForm extends Content
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    const TYPE_TEXT     = 'text';
    const TYPE_EMAIL    = 'email';
    const TYPE_INTEGER  = 'integer';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT   = 'select';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO    = 'radio';
    const TYPE_HTML     = 'html.insert';

    /**
     * Inject form factory dependency.
     *
     * @param \Symfony\Component\Form\FormFactory   $formFactory
     */
    public function setFormFactory(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Return the form object with dynamic fields.
     *
     * @return \Symfony\Component\Form\Form
     */
    public function generateFormFields($items)
    {
        $form = $this->formFactory->createBuilder('form');

        foreach ($items as $item) {
            if (self::TYPE_HTML == $item['type']) {
                continue;
            }
            $type = static::typeEquivalent($item['type']);
            $options =  array(
                'label' => $item['title'],
                'required' => (boolean) $item['parameters']['required'],
            );
            if ('choice' == $type) {
                $choices = array_map('trim', explode("\n", $item['parameters']['options']));
                $options += array(
                    'choices' => array_combine($choices, $choices),
                );
            }
            if (self::TYPE_RADIO == $item['type']) {
                $options += array(
                    'expanded' => true,
                );
            }
            $form->add($item['slug'], $type, $options);
        }

        return $form->getForm();
    }

    /**
     * Return the form object with dynamic fields.
     *
     * @param  integer                                   $sectionId
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \Symfony\Component\Form\Form              $form
     * @return boolean true if form is submited
     */
    public function checkSubmitedForm($sectionId, $request, $form)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $language = 'fr';
            $this->db->insert('expose_form_result', array(
                'expose_section_id' => $sectionId,
                'result' => serialize($data),
                'language' => $language,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ));
            return true;
        }
        return false;
    }

    /**
     * Return form results.
     *
     * @return array
     */
    public function getResults($sectionId)
    {
        $sql = "SELECT r.*
                FROM expose_form_result AS r
                WHERE r.expose_section_id = ?
                ORDER BY r.date ASC";
        $results = $this->db->fetchAll($sql, array($sectionId));

        foreach ($results as $row => $result) {
            $results[$row]['result'] = unserialize($result['result']);
        }

        return $results;
    }

    /**
     * Return fields types keys
     *
     * @return array
     */
    public static function getFieldTypes()
    {
        return array(
            self::TYPE_TEXT,
            self::TYPE_EMAIL,
            self::TYPE_INTEGER,
            self::TYPE_TEXTAREA,
            self::TYPE_SELECT,
            self::TYPE_CHECKBOX,
            self::TYPE_RADIO,
            self::TYPE_HTML,
        );
    }

    /**
     * Return field types keys and trans values
     *
     * @return array
     */
    public static function getFieldTypesChoice()
    {
        $keys = static::getFieldTypes();
        $values = array_map(function($item){
            return 'form.field.'.$item;
        }, $keys);
        return array_combine($keys, $values);
    }

    /**
     * Return Symfony form type equivalent.
     *
     * @param  string $type
     * @return string
     */
    public static function typeEquivalent($type)
    {
        $equivalents = array(
            self::TYPE_TEXT     => 'text',
            self::TYPE_EMAIL    => 'email',
            self::TYPE_INTEGER  => 'integer',
            self::TYPE_TEXTAREA => 'textarea',
            self::TYPE_SELECT   => 'choice',
            self::TYPE_CHECKBOX => 'checkbox',
            self::TYPE_RADIO    => 'choice',
        );

        return $equivalents[$type];
    }
}
