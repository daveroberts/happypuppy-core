<?

namespace HappyPuppy;
class habtmRelation extends Relation
{
	var $sort_by;
	var $link_table;
	var $foreign_class;
	var $foreign_table;
	var $link_table_fk_here;
	var $link_table_fk_foreigntable;
	var $foreign_table_pk;
	
	function __construct($dbobject, $name, $sort_by='', $foreign_class='', $foreign_table = '', $foreign_table_pk='', $link_table = '', $link_table_fk_here = '', $link_table_fk_foreigntable = '')
	{
		$this->name = $name;
		$this->sort_by = $sort_by;
		$this->link_table = $link_table;
		$this->foreign_table = $foreign_table;
		$this->foreign_class = $foreign_class;
		$this->link_table_fk_here = $link_table_fk_here;
		$this->link_table_fk_foreigntable = $link_table_fk_foreigntable;
		$this->foreign_table_pk = $foreign_table_pk;
	}
}

?>