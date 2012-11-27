<?php

namespace Message\Cog\Functions;

use Message\Cog\Service\Container as ServiceContainer;
use LogicException;

class Utility
{
	protected $_moduleLoader;

	public function __construct($moduleLoader)
	{
		$this->_moduleLoader = $moduleLoader;
	}

	/**
	 * Trace the current function call back to the module that called it.
	 *
	 * This calls `debug_backtrace` and loops through each of the elements,
	 * checking the class name until it finds an entry in the list of loaded
	 * modules.
	 *
	 * @return string         Module name found in the backtrace
	 * @throws LogicException If stack trace could not be traced back to a module
	 */
	public function traceCallingModuleName()
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($backtrace as $call) {
			// Turn a class name like Mothership\Core\Model\Order into Mothership\Core
			$namespaces = explode('\\', $call['class']);
			$moduleName = implode('\\', array_slice($namespaces, 0, 2));

			if(in_array($moduleName, $this->_moduleLoader->getModules())) {
				return $moduleName;
			}
		}

		throw new LogicException('Stack trace could not be traced back to a module');
	}

	/**
	 * Recursively converts an array to an object.
	 *
	 * @param array $array                  The array to convert
	 * @param bool  $maintainNumericIndices If true, numeric indecies are retained
	 * @return \stdClass                    The array in object form
	 */
	public function arrayToObject($array, $maintainNumericIndices = false)
	{
		if (!is_array($array)) {
			return $array;
		}

		$hasNumericIndices = (array_values($array) === $array) && $maintainNumericIndices;
		$new = $hasNumericIndices ? array() : new \stdClass;

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = $this->arrayToObject($value, $maintainNumericIndices);
			}

			if ($hasNumericIndices) {
				$new[$key] = $value;
			} else {
				$new->{ServiceContainer::get('fns.text')->toCamelCaps($key)} = $value;
			}
		}

		return $new;
	}
}