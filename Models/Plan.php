<?php
/**
 * File for the Plan model
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_plugin_FinancePlugin_plans")
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
     * Get the ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID
     *
     * @param string $id Unique Plan ID
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name
     *
     * @param string $name Name of plan
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description
     *
     * @param string $description Description of plan
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the date the plan was added
     *
     * @return int
     */
    public function getUpdatedOn()
    {
        return $this->updated_on;
    }

    /**
     * Set the date the plan was added
     *
     * @param int $updated_on unix timestamp of last update
     *
     * @return void
     */
    public function setUpdatedOn($updated_on)
    {
        $this->updated_on = $updated_on;
    }
}