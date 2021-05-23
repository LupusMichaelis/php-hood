<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

interface Controller
{
	function __invoke(App $app):void ;
}
