<?php
namespace PSharp\Http\Methods;

use PSharp\Http\Methods\Base\HttpMethodBase;
use Attribute;

/**
 * Class attribute for GET endpoints
 */
#[Attribute(Attribute::TARGET_METHOD)]
class HttpPatch extends HttpMethodBase
{
	//
}