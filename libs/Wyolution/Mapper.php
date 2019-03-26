<?PHP

namespace Wyolution;

/**
 * Wrapper class to to allow access to db's log file
 *
  * @package Wyolution
 * @author Anne Gunn <agunn@sherprog.com>
 *
 */
class Mapper extends \DB\SQL\MAPPER
{

	/**
	 * This class is the thinnest of wrappers for the normal f3 mapper.
	 * So far, the only thing it does is allow the calling code to get
	 * the db's log string if/when it wants.
	 *
	 * F3 should just make that a standard feature of the regular mapper.
	 *
	 * So all we do in our constructor is call the parent constructor
	 *
	 *    Instantiate class
	 * @param $db object
	 * @param $table string
	 * @param $fields array|string
	 * @param $ttl int
	 **/
	function __construct($db, $table, $fields=NULL, $ttl=60)
	{
		parent::__construct($db, $table, $fields, $ttl);
	}

	public function getLog()
	{
		return $this->db->log();
	}
}
