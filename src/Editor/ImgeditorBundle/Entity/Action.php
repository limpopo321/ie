<?php

namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Editor\ImgeditorBundle\Entity\Project;

/**
 * Action
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Action {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_action", type="integer", length=40)
     */
    private $id_action;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", length=40)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=40)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="json_data", type="string", length=1000)
     */
    private $json_data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="actions", cascade={"persist"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set id_action
     *
     * @param integer $idAction
     * @return Action
     */
    public function setIdAction($idAction) {
        $this->id_action = $idAction;

        return $this;
    }

    /**
     * Get id_action
     *
     * @return integer 
     */
    public function getIdAction() {
        return $this->id_action;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Action
     */
    public function setPosition($position) {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Action
     */
    public function setImage($image) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Set json_data
     *
     * @param string $jsonData
     * @return Action
     */
    public function setJsonData($jsonData) {
        $this->json_data = $jsonData;

        return $this;
    }

    /**
     * Get json_data
     *
     * @return string 
     */
    public function getJsonData() {
        return $this->json_data;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Action
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set project
     *
     * @param $project
     * @return Action
     */
    public function setProject(\Editor\ImgeditorBundle\Entity\Project $project) {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Editor\ImgeditorBundle\Entity\Project 
     */
    public function getProject() {
        return $this->project;
    }

}
