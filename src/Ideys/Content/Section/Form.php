<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Content\Item\Field;
use Symfony\Component\Form\FormFactory;

/**
 * Form content manager.
 */
class Form extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'validation_message' => '',
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
            if (Field::HTML == $item->category) {
                continue;
            }
            $fieldType = Field::getSymfonyEquivalent($item->category);
            $options =  array(
                'label' => $item->title,
                'required' => (boolean) $item->required,
            );
            if ('choice' == $fieldType) {
                $choices = array_map('trim', explode("\n", $item->options));
                $options += array(
                    'choices' => array_combine($choices, $choices),
                );
            }
            if (Field::MULTIPLE_SELECT == $item->category) {
                $options += array(
                    'multiple' => true,
                );
            }
            if (Field::RADIO == $item->category) {
                $options += array(
                    'expanded' => true,
                );
            }
            $form->add($item->slug, $fieldType, $options);
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
        $formDeleted = \Ideys\Content\ContentFactory::deleteSection($id);

        if ($formDeleted) {
            $this->db->delete('expose_form_result', array('expose_section_id' => $id));
        }

        return $formDeleted;
    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(FormFactory $formFactory)
    {
        $formBuilder = $this->settingsFormBuilder($formFactory)
            ->add('validation_message', 'textarea', array(
                'label' => 'form.validation.message',
            ))
        ;

        return $formBuilder->getForm();
    }
}
