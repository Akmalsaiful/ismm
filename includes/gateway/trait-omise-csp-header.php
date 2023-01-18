<?php

trait OmiseCspHeaderTrait
{
    function add_csp_header($url)
	{
		// Add the policy to the HTTP response
		header("Content-Security-Policy: frame-src http://localhost:5002 https://pay.google.com 'self';");
	}
}