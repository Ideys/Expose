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
    private $mime;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var array
     */
    private $recipients;


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
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     *
     * @return \Ideys\Files\File
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

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
     * @param string $fileName
     *
     * @return \Ideys\Files\File
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
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
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param \Ideys\Files\Recipient $recipient
     *
     * @return \Ideys\Files\File
     */
    public function addRecipient(Recipient $recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getDir().'/'.$this->fileName;
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
        $this->setFileName(uniqid('expose').'.'.$this->file->guessClientExtension());
        $this->setMime($this->file->getMimeType());
        $this->setName($this->file->getClientOriginalName());
        $this->setSlug(\Ideys\String::slugify($this->title));
        $this->getFile()->move($this->getDir(), $this->fileName);
    }
}
