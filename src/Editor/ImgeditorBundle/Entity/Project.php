<?php

namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Editor\ImgeditorBundle\Entity\Action;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Project
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Project {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="id_project", type="string", length=13)
     */
    private $id_project;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     *
     * @var UploadedFile
     */
    public $file;

    /**
     *
     * @var string 
     */
    private $path;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file) {
        $this->file = $file;
    }

    /**
     * Gets file
     * 
     * @return UploadedFile 
     */
    public function getFile() {
        return $this->file;
    }

    public function __construct() {
        $this->actions = new ArrayCollection();
    }

    public function getAbsolutePath() {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath() {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getUploadDir() {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/images';
    }

    /**
     * 
     * 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->file) {
            // zrób cokolwiek chcesz aby wygenerować unikalną nazwę
            $this->path = uniqid() . '.' . $this->file->guessExtension();
        }      
        $this->id_project = uniqid();
        $this->created = new \DateTime();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->file) {
            return;
        }
        // musisz wyrzucać tutaj wyjątek jeśli plik nie może zostać przeniesiony
        // w tym przypadku encja nie zostanie zapisana do bazy
        // metoda move() obiektu UploadedFile robi to automatycznie
        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set id_project
     *
     * @param string $idProject
     * @return Project
     */
    public function setIdProject($idProject) {
        $this->id_project = $idProject;

        return $this;
    }

    /**
     * Get id_project
     *
     * @return string 
     */
    public function getIdProject() {
        return $this->id_project;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Project
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Add actions
     *
     * @param \Editor\ImgeditorBundle\Entity\Action $actions
     * @return Project
     */
    public function addAction(\Editor\ImgeditorBundle\Entity\Action $actions) {
        $this->actions[] = $actions;

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \Editor\ImgeditorBundle\Entity\Action $actions
     */
    public function removeAction(\Editor\ImgeditorBundle\Entity\Action $actions) {
        $this->actions->removeElement($actions);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActions() {
        return $this->actions;
    }

    /**
     * Add path
     *
     * @param \Editor\ImgeditorBundle\Entity\Action $path
     * @return Project
     */
    public function addPath(\Editor\ImgeditorBundle\Entity\Action $path) {
        $this->path[] = $path;

        return $this;
    }

    /**
     * Remove path
     *
     * @param \Editor\ImgeditorBundle\Entity\Action $path
     */
    public function removePath(\Editor\ImgeditorBundle\Entity\Action $path) {
        $this->path->removeElement($path);
    }

    /**
     * Get path
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Project
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

}