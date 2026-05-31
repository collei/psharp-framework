<?php
namespace PSharp\Http;

use Attribute;

/**
 * Class attribute for endpoints responding upon all verbs
 */
#[Attribute(Attribute::TARGET_METHOD)]
class NotFound
{
	//
}