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

        $this->db->insert('expose_files', array(
            'title' => $file->getTitle(),
            'name' => $file->getFile()->getBasename(),
            'slug' => \Ideys\String::slugify($file->getName()),
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
        return 'SELECT f.id, f.title, f.name, f.slug '
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
            $files[$row] = new File();
            $files[$row]
                    ->setId($entity['id'])
                    ->setName($entity['name'])
                    ->setTitle($entity['title'])
                    ->setSlug($entity['slug'])
            ;
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

        $file = new File();
        $file
                ->setId($entity['id'])
                ->setTitle($entity['title'])
                ->setName($entity['name'])
                ->setSlug($entity['slug'])
        ;

        return $file;
    }
}
