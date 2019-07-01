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
 * @ORM\Table(name="s_plugin_FinancePlugin_environments")
 * @ORM\Entity
 */
class Environment extends ModelEntity
{
    private $table = 's_plugin_FinancePlugin_environments';
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", length=8, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $plugin_id
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    private $plugin_id;


    /**
     * @var string $environment
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $environment;

    /**
     * @var integer $updated_on
     *
     * @ORM\Column(type="integer", length=10, nullable=false)
     */
    private $updated_on;

    /**
     * Set the ID
     *
     * @param string $name ID
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

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
     * Get the plugin ID
     *
     * @return string
     */
    public function getPluginId()
    {
        return $this->plugin_id;
    }

    /**
     * Set the plugin ID
     *
     * @param string $name Plugin ID
     *
     * @return void
     */
    public function setPluginId($plugin_id)
    {
        $this->plugin_id = $plugin_id;
    }

    /**
     * Get the environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set the environment
     *
     * @param string $environment The API environment
     *
     * @return void
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
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