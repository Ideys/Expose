<?php

/**
 * Forms content manager.
 */
class ContentForm extends ContentPrototype implements ContentInterface
{
    const TYPE_TEXT     = 'text';
    const TYPE_EMAIL    = 'email';
    const TYPE_INTEGER  = 'integer';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT   = 'select';
    const TYPE_MULTIPLE_SELECT = 'multiple.select';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO    = 'radio';
    const TYPE_HTML     = 'html.insert';

    public static function getParameters()
    {
        return array(
            'validation_message' => $this->translator->trans('form.validation.message.default'),
        );
    }

    /**
     * Return the form object with dynamic fields.
     *
     * @return \Symfony\Component\Form\Form
     */
    public function generateFormFields($formFactory)
    {
        $form = $formFactory->createBuilder('form');

        foreach ($this->items as $item) {
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
            if (self::TYPE_MULTIPLE_SELECT == $item['type']) {
                $options += array(
                    'multiple' => true,
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
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \Symfony\Component\Form\Form              $form
     * @return boolean true if form is submited
     */
    public function checkSubmitedForm($request, $form)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->db->insert('expose_form_result', array(
                'expose_section_id' => $this->id,
                'result' => serialize($data),
                'language' => $this->language,
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
    public function getResults()
    {
        $sql = "SELECT r.*
                FROM expose_form_result AS r
                WHERE r.expose_section_id = ?
                ORDER BY r.date ASC";
        $results = $this->db->fetchAll($sql, array($this->id));

        foreach ($results as $row => $result) {
            $results[$row]['result'] = unserialize($result['result']);
        }

        return $results;
    }

    /**
     * Delete a form result.
     *
     * @param integer $id
     * @return boolean
     */
    public function deleteResult($id)
    {
        $rows = $this->db->delete('expose_form_result', array('id' => $id));

        return (0 < $rows);
    }

    /**
     * Delete a form and its result.
     *
     * @param integer $id
     * @return boolean
     */
    public function deleteSection($id)
    {
        $formDeleted = parent::deleteSection($id);

        if ($formDeleted) {
            $this->db->delete('expose_form_result', array('expose_section_id' => $id));
        }

        return $formDeleted;
    }

    /**
     * Return the form section edit form.
     *
     * @param array $section
     * @return \Symfony\Component\Form\Form
     */
    public function editForm($section)
    {
        $form = $this->sectionForm($section)
            ->remove('type')
            ->add('parameter_validation_message', 'textarea', array(
                'label' => 'form.validation.message',
            ))
        ;

        return $form->getForm();
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
            self::TYPE_MULTIPLE_SELECT,
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
            self::TYPE_MULTIPLE_SELECT => 'choice',
            self::TYPE_CHECKBOX => 'checkbox',
            self::TYPE_RADIO    => 'choice',
        );

        return $equivalents[$type];
    }
}
