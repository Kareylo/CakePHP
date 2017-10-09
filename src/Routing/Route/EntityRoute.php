<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.6.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Routing\Route;

use ArrayAccess;
use RuntimeException;

/**
 * Matches entities to routes
 *
 * This route will match by entity and map its fields to the URL pattern by
 * comparing the field names with the template vars. This makes it easy and
 * convenient to change routes globally.
 */
class EntityRoute extends Route
{
    /**
     * Match by entity and map its fields to the URL pattern by comparing the
     * field names with the template vars
     *
     * @param array $url Array of parameters to convert to a string.
     * @param array $context An array of the current request context.
     *   Contains information such as the current host, scheme, port, and base
     *   directory.
     * @return bool|string Either false or a string URL.
     */
    public function match(array $url, array $context = [])
    {
        // Check if there's an entity in the $url array
        if (!isset($url['_entity'])) {
            $entity = array_filter($url, function ($item) {
                return $item instanceof ArrayAccess || is_array($item);
            });
            if ($entity && is_array($entity)) {
                $key = array_search(array_shift($entity), $url);
                $url['_entity'] = $url[$key];
                unset($url[$key]);
            }
        }

        if (isset($url['_entity'])) {
            $this->_checkEntity($url['_entity']);

            $entity = $url['_entity'];
            preg_match_all('@:(\w+)@', $this->template, $matches);

            foreach ($matches[1] as $field) {
                $url[$field] = $entity[$field];
            }
        }

        return parent::match($url, $context);
    }

    /**
     * Checks that we really deal with an entity object
     *
     * @throws \RuntimeException
     * @param \ArrayAccess|array $entity Entity value from the URL options
     * @return void
     */
    protected function _checkEntity($entity)
    {
        if (!$entity instanceof ArrayAccess && !is_array($entity)) {
            throw new RuntimeException(sprintf(
                'Route `%s` expects the URL option `_entity` to be an array or object implementing \ArrayAccess, but `%s` passed.',
                $this->template,
                is_object($entity) ? get_class($entity) : gettype($entity)
            ));
        }
    }
}
