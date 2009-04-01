<?php

function imagereplace($path)
{
	if(file_exists($path))
	{
		$dims = getimagesize($path);
		$width = $dims[0];
		$height = $dims[1];
	}
	return "width: ". $width . "px;\nheight: ".$height."px;\ntext-indent: -9000px; background-image: url('$path');";
}

function roundedcorners($radius)
{
	return "-moz-border-radius: ".$radius."px; -webkit-border-radius:".$radius."px;";
}

?>