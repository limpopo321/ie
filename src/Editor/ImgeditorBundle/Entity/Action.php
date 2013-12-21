<?php

namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Action
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Action {

    /**
     *
     * @var UploadedFile
     */
    public $file;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string Hash projektu (md5)
     *
     * @ORM\Column(name="id_project", type="string", length=32)        
     */
    private $idProject;

    /**
     * @var string Hash akcji (md5)
     *
     * @ORM\Column(name="id_action", type="string", length=32, nullable=true)        
     */
    private $idAction;

    /**
     *
     * @var integer Kolejny numer akcji w obrebie danego projektu (do operacji "wtecz" i "do przodu")
     * 
     * @ORM\Column(name="position", type="integer")
     */
    public $position;

    /*     * * 
     * 
     * @var string Nazwa obrazka
     * 
     * @ORM\Column(name="image", type="string", nullable=true)
     */
    public $image;

    /**
     * @var string Dodatkowe dane w formie zserializowanej tablicy powiazane z
     * krokiem edycji
     * @ORM\Column(type="string", length=1000)
     */
    public $jsonData;

    /**
     *
     * @var string Czas utworzenia akcji
     * @ORM\Column (type="datetime") 
     */
    public $created;

    /**
     *
     * @var string Czas aktualizacji akcji
     * @ORM\Column (type="datetime") 
     */
    public $updated;
    
    
    public function setIdProject($idProject){
        $this->idProject = $idProject;
    }    
    
    public function getIdProject($idProject){
       return $this->idProject;
    }
    
    
    public function setPosition($position){
        $this->position = $position;
    }
    
    public function getPosition(){
       return $this->position;
    }
    
    
    public function setUpdated($updated){
        $this->updated = $updated;
    }
    
    
    public function setCreated($created){
        $this->created = $created;
    }
        
    

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

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('file', 'file', array(
                    'label' => 'Wybierz zjęcie'
                ))

        ;
    }

    public function getAbsolutePath() {
        return $this->getUploadRootDir() . $this->getUploadDir() . '/' . $this->path;
    }

    public function getWebPath() {
        return $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir() {
        return __DIR__ . '/../../../../web/uploads/documents/';
    }

    public function getUploadDir() {
        return 'uploads/documents';
    }

    public function upload() {      
        
        if (null === $this->getFile()) {
            return;
        }

        $this->getFile()->move(
                $this->getUploadRootDir(), $this->getFile()->getClientOriginalName()
        );


        $this->image = $this->getFile()->getClientOriginalName();
        $this->file = null;
    }

}