<?
namespace HappyPuppy;
class hasOneRelation extends Relation
{
	var $foreign_class;
	var $foreign_table;
	var $foreign_key;

	function __construct($dbobject, $name, $foreign_class='', $foreign_table = '', $foreign_key = '')
	{
		$refl = new \ReflectionClass(get_class($dbobject));
		if (strcmp($foreign_class, '') == 0) {
			$foreign_class = $refl->getNamespaceName()."\\".Inflector::remove_underscores($name);
		}
		if (strcmp($foreign_table, '') == 0) {
			if ($_ENV["config"]["plural_db_tables"] == 1) {
				$foreign_table = Inflector::plural(Inflector::remove_underscores($name));
			} else {
				$foreign_table = Inflector::remove_underscores($name);
			}
		}
		if (strcmp($foreign_key, '') == 0){ $foreign_key = $foreign_table.'_id'; }
		$this->name = $name;
		$this->foreign_class = $foreign_class;
		$this->foreign_table = $foreign_table;
		$this->foreign_key = $foreign_key;
	}
}

?>