<?php
set_error_handler(function($errorId, $errorString, $errorFile, $errorLine)
{
	throw new ErrorException($errorString, $errorId, 0, $errorFile, $errorLine);
});
