<?php
namespace Markup\Matkahuolto\Api;

interface AgentsInterface
{
    /**
     * Returns Matkahuolto agents by postcode
     *
     * @api
     * @return string agents in JSON
     */
    public function agents();
}
