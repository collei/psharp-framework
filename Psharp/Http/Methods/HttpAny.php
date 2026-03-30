<?php
namespace Psharp\Http\Methods;

use Psharp\Http\Methods\Base\HttpMethodBase;
use Attribute;

/**
 * Class attribute for endpoints responding upon all verbs
 */
#[Attribute(Attribute::TARGET_METHOD)]
class HttpAny extends HttpMethodBase
{
	//
}