<?php

namespace Ideys\Files;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File object.
 */
class File
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $file;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;



    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     *
     * @return \Ideys\Files\File
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Ideys\Files\File
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return \Ideys\Files\File
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return \Ideys\Files\File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return \Ideys\Files\File
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getDir().'/'.$this->name;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return WEB_DIR.'/../downloads';
    }

    /**
     * Persist file on server.
     */
    public function persist()
    {
        $this->getFile()->move($this->getDir());
    }
}
