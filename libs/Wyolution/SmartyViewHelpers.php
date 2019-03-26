<?php

namespace Wyolution;

class SmartyViewHelpers
{
	function currency($value) {
		if (trim($value) == '')
			return '';
		else
			return '$'.number_format($value);
	}
}
