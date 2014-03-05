<?php

namespace Ideys\Files;

use Doctrine\DBAL\Connection;

/**
 * Downloadable files manager.
 */
class FilesHandeler
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;


    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * Save a file on database.
     *
     * @param Message
     */
    public function addFile(File $file)
    {
        $file->persist();

        $timestamp = (new \DateTime())->format('c');

        $this->db->insert('expose_files', array(
            'file' => $file->getFileName(),
            'mime' => $file->getMime(),
            'title' => $file->getTitle(),
            'name' => $file->getName(),
            'slug' => $file->getSlug(),
            'updated_at' => $timestamp,
            'created_at' => $timestamp,
        ));
    }

    /**
     * Delete a message.
     *
     * @param integer $id
     */
    public function delete($id)
    {
        $this->db->delete('expose_files', array('id' => $id));
    }

    /**
     * Retrieve all files.
     *
     * @return string
     */
    private function baseQuery()
    {
        return 'SELECT f.id, f.file, f.mime, f.title, f.name, f.slug '
             . 'FROM expose_files AS f '
             . 'LEFT JOIN expose_files_recipients AS r '
             . 'ON f.id = r.expose_files_id ';
    }

    /**
     * Retrieve all files.
     */
    public function findAll()
    {
        $entities = $this->db->fetchAll(
                $this->baseQuery()
        );

        $files = array();
        foreach ($entities as $row => $entity) {
            $files[$row] = $this->hydrateFile($entity);
        }

        return $files;
    }

    /**
     * Retrieve a file by its slug and recipient token.
     *
     * @param string $slug  The file url slug name.
     * @param string $token The recipient credential token.
     *
     * @return \Ideys\Files\File
     */
    public function findBySlugAndToken($slug, $token)
    {
        $entity = $this->db->fetchAssoc(
                $this->baseQuery()
              . 'WHERE f.slug = ?'
              . 'AND r.token = ?',
        array($slug, $token));

        return $this->hydrateFile($entity);
    }

    /**
     * Hydrate a File with db results.
     *
     * @param array $entity
     *
     * @return \Ideys\Files\File
     */
    private function hydrateFile($entity)
    {
        $file = new File();
        $file
            ->setId($entity['id'])
            ->setFileName($entity['file'])
            ->setMime($entity['mime'])
            ->setName($entity['name'])
            ->setTitle($entity['title'])
            ->setSlug($entity['slug'])
        ;
        return $file;
    }
}
