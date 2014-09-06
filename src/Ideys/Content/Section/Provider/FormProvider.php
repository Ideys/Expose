<?php

namespace Ideys\Content\Section\Provider;

use Ideys\Content\Item\Entity\Field;
use Ideys\Content\Section\Entity;
use Ideys\Files\File;
use Ideys\String;
use Symfony\Component\Form as SfForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Form section provider.
 */
class FormProvider extends SectionProvider
{
    /**
     * Return the form object with dynamic fields.
     *
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param \Ideys\Content\Section\Entity\Form  $formSection
     *
     * @return \Symfony\Component\Form\Form
     */
    public function generateFormFields(SfForm\FormFactory $formFactory, Entity\Form $formSection)
    {
        $form = $formFactory->createBuilder('form');

        foreach ($formSection->getItems() as $item) {
            if (Field::HTML == $item->getCategory()) {
                continue;
            }
            if (! $item instanceof Field) {
                continue;
            }
            $fieldType = Field::getSymfonyEquivalent($item->getCategory());
            $options =  array(
                'label' => $item->getTitle(),
                'required' => (boolean) $item->isRequired(),
            );
            if ('choice' == $fieldType) {
                $choices = array_map('trim', explode("\n", $item->getChoices()));
                $options += array(
                    'choices' => array_combine($choices, $choices),
                );
            }
            if (Field::MULTIPLE_SELECT == $item->getCategory()) {
                $options += array(
                    'multiple' => true,
                );
            }
            if (Field::RADIO == $item->getCategory()) {
                $options += array(
                    'expanded' => true,
                );
            }
            $form->add($item->getSlug(), $fieldType, $options);
        }

        return $form->getForm();
    }

    /**
     * Return the form object with dynamic fields.
     *
     * @param  \Ideys\Content\Section\Entity\Form        $sectionForm
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \Symfony\Component\Form\Form              $form
     *
     * @return boolean true if form is submitted
     */
    public function checkSubmittedForm(Entity\Form $sectionForm, Request $request, SfForm\Form $form)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->handleFiles($data);
            $this->db->insert('expose_form_result', array(
                'expose_section_id' => $sectionForm->getId(),
                'result' => serialize($data),
                'language' => $this->language,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ));
            return true;
        }
        return false;
    }

    /**
     * Handle file data persistence.
     *
     * @param array $data
     */
    private function handleFiles(&$data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) {
                $data[$key.'__file'] = String::slugify($value->getClientOriginalName());
                $data[$key.'__path'] = uniqid('expose').'.'.$value->guessClientExtension();
                $value->move(File::getDir(), $data[$key.'__path']);
                unset($data[$key]);
            }
        }
    }

    /**
     * Return form results.
     *
     * @param Entity\Section $section
     *
     * @return array
     */
    public function getResults(Entity\Section $section)
    {
        $sql = 'SELECT r.* '.
            'FROM expose_form_result AS r '.
            'WHERE r.expose_section_id = ? '.
            'ORDER BY r.date ASC ';
        $results = $this->db->fetchAll($sql, array($section->getId()));

        foreach ($results as $row => $result) {
            $results[$row]['result'] = unserialize($result['result']);
        }

        return $results;
    }

    /**
     * Delete a form result.
     *
     * @param integer $id
     *
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
     * @param Entity\Section $section
     *
     * @return boolean
     */
    public function delete(Entity\Section $section)
    {
        $formDeleted = parent::delete($section);

        if ($formDeleted) {
            $this->db->delete('expose_form_result', array('expose_section_id' => $section->getId()));
        }

        return $formDeleted;
    }
}
