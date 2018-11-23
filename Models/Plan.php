<?php

namespace FinancePlugin\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_plans")
 * @ORM\Entity
 */
class Plan extends ModelEntity
{
    /**
     * @var string $id
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    private $name;


    /**
     * @var string $description
     *
     * @ORM\Column(type="string", length=500, nullable=false)
     */
    private $description;

    /**
     * @var integer $updated_on
     *
     * @ORM\Column(type="integer", length=10, nullable=false)
     */
    private $updated_on;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getUpdatedOn()
    {
        return $this->updated_on;
    }

    /**
     * @param int $updated_on
     */
    public function setUpdatedOn($updated_on)
    {
        $this->updated_on = $updated_on;
    }
}