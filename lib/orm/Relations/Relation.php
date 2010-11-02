<?php
namespace HappyPuppy;
abstract class Relation
{
	var $name;
	var $foreign_class;
	public function getType()
	{
		$refl = new \ReflectionClass(get_called_class());
		return $refl->getShortName();
	}
}
?>
