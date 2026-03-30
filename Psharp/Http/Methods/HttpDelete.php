<?php
namespace Psharp\Http\Methods;

use Psharp\Http\Methods\Base\HttpMethodBase;
use Attribute;

/**
 * Class attribute for GET endpoints
 */
#[Attribute(Attribute::TARGET_METHOD)]
class HttpDelete extends HttpMethodBase
{
	//
}